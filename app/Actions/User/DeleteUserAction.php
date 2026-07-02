<?php

namespace App\Actions\User;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    /**
     * Ejecuta la eliminación de un usuario de forma segura.
     *
     * @throws Exception
     */
    public function __invoke(User $user): bool
    {
        return DB::transaction(function () use ($user) {

            // Condición de error: No permitir que el usuario se elimine a sí mismo
            if ($user->id === auth()->id()) {
                throw new Exception('You cannot delete your own user account while logged in.');
            }

            // Condición de error: No permitir eliminar al único administrador
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    throw new Exception('Operation cancelled. There must be at least one administrator in the system.');
                }
            }

            // Flujo alternativo: Si tuviera relaciones complejas (ej. posts, logs) se limpian aquí
            // $user->profiles()->delete();

            return (bool) $user->delete();
        });
    }
}
