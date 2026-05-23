<?php

namespace App\Livewire\Admin\Modal;

use Illuminate\Validation\Rule;
use Livewire\Component;

class UserFinanceModal extends Component
{
    protected $listeners = ['openModalUserFinanceModal','closeModal','userFinance'];
    public $isOpen = false;
    public $userName = '';

    public function openModalUserFinanceModal($userId)
    {

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.modal.user-finance-modal');
    }
}
