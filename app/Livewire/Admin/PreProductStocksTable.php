<?php

namespace App\Livewire\Admin;

use App\Models\PreProductStock;
use App\Models\PreProductStockItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PreProductStocksTable extends PowerGridComponent
{
    public string $tableName = 'pre_product_stocks';

    protected $listeners = ['delete'];

    public function setUp(): array
    {
//        $this->showCheckBox();

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
        return PreProductStock::query()->orderByDesc('id');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
//            ->add('short_description', function ($description){
//                return Str::limit($description->short_description,80,'...');
//            })
            ->add('status', function ($product) {
                return $product->status == ACTIVE_STATUS
                    ? '<span class="bg-success text-white px-2 py-1 rounded-md">APPROVED</span>'
                    : '<span class="bg-danger text-white px-2 py-1 rounded-md">PENDING</span>';
            })
            ->add('modify_created_at',function ($product){
                return \Carbon\Carbon::parse($product->created_at)->format('d-M-Y');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')->hidden(),

            Column::make('Title', 'name')->sortable()->searchable(),

//            Column::make('Description', 'short_description')->sortable()->searchable(),

            Column::make('Status', 'status')->sortable()->searchable(),

            Column::add()->title('Created at')->field('modify_created_at', 'modify_created_at'),

            Column::action('Action')
        ];
    }

    public function actions(PreProductStock $item): array
    {
        $actions = [];

        // Delete Button with permission check
        if (auth()->user()->can('delete pre medicine stock')) {
            $actions[] = Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('deleteEvent', ['id' => $item->id]);
        }

        // Edit Button with permission check
        if (auth()->user()->can('view pre medicine stock')) {
            $actions[] = Button::add('edit')
                ->slot('<i class="fa fa-pencil"></i>')
                ->attributes(['wire:navigate href' => route('admin.pre-product-medicine-stock-item', $item->id)])
                ->class('btn btn-warning');
        }

        // Release Stock Button with permission check and conditional status check
        if ($item->status != ACTIVE_STATUS && auth()->user()->can('sync pre medicine stock')) {
            $actions[] = Button::add('Release Stock')
                ->slot('<i class="fa fa-exchange"></i>')
                ->attributes(['wire:click' => "updateProductStock($item->id)"])
                ->class('btn btn-success btn-sm');
        }

        return $actions;
    }


    public function updateProductStock($id)
    {
        DB::beginTransaction();

        try {

            $getStockProductItems = PreProductStockItem::where('pre_product_stock_id', $id)->get();

            if (count($getStockProductItems) == 0){
                DB::rollBack();
                $this->dispatch('toast', type: 'error', message: 'Order Item Not Found!');
                return false;
            }

            foreach ($getStockProductItems as $item) {

                $product = Product::find($item->product_id);

                if (!$product) {
                    $this->dispatch('toast', type: 'error', message: 'Medicine not found!');
                    continue;
                }

                $boxPerPic = $product->box_per_pic ?? 0;
                $boxCount = $item->box ?? 0;
                $pieces = $item->pieces ?? 0;

                $totalStock = ($boxCount * $boxPerPic) + $pieces;

                $product->update([
                    'stock' => $totalStock + $product->stock,
                    'updated_at' => now(),
                ]);
            }

            PreProductStockItem::with(['company', 'product'])
                ->where('pre_product_stock_id', $id)->update([
                    'status' => ACTIVE_STATUS
                ]);

            PreProductStock::find($id)->update([
                'status' => ACTIVE_STATUS
            ]);

            $this->dispatch('pg:eventRefresh-pre_product_stocks');

            $this->dispatch('toast', type: 'success', message: 'Medicine stock updated!');

            DB::commit();

        }catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function delete(\App\Models\PreProductStock $stock): void
    {
        $dltInvoice = $stock->delete();

        if ($dltInvoice) {
            $this->dispatch('pg:eventRefresh-default');
            $this->dispatch('refresh-browser');
            $this->dispatch('toast', type: 'success', message: 'Stock deleted successfully');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Stock not deleted');
        }
    }


}
