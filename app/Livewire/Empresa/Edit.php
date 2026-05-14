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

    public function guardar(): void
    {
        $empresa = Empresa::actual();
        Gate::authorize('update', $empresa);

        $this->form->save();

        Branding::limpiarCache();

        session()->flash('status', 'Configuración de empresa actualizada correctamente.');
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

    public function quitarLogoAlbaran(): void
    {
        $this->form->eliminarLogoAlbaran = true;
        $this->form->nuevoLogoAlbaran = null;
    }

    public function cancelarQuitarLogoAlbaran(): void
    {
        $this->form->eliminarLogoAlbaran = false;
    }

    public function descartarNuevoLogoAlbaran(): void
    {
        $this->form->nuevoLogoAlbaran = null;
    }

    public function render(): View
    {
        return view('livewire.empresa.edit');
    }
}
