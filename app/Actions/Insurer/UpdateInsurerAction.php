<?php

namespace App\Actions\Insurer;

use App\Models\Insurer;

class UpdateInsurerAction
{
    /**
     * Actualiza los datos de una aseguradora existente.
     *
     * * @param int $id Identificador único de la aseguradora.
     * @param  array  $data  Datos validados con los cambios a aplicar.
     */
    public function execute(int $id, array $data): Insurer
    {
        // findOrFail lanza automáticamente una excepción 404 si el ID fue alterado en la UI
        $insurer = Insurer::findOrFail($id);

        $insurer->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'is_active' => $data['is_active'] ?? $insurer->is_active,
        ]);

        return $insurer;
    }
}
