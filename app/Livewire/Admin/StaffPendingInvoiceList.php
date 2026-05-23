<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class StaffPendingInvoiceList extends Component
{
    #[Layout('layout.app')]
    #[Title('Staff Pending Invoice List')]
    public function render()
    {
        return view('livewire.admin.staff-pending-invoice-list',[
            'page' => 'Staff Pending Invoice List',
        ]);
    }
}
