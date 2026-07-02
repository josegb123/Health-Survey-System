<?php

namespace App\Livewire\User;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserAction;
use App\Models\User;
use Exception;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = ''; // Requerida para la regla 'confirmed'

    public string $role = '';

    public bool $is_active = true; // Atributo de estado dedicado

    // Estado del modal y control de excepciones
    public ?User $editingUser = null;

    public ?string $errorMessage = null;

    /**
     * Reglas de validación dinámicas basadas en el estado del componente.
     */
    public function rules(): array
    {
        $userId = $this->editingUser?->id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                $userId ? Rule::unique('users', 'email')->ignore($userId) : 'unique:users,email',
            ],
            // 'confirmed' busca automáticamente la propiedad password_confirmation
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Prepara las propiedades y despliega el formulario para la creación.
     */
    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->reset([
            'name',
            'email',
            'password',
            'password_confirmation',
            'role',
            'editingUser',
            'errorMessage',
        ]);

        $this->is_active = true; // Por defecto activo al crear

        $this->js("Flux.modal('user-form-modal').show()");
    }

    /**
     * Carga el estado del modelo y despliega el formulario para la edición.
     */
    public function openEditModal(User $user): void
    {
        $this->resetErrorBag();
        $this->reset(['password', 'password_confirmation', 'errorMessage']);

        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_active = $user->is_active; // Carga el estado real de la DB

        $this->role = method_exists($user, 'getRoleNames')
            ? ($user->getRoleNames()->first() ?? '')
            : ($user->role ?? '');

        $this->js("Flux.modal('user-form-modal').show()");
    }

    /**
     * Invoca de forma segura la acción de negocio para eliminar un usuario.
     */
    public function deleteUser(int $userId, DeleteUserAction $deleteUser): void
    {
        $user = User::find($userId);

        if (! $user) {
            $this->errorMessage = __('The user no longer exists or has already been deleted.');

            return;
        }

        try {
            $deleteUser($user);

            Flux::toast(
                heading: __('User deleted'),
                text: __('The user has been deleted successfully.'),
                variant: 'success',
            );

            $this->reset('errorMessage');

        } catch (Exception $e) {
            $this->errorMessage = __($e->getMessage());
        }
    }

    /**
     * Procesa de forma unificada el guardado o actualización de la entidad.
     */
    public function saveUser(CreateUserAction $createUser, UpdateUserAction $updateUser): void
    {
        $validated = $this->validate();

        // Condición de limpieza: Se remueve la confirmación para evitar fallos de persistencia en las Actions
        unset($validated['password_confirmation']);

        try {
            if ($this->editingUser) {
                $updateUser($this->editingUser, $validated);

                Flux::toast(
                    heading: __('User updated'),
                    text: __('The user has been updated successfully.'),
                    variant: 'success',
                );
            } else {
                $createUser($validated);

                Flux::toast(
                    heading: __('User created'),
                    text: __('The user has been created successfully.'),
                    variant: 'success',
                );
            }

            $this->js("Flux.modal('user-form-modal').close()");
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'role', 'editingUser', 'errorMessage']);

        } catch (Exception $e) {
            $this->errorMessage = __($e->getMessage());
        }
    }

    /**
     * Renderiza la vista asociada al listado de usuarios.
     */
    public function render(): View
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));
        }

        return view('livewire.users.index', [
            'users' => User::latest()->paginate(10),
        ]);
    }
}
