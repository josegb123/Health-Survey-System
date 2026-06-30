<?php

namespace App\Actions\SurveyAnswer;

use App\Models\SurveyAnswer;
use Carbon\Carbon;

class CreateSurveyAnswersAction
{
    /**
     * Guarda todas las respuestas de una encuesta en una sola consulta SQL.
     *
     * @param  int  $surveyId  ID de la cabecera de la encuesta (Survey).
     * @param  array  $answers  Array asociativo donde la llave es el 'survey_question_id' y el valor es la respuesta.
     *                          Ejemplo: [ 1 => 'Yes', 2 => 'Good service', 3 => '4' ]
     */
    public function execute(int $surveyId, array $answers): bool
    {
        if (empty($answers)) {
            return false;
        }

        $now = Carbon::now();
        $rowsToInsert = [];

        // Estructuramos el set de datos en un array plano de filas para SQL
        foreach ($answers as $questionId => $answerValue) {
            $rowsToInsert[] = [
                'survey_id' => $surveyId,
                'survey_question_id' => $questionId,
                'answer_value' => is_array($answerValue) ? json_encode($answerValue) : (string) $answerValue,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Ejecuta un único INSERT masivo: INSERT INTO survey_answers (...) VALUES (...), (...), (...)
        return SurveyAnswer::insert($rowsToInsert);
    }
}
