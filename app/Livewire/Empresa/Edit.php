<?php

namespace App\Livewire\Empresa;

use App\Livewire\Forms\EmpresaForm;
use App\Models\Empresa;
use App\Support\Branding;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.web', ['active' => 'empresa'])]
#[Title('Configuración de empresa')]
class Edit extends Component
{
    use WithFileUploads;

    public EmpresaForm $form;

    public function mount(): void
    {
        $empresa = Empresa::actual();

        Gate::authorize('update', $empresa);

        $this->form->fromModel($empresa);
    }

    public function deshacer(): void
    {
        $this->resetValidation();
        $this->form->fromModel(Empresa::actual());
    }

    public function guardar(): void
    {
        \Log::info('[EMPRESA DEBUG] guardar() iniciado');

        $empresa = Empresa::actual();
        Gate::authorize('update', $empresa);

        try {
            $this->form->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[EMPRESA DEBUG] Validación fallida', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('[EMPRESA DEBUG] Error inesperado en save()', ['error' => $e->getMessage()]);
            throw $e;
        }

        Branding::limpiarCache();

        \Log::info('[EMPRESA DEBUG] Guardado OK, redirigiendo');

        session()->flash('status', 'Empresa actualizada correctamente.');

        $this->redirect(route('configuracion.empresa'), navigate: true);
    }

    public function quitarLogo(): void
    {
        $this->form->eliminarLogo = true;
        $this->form->nuevoLogo = null;
    }

    public function cancelarQuitarLogo(): void
    {
        $this->form->eliminarLogo = false;
    }

    public function descartarNuevoLogo(): void
    {
        $this->form->nuevoLogo = null;
    }


    public function render(): View
    {
        return view('livewire.empresa.edit');
    }
}
