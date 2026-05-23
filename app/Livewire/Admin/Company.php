<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Company extends Component
{
    public $isOpen = false;
    protected $listeners = ['saveCompany','updateCompany','addCompany','delete'];

    public function addCompany()
    {
        $this->dispatch('openModal', 'Add New Company', 'saveCompany');
    }

    function addNew()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    public function saveCompany($data)
    {
        if (!empty($data)){
            \App\Models\Company::create($data);
        }
        $this->dispatch('closeModal');
        $this->dispatch('toast',type:'success',message:'New company added successfully');
        $this->dispatch('pg:eventRefresh-companies');
    }

    public function updateCompany($data)
    {
        if (!empty($data)){
            $companyId = $data['companyId'];
            \App\Models\Company::find($companyId)->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);
        }
        $this->reset();
        $this->dispatch('closeModal');
        $this->dispatch('toast',type:'success',message:'Company updated successfully');
        $this->dispatch('pg:eventRefresh-companies');
    }

    public function delete(\App\Models\Company $company): void
    {
        $delete = $company->delete();
        if ($delete){
            $this->dispatch('pg:eventRefresh-companies');
            $this->dispatch('toast',type:'success',message:'Company deleted successfully');
        }else{
            $this->dispatch('toast',type:'error',message:'Company not deleted');
        }
    }

    #[Layout('layout.app')]
    #[Title('Company')]
    public function render()
    {
        return view('livewire.admin.company',[
        'page'=>'Company List'
        ]);
    }
}
