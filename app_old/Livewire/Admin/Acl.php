<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Acl extends Component
{
    public $getAllRole=[];
    public $showEditModal = false;
    public $name='';
    protected $listeners = ['open-edit-role-modal' => 'loadRole'];
    public $role_id;
    public $role_name;


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

        $this->dispatch('showProductEditModalEvent', [$this->company_id]);

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
            'status' => '1',
        ]);
        $this->dispatch('closeModal', (object)[
            'modalId' => 'createModal'
        ]);
        $this->reset();
        $this->dispatch('toast', type: 'success', message: 'New medicine added successfully');
        $this->dispatch('pg:eventRefresh-products');
    }

    public function updateProduct()
    {
        $validationRules = [];

        if ($this->name) {
            $validationRules['name'] = ['required',
                Rule::unique('products', 'name')->ignore($this->productId)];

            $this->validate($validationRules);
        }

        $productId = $this->productId;

        \App\Models\Product::find($productId)->update([
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'company_id' => $this->company_id,
            'unit_price' => $this->unitPrice,
            'box_per_pic' => $this->box_per_pic,
            'stock' => $this->stock,
            'status' => $this->status,
        ]);
        $this->reset();
        $this->dispatch('closeModal', (object)[
            'modalId' => 'createModal'
        ]);
        $this->dispatch('resetSelect2');
        $this->dispatch('toast', type: 'success', message: 'Product updated successfully');
        $this->dispatch('pg:eventRefresh-products');
    }

    public function addProduct()
    {
        $this->showEditModal = false;
        $this->dispatch('openModal', (object)[
            'modalId' => 'createModal',
        ]);
    }

    public function loadRole($id)
    {
        $role = Role::find($id);
        $this->role_id = $role->id;
        $this->role_name = $role->name;

        $this->dispatch('openModal', (object)[
            'modalId' => 'editRoleModal',
        ]);
    }

    public function closeModal()
    {
        $this->reset(['name', 'showEditModal']);
        $this->resetValidation();
    }
    public function mount()
    {
       $this->getAllRole =  Role::get();
    }


    public function editRole($id)
    {
        $role = Role::find($id);
        $this->dispatch('openModal',[
            'modalId' => 'createModal',
            'edit_modal'=> true,
            'productId'=>$id
        ]);
        $this->name = $role->name;
    }

    #[Layout('layout.app')]
    #[Title('User Acl')]
    public function render()
    {
        return view('livewire.admin.acl',[
            'page' => 'User Role'
        ]);
    }
}
