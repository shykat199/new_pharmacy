<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class OrderInvoice extends Component
{
    #[Layout('layout.app')]
    #[Title('User Invoice List')]
    public function render()
    {
        $segment = \Request::segment(2);

        return view('livewire.admin.order-invoice',[
            'page' => 'All Invoice',
            'segment' => $segment,
        ]);
    }
}
