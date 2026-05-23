<?php

namespace App\Livewire\Admin;

use App\Models\PreProductStockItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PreOrderStockItemTable extends PowerGridComponent
{
    public string $tableName = 'pre_product_stock_items';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return PreProductStockItem::query()->with(['product','company'])->orderByDesc('id');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('company',function ($product){
                return $product->company->name;
            })
            ->add('product',function ($product){
                return $product->product->name;
            })
            ->add('box')
            ->add('pieces')
            ->add('status', function ($product) {
                return $product->status == ACTIVE_STATUS
                    ? '<span class="bg-success text-white px-2 py-1 rounded-md">Active</span>'
                    : '<span class="bg-danger text-white px-2 py-1 rounded-md">Inactive</span>';
            })
            ->add('modify_created_at',function ($product){
                return \Carbon\Carbon::parse($product->created_at)->format('d-M-Y');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Company', 'company'),
            Column::make('Product', 'product'),
            Column::make('Box', 'box')->sortable()->searchable(),

            Column::make('Pieces', 'pieces')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'modify_created_at', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(PreProductStockItem $item): array
    {
        $actions = [
            // Release Stock Button with permission check
            auth()->user()->can('sync pre medicine stock') ? Button::add('Release Stock')
                ->slot('<i class="fa fa-exchange"></i>')
                ->class('btn btn-success btn-sm')
                ->dispatch('deleteEvent', [
                    'id' => $item->id,
                    'stock' => 'release_stock'
                ]) : null,

            // Edit Button with permission check
            auth()->user()->can('view pre medicine stock') ? Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->attributes(['wire:click.prevent' => "editMedicineStock($item->id)"])
                ->class('btn btn-primary btn-sm') : null,

            // Delete Button with permission check
            auth()->user()->can('delete pre medicine stock') ? Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('deleteEvent', ['id' => $item->id]) : null,
        ];


        return array_filter($actions, fn($action) => $action !== null);
    }

    public function editMedicineStock($id)
    {
        $this->dispatch('openModal',[
            'modalId' => 'createModal',
            'edit_modal'=> true,
            'productId'=>$id
        ]);
    }

}
