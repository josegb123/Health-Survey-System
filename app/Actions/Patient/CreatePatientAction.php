<?php

namespace App\Actions\Patient;

use App\Models\Patient;

class CreatePatientAction
{
    /**
     * Registra un nuevo paciente en el sistema con sus datos clínicos iniciales.
     *
     * @param  array  $data  Datos limpios y validados.
     */
    public function execute(array $data): Patient
    {
        return Patient::create([
            'document_type' => $data['document_type'],
            'dni' => $data['dni'],
            'name' => $data['name'],
            'email' => $data['email'],
            'nationality' => $data['nationality'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'],
            'insurer_id' => $data['insurer_id'] ?? null,
        ]);
    }
}
