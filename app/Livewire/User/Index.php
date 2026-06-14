<?php

namespace App\Livewire\User;

use App\Models\User;
use App\Actions\User\CreateUserAction;
use App\Actions\User\UpdateUserAction;
use App\Actions\User\DeleteUserAction;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Exception;

class Index extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';

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
                $userId ? Rule::unique('users', 'email')->ignore($userId) : 'unique:users,email'
            ],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
        ];
    }

    /**
     * Prepara las propiedades y despliega el formulario para la creación.
     */
    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['name', 'email', 'password', 'role', 'editingUser', 'errorMessage']);

        $this->js("Flux.modal('user-form-modal').show()");
    }

    /**
     * Carga el estado del modelo y despliega el formulario para la edición.
     */
    public function openEditModal(User $user): void
    {
        $this->resetErrorBag();
        $this->reset(['password', 'errorMessage']);

        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;

        // Flujo alternativo: Soporte para Spatie Permission (getRoleNames) o propiedad nativa (role)
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

        if (!$user) {
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
            $this->reset(['name', 'email', 'password', 'role', 'editingUser', 'errorMessage']);

        } catch (Exception $e) {
            // Si la excepción fue lanzada manualmente desde nuestra Action con texto en inglés, se traduce.
            $this->errorMessage = __($e->getMessage());
        }
    }

    /**
     * Renderiza la vista asociada al listado de usuarios.
     */
    public function render(): View
    {
        return view('livewire.users.index', [
            'users' => User::latest()->paginate(10)
        ]);
    }
}
