<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\OrderItem;
use App\Models\PreProductStock;
use App\Models\PreProductStockItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Str;

class PreOrderStockItem extends Component
{

    public $name;
    public $updated_at;
    public $stockStatus;
    public $short_description;
    public $pre_product_stock_id = 0;
    public $stockItems = null;
    public $company_id;
    public $product_id = [];
    public $box = [];
    public $pieces = [];
    public $isAutoSaving = false;

    protected $listeners = ['changeEvent', 'autoSave'];

    protected function rules()
    {
        return [
            'name' => 'required',
            'company_id' => 'required',
        ];
    }

    protected function messages()
    {
        return [
            'company_id.required' => 'The company field is mandatory.',
        ];
    }

//    public function autoSave()
//    {
//
//        $this->isAutoSaving = true;
//
//        try {
//            \DB::transaction(function () {
//
//                $this->saveData(true);
//            });
//
//        } catch (\Exception $e) {
//            logger()->error('Auto-save failed: ' . $e->getMessage());
//        } finally {
//            $this->isAutoSaving = false;
//        }
//    }

    public function saveMedicineStock()
    {
        $this->validate();
        $this->saveData();
        $this->dispatch('toast', type: 'success', message: 'Medicine stock updated successfully');
    }

    private function saveData($autoSave = false)
    {
        $stockData = [
            'name' => $this->name,
            'short_description' => $this->short_description,
        ];

        $stock = PreProductStock::updateOrCreate(
            ['id' => $this->pre_product_stock_id],
            $stockData
        );

        $itemsData = [];


        foreach ($this->product_id as $index => $productId) {
            $itemsData[] = [
                'pre_product_stock_id' => $stock->id,
                'company_id' => $this->company_id,
                'product_id' => $productId,
                'box' => $this->box[$index] ?? 0,
                'pieces' => $this->pieces[$index] ?? 0,
                'status' => PENDING_STATUS,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

//        $checkStockItem = PreProductStockItem::where('pre_product_stock_id', $this->pre_product_stock_id)
//            ->where('company_id', $this->company_id)->get();

        PreProductStockItem::where('pre_product_stock_id', $this->pre_product_stock_id)->delete();

        PreProductStockItem::insert($itemsData);

        $this->stockItems = PreProductStockItem::with(['company', 'product'])
            ->where('pre_product_stock_id', $this->pre_product_stock_id)->get();
    }

    public function changeEvent($value, $type = ''): void
    {
        $this->company_id = $value;

        if ($type == 'changeCompany') {

            $getStockProductItem = PreProductStockItem::whereHas('product',function ($product){
                $product->where('status',ACTIVE_STATUS);
            })->with(['company', 'product'])
                ->where('company_id', $this->company_id)
                ->where('pre_product_stock_id', $this->pre_product_stock_id)
                ->orderBy(
                    Product::select('name')
                        ->whereColumn('products.id', 'pre_product_stock_items.product_id')
                )
                ->get();

            if (count($getStockProductItem) == 0) {
                $this->reset('box', 'pieces');
            } else {
                foreach ($getStockProductItem as $key => $item) {
                    $this->box[$key] = $item->box;
                    $this->pieces[$key] = $item->pieces;
                    $this->company_id = $item->company_id;
                    $this->product_id[$key] = $item->product_id;
//                    $this->medicine_name[$key] = $item->product->name ?? $item->product->product->name.' '.Str::limit($item->product->strength,'3',' ').Str::limit($item->product->type,'3',' - ');
                }
            }
        }
        else {
            $products = Product::with('company')->where('company_id', $this->company_id)
                ->where('status',ACTIVE_STATUS)
                ->orderBy('name')->orderBy('products.name')->get();

//            $products->each(function ($product) {
//                $productStrength = Str::limit($product->strength,5,' ');
//                $product->name = "{$product->name} {$productStrength} {$product->type}";
//            });

            $this->stockItems = $products;

            $productIds = [];

            foreach ($this->stockItems as $key => $product) {
                $productIds[$key] = $product->id;
            }

            if (!empty($productIds)) {
                $this->product_id = array_merge([], $productIds);
            }
        }
    }

    public function mount($id)
    {
        $preProductDetails = PreProductStock::find($id);
        $this->pre_product_stock_id = $id;
        $this->name = $preProductDetails->name;
        $this->short_description = $preProductDetails->short_description;
        $this->updated_at = $preProductDetails->updated_at;
        $this->stockStatus = $preProductDetails->status;

        $getStockProductItem = PreProductStockItem::whereHas('product',function ($product){
            $product->where('status',ACTIVE_STATUS);
        })->with(['company', 'product'])
            ->where('pre_product_stock_id', $this->pre_product_stock_id)
            ->orderByRaw("CASE WHEN (box IS NOT NULL AND box > 0) OR (pieces IS NOT NULL AND pieces > 0) THEN 0 ELSE 1 END")
            ->orderBy(
                Product::select('name')
                    ->whereColumn('products.id', 'pre_product_stock_items.product_id')
            )
            ->get();

        if (!empty($getStockProductItem)) {

            $this->stockItems = $getStockProductItem;
            foreach ($getStockProductItem as $key => $item) {
                $this->box[$key] = $item->box;
                $this->pieces[$key] = $item->pieces;
                $this->company_id = $item->company_id;
                $this->product_id[$key] = $item->product_id;
            }
        }
    }

    public function updateProductStock()
    {
        DB::beginTransaction();
        try {


            if(empty($this->company_id) || empty($this->product_id)){
                $this->dispatch('toast', type: 'error', message: 'Medicine item is not selected!');
                return true;
            }

            $this->saveData();

            $getStockProductItems = PreProductStockItem::where('pre_product_stock_id', $this->pre_product_stock_id)->get();

            foreach ($getStockProductItems as $item) {

                $product = Product::where('id',$item->product_id)->where('status',ACTIVE_STATUS)->first();

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

            $preProductStock = PreProductStock::find($this->pre_product_stock_id);

            $preProductStock->update([
                'status' => ACTIVE_STATUS
            ]);

            $this->stockStatus = ACTIVE_STATUS;

            $this->stockItems = PreProductStockItem::where('pre_product_stock_id', $this->pre_product_stock_id)->get();

            $this->dispatch('toast', type: 'success', message: 'Medicine stock approved successfully!');

            DB::commit();

        }catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Failed to update order: ' . $e->getMessage());
        }
    }


    #[Layout('layout.app')]
    #[Title('Pre Product Stock')]
    public function render()
    {
        return view('livewire.admin.pre-order-stock-item', [
            'title1' => 'Product Stock Details',
            'title2' => 'Add Stock Item',
            'title3' => 'Stock Item List',
            'companies' => Company::select('id', 'name')->where('status', ACTIVE_STATUS)->get()
        ]);
    }
}
