<?php

namespace App\Services;

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExcelReportService
{
    // Constant for unified font configuration across all sheets
    private const DEFAULT_FONT_NAME = 'Aptos Narrow';

    /**
     * Entry point to generate the satisfaction survey Excel report.
     */
    public function generate(string $startDate, string $endDate, ?int $templateId = null): Spreadsheet
    {
        // Define the start and end of the requested date range
        $startOfDay = Carbon::parse($startDate)->startOfDay();
        $endOfDay = Carbon::parse($endDate)->endOfDay();

        // Fallback to the system's default template if no template ID is provided
        if (!$templateId) {
            $systemSettings = SystemSetting::set();
            $templateId = $systemSettings?->default_survey_template_id;
        }

        $spreadsheet = new Spreadsheet;

        // Graceful error handling: If no template ID is defined, return an informative sheet
        if (!$templateId) {
            $spreadsheet->getActiveSheet()->setCellValue('A1', __('No default survey template configured.'));

            return $spreadsheet;
        }

        // Fetch survey template along with its ordered questions
        $surveyTemplate = SurveyTemplate::with([
            'surveyQuestions' => function ($query) {
                $query->orderBy('order');
            }
        ])->find($templateId);

        // Graceful error handling: If the template ID is invalid, return an informative sheet
        if (!$surveyTemplate) {
            $spreadsheet->getActiveSheet()->setCellValue('A1', __('Default template not found.'));

            return $spreadsheet;
        }

        $questions = $surveyTemplate->surveyQuestions;

        // Fetch completed surveys within the date range, eager loading patient and answers relationships
        $completedSurveys = Survey::with(['patient', 'answers'])
            ->where('survey_template_id', $surveyTemplate->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('created_at')
            ->get();

        // Remove the default initial worksheet created automatically by PhpSpreadsheet
        $spreadsheet->removeSheetByIndex(0);

        // Generate one worksheet per survey question
        foreach ($questions as $questionIndex => $question) {
            $this->buildQuestionWorksheet($spreadsheet, $question, $completedSurveys, $questionIndex, $startOfDay);
        }

        return $spreadsheet;
    }

    /**
     * Builds and styles a single worksheet corresponding to one survey question.
     */
    private function buildQuestionWorksheet(
        Spreadsheet $spreadsheet,
        object $question,
        Collection $completedSurveys,
        int $questionIndex,
        Carbon $startDateCarbon
    ): void {
        // Create a new worksheet tab for the current question index
        $worksheet = $spreadsheet->createSheet($questionIndex);
        $worksheet->setTitle('PREGUNTA ' . ($questionIndex + 1));

        $questionOptions = $question->options ?? [];
        $isChoiceQuestion = in_array($question->field_type, ['radio', 'select']) && !empty($questionOptions);
        $isTextQuestion = $question->field_type === 'text';

        // 1. Build dynamic column specifications based on question type
        $columnConfigurations = [
            'ID' => ['label' => 'ID', 'width' => 12, 'align' => Alignment::HORIZONTAL_CENTER],
            'PATIENT' => ['label' => 'PACIENTE', 'width' => 46, 'align' => Alignment::HORIZONTAL_LEFT],
        ];

        // Append option columns if it is a choice question
        if ($isChoiceQuestion) {
            foreach ($questionOptions as $option) {
                $optionLabel = $option['label'] ?? $option;
                $columnConfigurations['OPT_' . $optionLabel] = [
                    'label' => $optionLabel,
                    'width' => max(16, mb_strlen($optionLabel) + 4),
                    'align' => Alignment::HORIZONTAL_CENTER,
                    'is_option' => true,
                ];
            }
        }

        // Append answer column if it is a text-based question
        if ($isTextQuestion) {
            $columnConfigurations['TEXT_ANSWER'] = [
                'label' => 'RESPUESTA',
                'width' => 40,
                'align' => Alignment::HORIZONTAL_LEFT,
            ];
        }

        // Standard trailing columns for ratings and metadata
        $columnConfigurations['WEIGHTED'] = ['label' => 'PONDERADO', 'width' => 14, 'align' => Alignment::HORIZONTAL_CENTER];
        $columnConfigurations['DATE'] = ['label' => 'FECHA', 'width' => 14, 'align' => Alignment::HORIZONTAL_CENTER];
        $columnConfigurations['OBSERVATIONS'] = ['label' => 'OBSERVACIONES', 'width' => 28, 'align' => Alignment::HORIZONTAL_LEFT];

        // Set left margin spacing in Column A
        $worksheet->getColumnDimension('A')->setWidth(3);

        // Map column identifiers to physical Excel column letters (starting at B)
        $currentColumnIndex = 2;
        $columnLetterMapping = [];

        foreach ($columnConfigurations as $columnKey => $columnConfig) {
            $columnLetter = Coordinate::stringFromColumnIndex($currentColumnIndex);
            $columnLetterMapping[$columnKey] = $columnLetter;
            $worksheet->getColumnDimension($columnLetter)->setWidth($columnConfig['width']);
            $currentColumnIndex++;
        }

        $firstDataColumnLetter = 'B';
        $lastDataColumnLetter = Coordinate::stringFromColumnIndex($currentColumnIndex - 1);

        // 2. Render Report Main Headers (Rows 2, 3, and 4)
        $headerRows = [
            2 => 'TABULACION ENCUESTA DE SATISFACCION',
            3 => Date::dateTimeToExcel($startDateCarbon),
            4 => ($questionIndex + 1) . '. ' . mb_strtoupper($question->question_text),
        ];

        // Apply unified alignment and font to the entire header region at once
        $headerRange = "{$firstDataColumnLetter}2:{$lastDataColumnLetter}4";
        $worksheet->getStyle($headerRange)->getFont()->setName(self::DEFAULT_FONT_NAME);
        $worksheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($headerRows as $rowNumber => $value) {
            $cellCoordinate = "{$firstDataColumnLetter}{$rowNumber}";
            $rowRange = "{$cellCoordinate}:{$lastDataColumnLetter}{$rowNumber}";

            $worksheet->mergeCells($rowRange);
            $worksheet->setCellValue($cellCoordinate, $value);
        }

        // Specific styling rules per header row
        $worksheet->getStyle("{$firstDataColumnLetter}2")->getFont()->setBold(true)->setSize(12);
        $worksheet->getStyle("{$firstDataColumnLetter}3")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        $worksheet->getStyle("{$firstDataColumnLetter}4")->getFont()->setBold(true);
        $worksheet->getStyle("{$firstDataColumnLetter}4")->getAlignment()->setWrapText(true);

        // 3. Render Table Column Headers (Rows 5 and 6)
        $headerStartRow = 5;
        foreach ($columnConfigurations as $columnKey => $columnConfig) {
            $columnLetter = $columnLetterMapping[$columnKey];

            $worksheet->mergeCells("{$columnLetter}{$headerStartRow}:{$columnLetter}" . ($headerStartRow + 1));
            $worksheet->setCellValue("{$columnLetter}{$headerStartRow}", $columnConfig['label']);

            $worksheet->getStyle("{$columnLetter}{$headerStartRow}")
                ->getFont()->setBold(true)->setSize(11)->setName(self::DEFAULT_FONT_NAME);
            $worksheet->getStyle("{$columnLetter}{$headerStartRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        }

        // 4. Render Survey Data Rows (Starting at Row 7)
        $startDataRow = 7;
        $currentRow = $startDataRow;

        foreach ($completedSurveys as $survey) {
            $patient = $survey->patient;

            // Direct index lookup for the answer related to the current question (O(1) in memory)
            $matchedAnswer = $survey->answers->firstWhere('survey_question_id', $question->id);

            // Populate Patient ID (Column B)
            $worksheet->setCellValue($columnLetterMapping['ID'] . $currentRow, $patient->id ?? 'N/A');

            // Populate Patient Full Identifier (Column C)
            $patientFullName = trim(($patient->dni ?? '') . ' ' . ($patient->name ?? ''));
            $worksheet->setCellValue($columnLetterMapping['PATIENT'] . $currentRow, $patientFullName);

            // Render choice selections ('X') if applicable
            if ($isChoiceQuestion && $matchedAnswer) {
                $normalizedAnswerValue = CalculateSurveyRating::normalize($matchedAnswer->answer_value);
                foreach ($questionOptions as $option) {
                    $optionLabel = $option['label'] ?? $option;
                    if (CalculateSurveyRating::normalize($optionLabel) === $normalizedAnswerValue) {
                        $worksheet->setCellValue($columnLetterMapping['OPT_' . $optionLabel] . $currentRow, 'X');
                    }
                }
            }

            // Render text answer if question type is text
            if ($isTextQuestion) {
                $textValue = $matchedAnswer ? trim((string) $matchedAnswer->answer_value) : '';
                $worksheet->setCellValue($columnLetterMapping['TEXT_ANSWER'] . $currentRow, $textValue);
            }

            // Calculate and render Weighted Score
            if ($isTextQuestion) {
                // For text questions: 5.00 if responded, 1.00 if unanswered or empty
                $hasTextAnswer = $matchedAnswer && !empty(trim((string) $matchedAnswer->answer_value));
                $weightedScore = $hasTextAnswer ? 5.00 : 1.00;

                $worksheet->setCellValue($columnLetterMapping['WEIGHTED'] . $currentRow, $weightedScore);
                $worksheet->getStyle($columnLetterMapping['WEIGHTED'] . $currentRow)->getNumberFormat()->setFormatCode('0.00');
            } elseif ($matchedAnswer !== null && $matchedAnswer->weighted_value !== null) {
                $worksheet->setCellValue($columnLetterMapping['WEIGHTED'] . $currentRow, (float) $matchedAnswer->weighted_value);
                $worksheet->getStyle($columnLetterMapping['WEIGHTED'] . $currentRow)->getNumberFormat()->setFormatCode('0.00');
            } elseif ($question->field_type === 'number' && $matchedAnswer && is_numeric($matchedAnswer->answer_value)) {
                $worksheet->setCellValue($columnLetterMapping['WEIGHTED'] . $currentRow, (float) $matchedAnswer->answer_value);
                $worksheet->getStyle($columnLetterMapping['WEIGHTED'] . $currentRow)->getNumberFormat()->setFormatCode('0.00');
            }

            // Populate Survey Submission Date
            if ($survey->created_at) {
                $worksheet->setCellValue($columnLetterMapping['DATE'] . $currentRow, Date::dateTimeToExcel($survey->created_at));
                $worksheet->getStyle($columnLetterMapping['DATE'] . $currentRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }

            // Populate Observations (Empty placeholder)
            $worksheet->setCellValue($columnLetterMapping['OBSERVATIONS'] . $currentRow, '');

            // Apply fonts and horizontal alignment to the entire row cells
            foreach ($columnConfigurations as $columnKey => $columnConfig) {
                $cellAddress = $columnLetterMapping[$columnKey] . $currentRow;
                $worksheet->getStyle($cellAddress)->getFont()->setName(self::DEFAULT_FONT_NAME)->setSize(11);
                $worksheet->getStyle($cellAddress)->getAlignment()->setHorizontal($columnConfig['align']);
            }

            $currentRow++;
        }

        // 5. Render Average Summary Row (After loop completion)
        $lastDataRow = $currentRow - 1;

        if ($lastDataRow >= $startDataRow) {
            $weightedCol = $columnLetterMapping['WEIGHTED'];

            // Etiqueta "PROMEDIO" en la columna previa al ponderado
            $worksheet->setCellValue("C{$currentRow}", 'PROMEDIO');
            $worksheet->getStyle("C{$currentRow}")->getFont()->setBold(true)->setName(self::DEFAULT_FONT_NAME);
            $worksheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $worksheet->setCellValue("{$weightedCol}{$currentRow}", "=AVERAGE({$weightedCol}{$startDataRow}:{$weightedCol}{$lastDataRow})");

            // Formato visual de la celda del promedio
            $worksheet->getStyle("{$weightedCol}{$currentRow}")->getNumberFormat()->setFormatCode('0.00');
            $worksheet->getStyle("{$weightedCol}{$currentRow}")->getFont()->setBold(true)->setName(self::DEFAULT_FONT_NAME);
            $worksheet->getStyle("{$weightedCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Adjust row heights to match target design
        $worksheet->getRowDimension(2)->setRowHeight(15.75);
        $worksheet->getRowDimension(4)->setRowHeight(15);
    }
}
