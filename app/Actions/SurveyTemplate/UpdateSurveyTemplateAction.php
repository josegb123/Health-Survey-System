<?php

namespace App\Actions\SurveyTemplate;

use App\Models\SurveyTemplate;

class UpdateSurveyTemplateAction
{
    /**
     * Actualiza una plantilla existente.
     *
     * @param int $id ID de la plantilla.
     * @param array $data Datos a modificar.
     * @return SurveyTemplate
     */
    public function execute(int $id, array $data): SurveyTemplate
    {
        $template = SurveyTemplate::findOrFail($id);

        $template->update([
            'title' => $data['title'] ?? $template->title,
            'is_active' => $data['is_active'] ?? $template->is_active,
        ]);

        return $template;
    }
}
