<?php

namespace App\Services;

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SystemSetting;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelReportService
{
    public function generate(string $startDate, string $endDate): Spreadsheet
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $settings = SystemSetting::set();
        $templateId = $settings->default_survey_template_id;

        if (!$templateId) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->setCellValue('A1', __('No default survey template configured.'));
            return $spreadsheet;
        }

        $template = \App\Models\SurveyTemplate::with(['surveyQuestions' => function ($q) {
            $q->orderBy('order');
        }])->find($templateId);

        if (!$template) {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->setCellValue('A1', __('Default template not found.'));
            return $spreadsheet;
        }

        $questions = $template->surveyQuestions;

        $surveys = Survey::with(['patient', 'answers'])
            ->where('survey_template_id', $template->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($questions as $qIndex => $question) {
            $sheetName = 'PREGUNTA ' . ($qIndex + 1);
            $ws = $spreadsheet->createSheet($qIndex);
            $ws->setTitle($sheetName);

            $ws->getColumnDimension('A')->setWidth(3);
            $ws->getColumnDimension('B')->setWidth(46);

            $options = $question->options ?? [];
            $colLetter = 'C';
            foreach ($options as $opt) {
                $label = $opt['label'] ?? $opt;
                $ws->getColumnDimension($colLetter)->setWidth(max(16, mb_strlen($label) + 4));
                $colLetter++;
            }
            $ws->getColumnDimension($colLetter)->setWidth(14);
            $colLetter++;
            $ws->getColumnDimension($colLetter)->setWidth(14);
            $colLetter++;
            $ws->getColumnDimension($colLetter)->setWidth(28);

            $lastCol = $this->lastColLetter(2 + count($options) + 3);

            $ws->mergeCells('B2:' . $lastCol . '2');
            $ws->setCellValue('B2', 'TABULACION ENCUESTA DE SATISFACCION');
            $ws->getStyle('B2')->getFont()->setBold(true)->setSize(12)->setName('Aptos Narrow');
            $ws->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ws->mergeCells('B3:' . $lastCol . '3');
            $dt = Carbon::parse($startDate);
            $ws->setCellValue('B3', $dt);
            $ws->getStyle('B3')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $ws->getStyle('B3')->getFont()->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ws->mergeCells('B4:' . $lastCol . '4');
            $ws->setCellValue('B4', ($qIndex + 1) . '. ' . mb_strtoupper($question->question_text));
            $ws->getStyle('B4')->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

            $headerRow = 5;
            $ws->mergeCells('B' . $headerRow . ':B' . ($headerRow + 1));
            $ws->setCellValue('B' . $headerRow, 'NOMBRE DE PACIENTE');
            $ws->getStyle('B' . $headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('B' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $colLetter = 'C';
            foreach ($options as $opt) {
                $label = $opt['label'] ?? $opt;
                $ws->mergeCells($colLetter . $headerRow . ':' . $colLetter . ($headerRow + 1));
                $ws->setCellValue($colLetter . $headerRow, $label);
                $ws->getStyle($colLetter . $headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
                $ws->getStyle($colLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                $colLetter++;
            }

            $ws->mergeCells($colLetter . $headerRow . ':' . $colLetter . ($headerRow + 1));
            $ws->setCellValue($colLetter . $headerRow, 'PONDERADO');
            $ws->getStyle($colLetter . $headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle($colLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colLetter++;

            $ws->mergeCells($colLetter . $headerRow . ':' . $colLetter . ($headerRow + 1));
            $ws->setCellValue($colLetter . $headerRow, 'FECHA');
            $ws->getStyle($colLetter . $headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle($colLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colLetter++;

            $ws->mergeCells($colLetter . $headerRow . ':' . $colLetter . ($headerRow + 1));
            $ws->setCellValue($colLetter . $headerRow, 'OBSERVACIONES');
            $ws->getStyle($colLetter . $headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle($colLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 7;
            foreach ($surveys as $survey) {
                $patient = $survey->patient;
                $patientName = ($patient->dni ?? '') . ' ' . ($patient->name ?? '');
                $ws->setCellValue('B' . $row, $patientName);
                $ws->getStyle('B' . $row)->getFont()->setSize(11)->setName('Aptos Narrow');
                $ws->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $matchedAnswer = null;
                foreach ($survey->answers as $answer) {
                    if ($answer->survey_question_id === $question->id) {
                        $matchedAnswer = $answer;
                        break;
                    }
                }

                $colLetter = 'C';
                foreach ($options as $optIndex => $opt) {
                    $label = $opt['label'] ?? $opt;
                    if ($matchedAnswer !== null && CalculateSurveyRating::normalize($matchedAnswer->answer_value) === CalculateSurveyRating::normalize($label)) {
                        $ws->setCellValue($colLetter . $row, 'X');
                        $ws->getStyle($colLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    $colLetter++;
                }

                if ($matchedAnswer !== null && $matchedAnswer->weighted_value !== null) {
                    $ws->setCellValue($colLetter . $row, $matchedAnswer->weighted_value);
                    $ws->getStyle($colLetter . $row)->getNumberFormat()->setFormatCode('0.00');
                    $ws->getStyle($colLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $colLetter++;

                if ($survey->created_at) {
                    $ws->setCellValue($colLetter . $row, $survey->created_at);
                    $ws->getStyle($colLetter . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                    $ws->getStyle($colLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $colLetter++;

                $ws->setCellValue($colLetter . $row, '');
                $ws->getStyle($colLetter . $row)->getFont()->setSize(11)->setName('Aptos Narrow');

                $row++;
            }

            $ws->getRowDimension(2)->setRowHeight(15.75);
            $ws->getRowDimension(4)->setRowHeight(15);
        }

        return $spreadsheet;
    }

    private function lastColLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $colIndex--;
            $letter = chr(65 + ($colIndex % 26)) . $letter;
            $colIndex = intdiv($colIndex, 26);
        }
        return $letter;
    }
}
