<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class CreateUserAction
{
    /**
     * Ejecuta la lógica para crear un usuario y sus relaciones.
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function __invoke(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);


            $user->assignRole($data['role']);
            // Ejemplo de flujo alternativo: Asignación de rol si existe en la petición
            if (!empty($data['roles'])) {
                $user->roles()->attach($data['roles']);
            }

            return $user;
        });
    }
}
