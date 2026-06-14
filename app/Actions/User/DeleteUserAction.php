<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class DeleteUserAction
{
    /**
     * Ejecuta la eliminación de un usuario de forma segura.
     *
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function __invoke(User $user): bool
    {
        return DB::transaction(function () use ($user) {

            // Condición de error: No permitir que el usuario se elimine a sí mismo
            if ($user->id === auth()->id()) {
                throw new Exception('No puedes eliminar tu propia cuenta de usuario en sesión.');
            }

            // Condición de error: No permitir eliminar al único administrador
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    throw new Exception('Operación cancelada. Debe existir al menos un administrador en el sistema.');
                }
            }

            // Flujo alternativo: Si tuviera relaciones complejas (ej. posts, logs) se limpian aquí
            // $user->profiles()->delete();

            return (bool) $user->delete();
        });
    }
}
