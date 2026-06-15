<?php

namespace App\Actions\Insurer;

use App\Models\Insurer;

class CreateInsurerAction
{
    /**
     * Registra una nueva aseguradora en el sistema.
     * * @param array $data Datos ya validados desde la capa superior.
     * @return Insurer
     */
    public function execute(array $data): Insurer
    {
        return Insurer::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
