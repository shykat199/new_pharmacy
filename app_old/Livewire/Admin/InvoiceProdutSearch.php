<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;

class InvoiceProdutSearch extends Component
{
    public $query = '';
    public $products = [];
    public $selectedProduct = null;
    public $index;
    protected $listeners = ['closeModal'];

    public function updatedQuery()
    {
        $this->products = Product::with('company')
            ->where('status',ACTIVE_STATUS)
            ->where('stock' ,'>', 0)
            ->where('name', 'like', $this->query.'%')
            ->get()->toArray();
    }

    public function selectProduct($productId, $index): void
    {
        $this->selectedProduct = Product::find($productId);
        $this->query = $this->selectedProduct->name;
        $this->products = [];

        if (!empty($this->selectedProduct)) {

            $fullName = $this->selectedProduct->name . ' ' .
                \Str::limit($this->selectedProduct->strength,3,'') . ' ' .
                \Str::limit($this->selectedProduct->type,3,'') . ' - ' .
                \Str::limit($this->selectedProduct->company?->name,3,'');

            $this->query = trim($fullName);
            $this->products = [];


            $this->dispatch('productSelected', [
                'index' => $index,
                'product_id' => $this->selectedProduct->id,
                'strength' => $this->selectedProduct->strength,
                'company_id' => $this->selectedProduct->company_id,
                'price' => $this->selectedProduct->unit_price,
                'stock' => $this->selectedProduct->stock,
                'name' => $this->selectedProduct->name,
                'unit_price' => $this->selectedProduct->unit_price,
                'box_per_pic' => $this->selectedProduct->box_per_pic,
            ]);

            $this->selectedProduct = [];
        }
    }

    public function mount($index, $value = null)
    {

        $this->index = $index;
        $this->query = $value ?? '';
    }

    public function closeModal()
    {
        $this->query = '';
    }


    public function render()
    {
        return view('livewire.admin.invoice-produt-search');
    }
}
