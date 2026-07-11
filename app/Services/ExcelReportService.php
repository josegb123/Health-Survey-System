<?php

namespace App\Services;

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelReportService
{
    public function generate(string $startDate, string $endDate, ?int $templateId = null): Spreadsheet
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if (! $templateId) {
            $settings = SystemSetting::set();
            $templateId = $settings->default_survey_template_id;
        }

        if (! $templateId) {
            $spreadsheet = new Spreadsheet;
            $spreadsheet->getActiveSheet()->setCellValue('A1', __('No default survey template configured.'));

            return $spreadsheet;
        }

        $template = SurveyTemplate::with(['surveyQuestions' => function ($q) {
            $q->orderBy('order');
        }])->find($templateId);

        if (! $template) {
            $spreadsheet = new Spreadsheet;
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

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        foreach ($questions as $qIndex => $question) {
            $sheetName = 'PREGUNTA '.($qIndex + 1);
            $ws = $spreadsheet->createSheet($qIndex);
            $ws->setTitle($sheetName);

            $options = $question->options ?? [];
            $hasOptions = in_array($question->field_type, ['radio', 'select']) && ! empty($options);

            $ws->getColumnDimension('A')->setWidth(3);
            $ws->getColumnDimension('B')->setWidth(12);
            $ws->getColumnDimension('C')->setWidth(46);

            if ($hasOptions) {
                $colLetter = 'D';
                foreach ($options as $opt) {
                    $ws->getColumnDimension($colLetter)->setWidth(max(16, mb_strlen($opt['label'] ?? $opt) + 4));
                    $colLetter++;
                }
            }
            $respuestaCol = $hasOptions
                ? $this->lastColLetter(4 + count($options) - 1)
                : 'D';
            $ws->getColumnDimension($respuestaCol)->setWidth(16);
            $valorCol = $this->lastColLetter($this->colIndex($respuestaCol) + 1);
            $ws->getColumnDimension($valorCol)->setWidth(10);
            $fechaCol = $this->lastColLetter($this->colIndex($valorCol) + 1);
            $ws->getColumnDimension($fechaCol)->setWidth(14);
            $obsCol = $this->lastColLetter($this->colIndex($fechaCol) + 1);
            $ws->getColumnDimension($obsCol)->setWidth(28);

            $totalCols = $this->colIndex($obsCol);
            $lastCol = $this->lastColLetter($totalCols);

            $ws->mergeCells('B2:'.$lastCol.'2');
            $ws->setCellValue('B2', 'TABULACION ENCUESTA DE SATISFACCION');
            $ws->getStyle('B2')->getFont()->setBold(true)->setSize(12)->setName('Aptos Narrow');
            $ws->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ws->mergeCells('B3:'.$lastCol.'3');
            $dt = Carbon::parse($startDate);
            $ws->setCellValue('B3', $dt);
            $ws->getStyle('B3')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $ws->getStyle('B3')->getFont()->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ws->mergeCells('B4:'.$lastCol.'4');
            $ws->setCellValue('B4', ($qIndex + 1).'. '.mb_strtoupper($question->question_text));
            $ws->getStyle('B4')->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

            $headerRow = 5;
            $ws->mergeCells('B'.$headerRow.':B'.($headerRow + 1));
            $ws->setCellValue('B'.$headerRow, 'ID');
            $ws->getStyle('B'.$headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('B'.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $ws->mergeCells('C'.$headerRow.':C'.($headerRow + 1));
            $ws->setCellValue('C'.$headerRow, 'PACIENTE');
            $ws->getStyle('C'.$headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle('C'.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $colLetter = 'D';
            if ($hasOptions) {
                foreach ($options as $opt) {
                    $label = $opt['label'] ?? $opt;
                    $ws->mergeCells($colLetter.$headerRow.':'.$colLetter.($headerRow + 1));
                    $ws->setCellValue($colLetter.$headerRow, $label);
                    $ws->getStyle($colLetter.$headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
                    $ws->getStyle($colLetter.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                    $colLetter++;
                }
            }

            $ws->mergeCells($colLetter.$headerRow.':'.$colLetter.($headerRow + 1));
            $ws->setCellValue($colLetter.$headerRow, 'PONDERADO');
            $ws->getStyle($colLetter.$headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle($colLetter.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colLetter++;

            $ws->mergeCells($colLetter.$headerRow.':'.$colLetter.($headerRow + 1));
            $ws->setCellValue($colLetter.$headerRow, 'FECHA');
            $ws->getStyle($colLetter.$headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle($colLetter.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colLetter++;

            $ws->mergeCells($colLetter.$headerRow.':'.$colLetter.($headerRow + 1));
            $ws->setCellValue($colLetter.$headerRow, 'OBSERVACIONES');
            $ws->getStyle($colLetter.$headerRow)->getFont()->setBold(true)->setSize(11)->setName('Aptos Narrow');
            $ws->getStyle($colLetter.$headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 7;
            foreach ($surveys as $survey) {
                $patient = $survey->patient;
                $ws->setCellValue('B'.$row, $patient->id);
                $ws->getStyle('B'.$row)->getFont()->setSize(11)->setName('Aptos Narrow');
                $ws->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $patientName = ($patient->dni ?? '').' '.($patient->name ?? '');
                $ws->setCellValue('C'.$row, $patientName);
                $ws->getStyle('C'.$row)->getFont()->setSize(11)->setName('Aptos Narrow');
                $ws->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $matchedAnswer = null;
                foreach ($survey->answers as $answer) {
                    if ((int) $answer->survey_question_id === $question->id) {
                        $matchedAnswer = $answer;
                        break;
                    }
                }

                $colLetter = 'D';
                if ($hasOptions && $matchedAnswer) {
                    foreach ($options as $opt) {
                        $label = $opt['label'] ?? $opt;
                        if (CalculateSurveyRating::normalize($matchedAnswer->answer_value) === CalculateSurveyRating::normalize($label)) {
                            $ws->setCellValue($colLetter.$row, 'X');
                            $ws->getStyle($colLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                        $colLetter++;
                    }

                    $ws->setCellValue($colLetter.$row, $matchedAnswer->answer_value);
                    $ws->getStyle($colLetter.$row)->getFont()->setSize(11)->setName('Aptos Narrow');
                    $ws->getStyle($colLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $colLetter++;
                } elseif ($hasOptions) {
                    $colLetter = $this->lastColLetter(4 + count($options) - 1 + 2);
                }

                if ($matchedAnswer !== null && $matchedAnswer->weighted_value !== null) {
                    $ws->setCellValue($colLetter.$row, $matchedAnswer->weighted_value);
                    $ws->getStyle($colLetter.$row)->getNumberFormat()->setFormatCode('0.00');
                    $ws->getStyle($colLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } elseif ($question->field_type === 'number' && $matchedAnswer && is_numeric($matchedAnswer->answer_value)) {
                    $ws->setCellValue($colLetter.$row, (float) $matchedAnswer->answer_value);
                    $ws->getStyle($colLetter.$row)->getNumberFormat()->setFormatCode('0.00');
                    $ws->getStyle($colLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } elseif ($question->field_type === 'text' && $matchedAnswer) {
                    $ws->setCellValue($colLetter.$row, $matchedAnswer->answer_value);
                    $ws->getStyle($colLetter.$row)->getFont()->setSize(11)->setName('Aptos Narrow');
                    $ws->getStyle($colLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                $colLetter++;

                if ($survey->created_at) {
                    $ws->setCellValue($colLetter.$row, $survey->created_at);
                    $ws->getStyle($colLetter.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                    $ws->getStyle($colLetter.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $colLetter++;

                $ws->setCellValue($colLetter.$row, '');
                $ws->getStyle($colLetter.$row)->getFont()->setSize(11)->setName('Aptos Narrow');

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
            $letter = chr(65 + ($colIndex % 26)).$letter;
            $colIndex = intdiv($colIndex, 26);
        }

        return $letter;
    }

    private function colIndex(string $colLetter): int
    {
        $index = 0;
        $len = strlen($colLetter);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($colLetter[$i]) - 64);
        }

        return $index;
    }
}
