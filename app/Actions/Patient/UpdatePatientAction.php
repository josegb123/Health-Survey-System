<?php

namespace App\Actions\Patient;

use App\Models\Patient;

class UpdatePatientAction
{
    /**
     * Actualiza la ficha de información de un paciente existente.
     *
     * @param int $id Identificador único del paciente.
     * @param array $data Datos validados con las modificaciones.
     * @return Patient
     */
    public function execute(int $id, array $data): Patient
    {
        $patient = Patient::findOrFail($id);

        // Take the data from array and if its null set the existent data of the row
        $patient->update([
            'document_type' => $data['document_type'] ?? $patient->document_type,
            'dni' => $data['dni'] ?? $patient->dni,
            'name' => $data['name'] ?? $patient->name,
            'email' => $data['email'] ?? $patient->email,
            'nationality' => $data['nationality'] ?? $patient->nationality,
            'address' => $data['address'] ?? $patient->address,
            'phone' => $data['phone'] ?? $patient->phone,
            'insurer_id' => $data['insurer_id'] ?? $patient->insurer_id,
        ]);

        return $patient;
    }
}
