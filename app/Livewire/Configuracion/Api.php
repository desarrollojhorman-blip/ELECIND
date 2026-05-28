<?php

namespace App\Livewire\Configuracion;

use App\Models\ApiToken;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.web', ['active' => 'api'])]
#[Title('API')]
class Api extends Component
{
    public bool $showModal    = false;
    public bool $showViewModal   = false;
    public bool $showDeleteModal = false;

    public ?int $editingId  = null;
    public ?int $deletingId = null;
    public ?int $viewingId  = null;

    public string $nombre      = '';
    public string $descripcion = '';
    public string $token       = '';
    public bool   $activo      = true;

    public function mount(): void
    {
        Gate::authorize('api.ver');
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, ApiToken> */
    #[Computed]
    public function apis(): \Illuminate\Database\Eloquent\Collection
    {
        return ApiToken::query()->orderByDesc('created_at')->get();
    }

    public function viewingApi(): ?ApiToken
    {
        return $this->viewingId ? ApiToken::find($this->viewingId) : null;
    }

    public function nuevo(): void
    {
        Gate::authorize('api_tokens.gestionar');
        $this->editingId   = null;
        $this->nombre      = '';
        $this->descripcion = '';
        $this->token       = '';
        $this->activo      = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editar(int $id): void
    {
        Gate::authorize('api_tokens.gestionar');
        $api = ApiToken::findOrFail($id);
        $this->editingId   = $id;
        $this->nombre      = $api->nombre;
        $this->descripcion = $api->descripcion ?? '';
        $this->token       = $api->token;
        $this->activo      = $api->activo;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function generarTokenModal(): void
    {
        $this->token = Str::random(64);
    }

    public function ver(int $id): void
    {
        $this->viewingId     = $id;
        $this->showViewModal = true;
    }

    public function guardar(): void
    {
        Gate::authorize('api_tokens.gestionar');
        $this->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'token'       => ['required', 'string', 'max:80', Rule::unique('api_tokens', 'token')->ignore($this->editingId)],
            'activo'      => ['boolean'],
        ], [
            'nombre.required' => 'El nombre de la aplicación es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'token.required'  => 'El token es obligatorio. Introdúcelo manualmente o usa "Generar".',
            'token.unique'    => 'Este token ya está en uso por otra API.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
        ]);

        if ($this->editingId === null) {
            ApiToken::create([
                'nombre'      => trim($this->nombre),
                'descripcion' => trim($this->descripcion) ?: null,
                'token'       => trim($this->token),
                'activo'      => $this->activo,
                'creado_por'  => (int) auth()->id(),
            ]);
            session()->flash('status', 'API creada correctamente.');
        } else {
            $api              = ApiToken::findOrFail($this->editingId);
            $api->nombre      = trim($this->nombre);
            $api->descripcion = trim($this->descripcion) ?: null;
            $api->token       = trim($this->token);
            $api->activo      = $this->activo;
            $api->save();
            session()->flash('status', 'API actualizada correctamente.');
        }

        $this->showModal = false;
    }

    public function confirmarEliminar(int $id): void
    {
        Gate::authorize('api_tokens.gestionar');
        $this->deletingId     = $id;
        $this->showDeleteModal = true;
    }

    public function eliminar(): void
    {
        Gate::authorize('api_tokens.gestionar');
        if ($this->deletingId !== null) {
            ApiToken::findOrFail($this->deletingId)->delete();
            session()->flash('status', 'API eliminada definitivamente.');
        }

        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    public function regenerarToken(int $id): void
    {
        $api        = ApiToken::findOrFail($id);
        $api->token = Str::random(64);
        $api->save();

        if ($this->viewingId === $id) {
            $this->viewingId = $id;
        }

        session()->flash('status', 'Token regenerado correctamente.');
    }

    public function render(): View
    {
        return view('livewire.configuracion.api', [
            'viewingApi' => $this->viewingApi(),
        ]);
    }
}
