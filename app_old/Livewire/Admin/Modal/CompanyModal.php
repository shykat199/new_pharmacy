<?php

namespace App\Livewire\Admin\Modal;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CompanyModal extends Component
{
    public $isOpen = false;
    public $modalTitle = 'Modal Title';
    public $actionMethod = '';
    protected $listeners = ['openModal', 'closeModal'];

    public $companyId = '';
    #[Validate('required|unique:companies,name')]
    public $name = '';
    public $status = '';


    public function openModal($title, $method, $company = null)
    {
        $this->modalTitle = $title;
        $this->actionMethod = $method;
        if ($company) {
            $this->companyId   = $company['id'];
            $this->name     = $company['name'];
            $this->status  = $company['status'];
            $this->validate([
                'name' => ['required', Rule::unique('companies')->ignore($this->companyId)],
            ]);
        } else {
            $this->reset(['companyId', 'name']);
        }
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset();
        $this->resetValidation();
    }

    public function executeAction()
    {
        if ($this->actionMethod) {
            $this->dispatch($this->actionMethod);
        }
        $this->closeModal();
    }

    public function saveCompany()
    {
        $this->validate();
        $submittedData = [
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'status' => ACTIVE_STATUS,
        ];

        $this->dispatch('saveCompany', $submittedData);
    }

    public function updateCompany()
    {
        $validationRules=[];

        if ($this->name){
            $validationRules['name'] = ['required',
                Rule::unique('companies', 'name')->ignore($this->companyId)];
        }

        $this->validate($validationRules);

        $submittedData = [
            'companyId' => $this->companyId,
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'status' => $this->status,
        ];

        if ($this->status == INACTIVE_STATUS){
            Product::where('company_id',$this->companyId)->update([
                'status'=>INACTIVE_STATUS
            ]);
        }
        if ($this->status == ACTIVE_STATUS){
            Product::where('company_id',$this->companyId)->update([
                'status'=>ACTIVE_STATUS
            ]);
        }

        $this->dispatch('updateCompany', $submittedData);
    }

    public function render()
    {
        return view('livewire.admin.modal.company-modal');
    }
}
