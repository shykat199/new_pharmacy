<?php

namespace App\Livewire\Admin;

use App\Models\PreProductStock;
use App\Models\PreProductStockItem;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PreProductStocks extends Component
{

    public $showEditModal = false;
    #[Validate('required')]
    public $name = '';
    public $short_description = '';
    public $status = INACTIVE_STATUS;

    public function addMedicineStock()
    {
        $this->showEditModal = false;
        $this->dispatch('openModal', (object)[
            'modalId' => 'createModal',
        ]);
        $this->reset();
    }

    public function closeModal(): void
    {
        $this->reset();
        $this->resetValidation();
    }

    public function saveMedicineStock()
    {
        $this->validate();

        PreProductStock::create([
            'name' => $this->name,
            'short_description' => $this->short_description,
            'status' => $this->status,
        ]);

        $this->dispatch('closeModal', (object)[
            'modalId' => 'createModal'
        ]);
        $this->reset();
        $this->dispatch('toast', type: 'success', message: 'New stock added successfully');
        $this->dispatch('pg:eventRefresh-pre_product_stocks');
    }

    #[Layout('layout.app')]
    #[Title('Pre Product Stock')]
    public function render()
    {
        return view('livewire.admin.pre-product-stocks', [
            'page' => 'Pre Product Medicine Stock list',
            'totalDueAmount' =>\App\Models\User::where('role', USER_ROLE)->where('status', ACTIVE_STATUS)->sum('balance')
        ]);
    }
}
