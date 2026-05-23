<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class UserInvoiceList extends Component
{
    public $userId;

    public function mount($id): void
    {
        $this->userId = $id;
    }

    #[Layout('layout.app')]
    #[Title('Customers Invoice List')]
    public function render()
    {
        return view('livewire.admin.user-invoice-list',[
            'page'=>'Customers Invoice List'
        ]);
    }
}
