<?php

namespace App\Livewire\Admin;

use App\Models\Insurer;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InsurerIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?int $selectedInsurerId = null;

    public string $selectedInsurerName = '';

    public string $name = '';

    public string $type = 'contributory';

    public bool $is_active = true;

    public string $editName = '';

    public string $editType = 'contributory';

    public bool $editIsActive = true;

    public mixed $importFile = null;

    public string $duplicateName = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string|in:contributory,subsidized',
        'is_active' => 'boolean',
    ];

    public function openCreateModal(): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        $this->reset(['name', 'type', 'is_active']);
        $this->is_active = true;
        $this->modal('create-insurer-modal')->show();
    }

    public function showImportModal(): void
    {
        $this->importFile = null;
        $this->modal('import-modal')->show();
    }

    public function openEditModal(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        $insurer = Insurer::findOrFail($id);
        $this->selectedInsurerId = $insurer->id;
        $this->editName = $insurer->name;
        $this->editType = $insurer->type;
        $this->editIsActive = $insurer->is_active;
        $this->modal('edit-insurer-modal')->show();
    }

    public function saveInsurer(): void
    {
        $this->validate();

        $exists = Insurer::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($this->name)])
            ->exists();

        if ($exists) {
            $this->duplicateName = $this->name;
            $this->modal('create-insurer-modal')->close();
            $this->modal('duplicate-name-modal')->show();

            return;
        }

        try {
            Insurer::create([
                'name' => $this->name,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);

            $this->modal('create-insurer-modal')->close();
            $this->reset(['name', 'type', 'is_active']);
            $this->dispatch('toast', type: 'success', text: __('Insurer created successfully.'));
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: __('Failed to create insurer: :error', ['error' => $e->getMessage()]));
        }
    }

    public function updateInsurer(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editType' => 'required|string|in:contributory,subsidized',
            'editIsActive' => 'boolean',
        ]);

        $insurer = Insurer::findOrFail($this->selectedInsurerId);

        $exists = Insurer::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($this->editName)])
            ->where('id', '!=', $this->selectedInsurerId)
            ->exists();

        if ($exists) {
            $this->duplicateName = $this->editName;
            $this->modal('edit-insurer-modal')->close();
            $this->modal('duplicate-name-modal')->show();

            return;
        }

        try {
            $insurer->update([
                'name' => $this->editName,
                'type' => $this->editType,
                'is_active' => $this->editIsActive,
            ]);

            $this->modal('edit-insurer-modal')->close();
            $this->reset(['selectedInsurerId', 'editName', 'editType', 'editIsActive']);
            $this->dispatch('toast', type: 'success', text: __('Insurer updated successfully.'));
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: __('Failed to update insurer: :error', ['error' => $e->getMessage()]));
        }
    }

    public function confirmToggleStatus(int $id, string $name): void
    {
        $this->selectedInsurerId = $id;
        $this->selectedInsurerName = $name;
        $this->modal('status-modal')->show();
    }

    public function toggleStatus(): void
    {
        try {
            $insurer = Insurer::findOrFail($this->selectedInsurerId);
            $insurer->update(['is_active' => ! $insurer->is_active]);
            $this->modal('status-modal')->close();
            $this->reset(['selectedInsurerId', 'selectedInsurerName']);
            $this->dispatch('toast', type: 'success', text: __('Insurer status updated.'));
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: __('Failed to update status: :error', ['error' => $e->getMessage()]));
        }
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->selectedInsurerId = $id;
        $this->selectedInsurerName = $name;
        $this->modal('delete-modal')->show();
    }

    public function deleteInsurer(): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        try {
            $insurer = Insurer::findOrFail($this->selectedInsurerId);

            if ($insurer->patients()->exists()) {
                $this->modal('delete-modal')->close();
                $this->reset(['selectedInsurerId', 'selectedInsurerName']);
                $this->dispatch('toast', type: 'error', text: __('Cannot delete an insurer that has associated patients.'));

                return;
            }

            $insurer->delete();
            $this->modal('delete-modal')->close();
            $this->reset(['selectedInsurerId', 'selectedInsurerName']);
            $this->dispatch('toast', type: 'success', text: __('Insurer deleted successfully.'));
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: __('Failed to delete insurer: :error', ['error' => $e->getMessage()]));
        }
    }

    public function exportInsurers(): StreamedResponse
    {
        $insurers = Insurer::all()->map(fn ($i) => [
            'id' => $i->id,
            'name' => $i->name,
            'type' => $i->type,
            'is_active' => $i->is_active,
        ])->toArray();

        $data = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'insurers' => $insurers,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'aseguradoras-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importInsurers(): void
    {
        $this->validate(['importFile' => 'required|file|json|max:512']);

        $content = file_get_contents($this->importFile->getRealPath());

        if ($content === false) {
            $this->addError('importFile', __('Could not read the uploaded file.'));

            return;
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('importFile', __('The uploaded file is not valid JSON.'));

            return;
        }

        if (! isset($data['insurers']) || ! is_array($data['insurers'])) {
            $this->addError('importFile', __('The JSON file does not contain valid insurer data.'));

            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($data['insurers'] as $item) {
            if (empty($item['name']) || empty($item['type'])) {
                $skipped++;

                continue;
            }

            $exists = Insurer::withTrashed()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($item['name'])])
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            Insurer::create([
                'name' => $item['name'],
                'type' => $item['type'],
                'is_active' => $item['is_active'] ?? true,
            ]);

            $created++;
        }

        $this->importFile = null;

        $message = __(':created insurer(s) imported, :skipped skipped (duplicates or invalid).', [
            'created' => $created,
            'skipped' => $skipped,
        ]);

        $this->dispatch('toast', type: $created > 0 ? 'success' : 'warning', text: $message);
    }

    public function render()
    {
        return view('livewire.admin.insurer-index', [
            'insurers' => Insurer::withCount('patients')->latest()->paginate(10),
        ]);
    }
}
