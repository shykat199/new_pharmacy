<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class CustomerPendingInvoiceList extends Component
{
    #[Layout('layout.app')]
    #[Title('Customer Pending Invoice List')]
    public function render()
    {
        return view('livewire.admin.customer-pending-invoice-list',[
            'page' => 'Customer Pending Invoice List',
        ]);
    }
}
