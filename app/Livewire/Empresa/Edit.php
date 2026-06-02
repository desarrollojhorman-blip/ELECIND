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

        Gate::authorize('view', $empresa);

        $this->form->fromModel($empresa);
    }

    public function deshacer(): void
    {
        $this->resetValidation();
        $this->form->fromModel(Empresa::actual());
    }

    public function guardar(): void
    {
        $empresa = Empresa::actual();
        Gate::authorize('update', $empresa);

        $this->form->save();

        Branding::limpiarCache();

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
        return view('livewire.empresa.edit', [
            'puedeEditar' => Gate::allows('update', Empresa::actual()),
        ]);
    }
}
