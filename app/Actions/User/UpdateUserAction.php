<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UpdateUserAction
{
    /**
     * Ejecuta la lógica para actualizar un usuario existente.
     *
     * @param User $user El modelo de usuario inyectado que se va a modificar.
     * @param array $data Los datos ya validados provenientes del formulario/Livewire.
     * @return User
     * @throws Exception
     */
    public function __invoke(User $user, array $data): User
    {
        // Usamos una transacción para asegurar la consistencia si añades más tablas relacionadas en el futuro
        return DB::transaction(function () use ($user, $data) {

            // 1. Filtrar y preparar los datos básicos
            $updateData = [
                'name'  => $data['name'],
                'email' => $data['email'],
                'role'  => $data['role'] ?? $user->role, // Mantiene el rol actual si no se envía
            ];

            // 2. Condición de error / Flujo alternativo: Evitar degradar al único administrador
            if ($user->role === 'admin' && $updateData['role'] !== 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    throw new Exception('No se puede cambiar el rol del único administrador del sistema.');
                }
            }

            // 3. Flujo alternativo para la contraseña
            // Si viene vacía (porque en el modal es opcional al editar), no se modifica.
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            // 4. Actualizar el modelo
            $user->update($updateData);

            return $user;
        });
    }
}
