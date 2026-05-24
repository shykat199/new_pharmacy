<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Medicine extends Component
{
    public $showEditModal = false;
    protected $listeners = ['addProduct', 'delete', 'saveMedicine', 'editProduct', 'updateProduct','changeEvent','addStock','updateType', 'updateStrength'];

    public $productId = '';
    #[Validate('required|unique:products,name')]
    public $name = '';
    #[Validate('required')]
    public $company_id = '';
    #[Validate('required')]
    public $unitPrice = '';
    #[Validate('required')]
    public $box_per_pic = '';
    #[Validate('required')]
    public $low_stock = '';
//    #[Validate('required')]
    public $strength = '';
    #[Validate('required')]
    public $m_type = '';
    public $stock = 0;
    public $status = '';
    public $productName = '';
    public $boxPerPic = '';
    public $pieces = 0;
    public $box = 0;
    public $type = 'add';
    public $qType = '';
    public string $selectedCompany = '';
    public $medicineType;
    public $medicineStrength;

    public function messages()
    {
        return [
            'name.required' => 'The product name is required.',
            'name.unique' => 'This product name is already taken.',
            'company_id.required' => 'Please select a company.',
            'unitPrice.required' => 'Unit price is required.',
            'box_per_pic.required' => 'Box per pic is required.',
            'low_stock.required' => 'Low stock value is required.',
            'm_type.required' => 'Medicine type is required.',
        ];
    }

    public function addProduct()
    {
        $this->showEditModal = false;
        $this->dispatch('openModal', (object)[
            'modalId' => 'createModal',
        ]);
    }

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function editProduct($id)
    {
        $this->showEditModal = true;

        $product = Product::find($id);

        $this->name = $product->name;
        $this->company_id = $product->company_id;
        $this->unitPrice = $product->unit_price;
        $this->box_per_pic = $product->box_per_pic;
        $this->stock = $product->stock;
        $this->status = $product->status;
        $this->productId = $product->id;
        $this->low_stock = $product->low_stock;
        $this->strength = $product->strength ?? '';
        $this->m_type = $product->type ?? '';

        $this->dispatch('showProductEditModalEvent', [$this->company_id]);
        $this->dispatch('changeProductStatus', [$this->status]);
        $this->dispatch('changeProductStrength', [$this->strength]);
        $this->dispatch('changeProductType', [$this->m_type]);

    }

    public function saveMedicine()
    {
        $this->validate();
        Product::create([
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'company_id' => $this->company_id,
            'unit_price' => $this->unitPrice,
            'box_per_pic' => $this->box_per_pic,
            'stock' => $this->stock,
            'low_stock' => $this->low_stock,
            'strength' => $this->strength,
            'type' => $this->m_type,
            'status' => ACTIVE_STATUS,
        ]);
        $this->dispatch('closeModal', (object)[
            'modalId' => 'createModal'
        ]);
        $this->reset();
        $this->resetValidation();
        $this->company_id = '';
        $this->dispatch('toast', type: 'success', message: 'New medicine added successfully');
        $this->dispatch('pg:eventRefresh-products');
    }

    public function updateType($value)
    {
        $this->m_type = $value;
    }

    public function updateStrength($value)
    {
        $this->strength = $value;
    }

    public function updateProduct()
    {

        $productId = $this->productId;

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'unitPrice' => 'required|min:1',
            'box_per_pic' => 'required|min:1',
            'company_id' => 'required',
            'status' => 'required',
            'low_stock' => 'required|integer|min:1',
//            'strength' => 'required',
            'm_type' => 'required',
        ]);

        \App\Models\Product::findOrFail($productId)->update([
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'company_id' => $this->company_id,
            'unit_price' => $this->unitPrice,
            'box_per_pic' => $this->box_per_pic,
            'status' => $this->status,
            'low_stock' => $this->low_stock,
            'strength' => $this->strength ?? '',
            'type' => $this->m_type,
        ]);
        $this->reset();
        $this->dispatch('closeModal', (object)[
            'modalId' => 'createModal'
        ]);
        $this->dispatch('resetSelect2');
        $this->dispatch('toast', type: 'success', message: 'Product updated successfully');
        $this->dispatch('pg:eventRefresh-products');
    }

    public function changeEvent($value, $type = '')
    {
        if ($type === 'companyId') {
            $this->company_id = $value;
        } else {
            $this->status = $value;
        }
    }

    public function delete(\App\Models\Product $product): void
    {
        $delete = $product->delete();
        if ($delete) {
            $this->dispatch('pg:eventRefresh-products');
            $this->dispatch('toast', type: 'success', message: 'Product deleted successfully');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Product not deleted');
        }
    }

    public function addStock($productId)
    {
        $productDetails = Product::with('company')->find($productId);

        $this->stock = $productDetails->stock;
        $this->productName = trim(
            ($productDetails->name ?? '') . ' ' .
            ($productDetails->strength ?? '') . ' ' .
            ($productDetails->type ?? '') . ' ' .
            \Illuminate\Support\Str::limit($productDetails->company->name ?? '', 3, '')
        );
        $this->boxPerPic = $productDetails->box_per_pic;
        $this->productId = $productDetails->id;
    }

    public function updateProductStock()
    {
        $this->validate([
            'type' => 'required'
        ]);

        $productDetails = Product::find($this->productId);
        $totalStock = $productDetails->stock ?? 0;
        $boxPerPic = $productDetails->box_per_pic ?? 0;

        if ($this->type == 'add') {
            $finalTotalStock = $totalStock + (((int)$this->box * $boxPerPic) + (int)$this->pieces);
        } else {
            $finalTotalStock = $totalStock - (((int)$this->box * $boxPerPic) + (int)$this->pieces);

            if ($finalTotalStock < 0){
                $this->dispatch('toast', type: 'error', message: 'Operation would result in negative stock. Try again!');
                return;
            }
        }

        $productDetails->update([
            'stock' => $finalTotalStock
        ]);

        $this->dispatch('closeModal', (object)[
            'modalId' => 'addStock'
        ]);
        $this->reset();
        $this->dispatch('toast', type: 'success', message: 'Medicine stock updated successfully');
        $this->dispatch('pg:eventRefresh-products');
    }

    public function mount()
    {
        $this->qType = request()->get('type', '');
    }


    #[Layout('layout.app')]
    #[Title('Products')]
    public function render()
    {

        $this->medicineType = Product::distinct()->where('products.type','!=','')->orderBy('type') ->pluck('products.type')->toArray();
        $this->medicineStrength = Product::distinct()->where('products.strength','!=','')->orderBy('strength') ->pluck('products.strength')->toArray();

        return view('livewire.admin.medicine', [
            'page' => request()->get('type') == 'lowstock' ? 'Low Medicine Stock list' : 'Medicine list',
            'companies' => Company::select('id', 'name')->where('status', ACTIVE_STATUS)->orderBy('companies.name')->get(),
            'medicineType'=>$this->medicineType,
            'medicineStrength'=>$this->medicineStrength,
        ]);
    }
}
