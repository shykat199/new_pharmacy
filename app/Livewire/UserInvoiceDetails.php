<?php

namespace App\Livewire;

use App\Models\Invoices;
use App\Models\OrderItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UserInvoiceDetails extends Component
{

    public $invoiceId = 0;
    public $invoiceDetails = null;
    public $orderItems = null;
    public $orderInvoiceItemId = null;

    public $rows = [];
    public $new_user_rows = [];
    public $order_item = [];
    public $searchedUsers = [];
    public $selectedUsers = null;

    public $userId = '';
    public $showEditModal = false ;

    protected $listeners = ['productSelected','deleteItem' ,'saveCustomer', 'openModal' ,'doubleSpacePressed', 'updateDueAmount', 'addOtherChargeAmount', 'fileDownloaded','updateGrandTotal','updateFinalGrandTotal','approveInvoice','sendAdminApproval'];
    public $calculatedBoxQty = 0;
    public $calculatedQty = 0;

    public $user_invoice_totalMedicinePrice = 0;
    public $user_invoice_grandTotal = 0;
    public $user_invoice_dueAmount = 0;

    public $grandTotal = 0;
    public $paidAmount = 0;
    public $otherCharge = 0;
    public $dueAmount = 0;
    public $totalProductQty = 0;
    public $totalPrice = [];
    public $new_order_items = [];
    public $totalPriceWithoutDiscount = [];
    public $totalDiscount = [];
    public $removedOrderItemIds = [];
    public $totalMedicinePrice = 0;

    public function mount($id): void
    {
        $this->invoiceId = $id;
        $this->invoiceDetails = Invoices::with(['user'])->find($this->invoiceId);
        $this->orderItems = OrderItem::with(['product'])->where('invoice_id', $this->invoiceId)->get();

        $this->new_user_rows = [
            ['product' => '', 'user_invoice_qty' => '', 'user_invoice_pieces' => '', 'user_invoice_price' => 0, 'stock' => '', 'discount' => 0, 'user_invoice_total' => 0, 'user_invoice_companyId' => '', 'user_invoice_product_id' => 0],
        ];

        $this->order_item = [
            ['product' => '', 'user_invoice_qty' => '', 'user_invoice_pieces' => '', 'user_invoice_price' => 0, 'user_invoice_order_item_id' => 0, 'user_invoice_total' => 0, 'user_invoice_companyId' => '', 'user_invoice_product_id' => 0],
        ];

        $this->new_order_items = [
            ['product' => '', 'qty' => 0, 'pieces' => '', 'price' => 0, 'stock' => '', 'discount' => 0, 'total' => 0, 'companyId' => '', 'product_id' => 0],
        ];

        $orderItems = [];

        foreach ($this->orderItems as $item) {
            $orderItems [] = [
                'product' => $item->product->name,
                'companyId' => $item->product->company_id,
                'product_id' => $item->product->id,
                'order_item_id' => $item->id,
                'qty' => $item->box_qty,
                'pieces' => $item->quantity,
                'price' => $item->product->unit_price,
                'stock' => $item->product->stock,
                'discount' => $item->discount,
                'total' => (double)$item->final_total
            ];
        }
        if (!empty($orderItems)) {
            $this->new_order_items = $orderItems;
        }

        $finalOrderAmt = OrderItem::where('invoice_id',$this->invoiceId)->sum('final_total');


        $newFinalOrderAmt = customRoundNumber($finalOrderAmt);

        $final_total = number_format($newFinalOrderAmt,2);


        $this->grandTotal = number_format($final_total,2);
        $this->paidAmount = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->paid_amount,2) : 0;
        $this->otherCharge = !empty($this->invoiceDetails) ?  number_format($this->invoiceDetails->other_charges,2) : 0;
        $this->dueAmount = number_format($newFinalOrderAmt,2);
        $this->totalMedicinePrice = number_format($newFinalOrderAmt,2);

    }

    public function rules()
    {
        return [
            'new_user_rows.*.user_invoice_price' => 'required|numeric|min:0',
            'new_user_rows.*.user_invoice_product_id' => 'required|integer|min:1',

            'order_item.*.user_invoice_product_id' => 'required|integer|min:1',
            'order_item.*.user_invoice_companyId' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'new_user_rows.*.user_invoice_price.required' => 'The price field is required.',
            'new_user_rows.*.user_invoice_price.numeric' => 'The price must be a number.',
            'new_user_rows.*.user_invoice_price.min' => 'The price must be at least 0.',

            'new_user_rows.*.user_invoice_product_id.required' => 'Please select a product.',
            'new_user_rows.*.user_invoice_product_id.integer' => 'Please select a product.',
            'new_user_rows.*.user_invoice_product_id.min' => 'Please select a product.',

        ];
    }

    public function addNewUserRow()
    {
        $this->new_user_rows[] = [
            'product' => '',
            'qty' => 0,
            'pieces' => '',
            'price' => 0,
            'stock' => '',
            'discount' => 0,
            'total' => 0
        ];
    }
    public function removeUserRow($index)
    {
        $currentRow = $this->new_user_rows[$index];

        if ($currentRow) {

            $currentRemovedTotal = $currentRow['total'];

            $this->grandTotal = number_format($this->grandTotal - $currentRemovedTotal, 2);
            $this->dueAmount = number_format($this->dueAmount - $currentRemovedTotal, 2);

            unset($this->new_user_rows[$index]);

            $this->new_user_rows = array_values($this->new_user_rows);
            $this->dispatch('update-totals');
        }
    }
    public function removeUserOrderItemRow($index,$index2)
    {
//        $currentRow = $this->new_order_items[$index];

//        if ($currentRow) {
//
//            $currentRemovedTotal = $currentRow['total'];
//
//            $this->grandTotal = number_format($this->grandTotal - $currentRemovedTotal, 2);
//            $this->dueAmount = number_format($this->dueAmount - $currentRemovedTotal, 2);
//
//            unset($this->new_order_items[$index]);
//
//            $this->new_user_rows = array_values($this->new_order_items);
//            $this->dispatch('update-totals');
//        }

        $this->dispatch('deleteInvoiceItemEvent',['id'=>$index,'position'=>$index2]);

    }

    public function deleteItem($id,$position)
    {

        $orderItem = OrderItem::find($id);

        if (empty($orderItem)){
            $this->dispatch('toast', type: 'error', message: 'Order item not found!');
            return;
        }

        $final_total = $orderItem->final_total;
        $invoice_id = $orderItem->invoice_id;

        $invoice =  Invoices::find($invoice_id);

        $invoice->update([
            'final_total' => ($invoice->final_total - $final_total)
        ]);

        $orderItem->delete();

        $this->invoiceDetails =  Invoices::find($invoice_id);

        unset($this->new_order_items[$position]);

        $final_total = $this->invoiceDetails->final_total ?? 0;

        $this->grandTotal = number_format($final_total,2);

        $allOrderItem =  OrderItem::where('invoice_id',$this->invoiceId)->get();


        if (count($allOrderItem) == 0){
            Invoices::find($this->invoiceId)->delete();
            $this->redirectRoute('user.pending-invoice-list');
        }

        $this->dispatch('toast', type: 'success', message: 'Invoice item deleted!');

    }


    public function updateField($index, $field)
    {
        if (!isset($this->new_user_rows[$index][$field])) {
            return;
        }
        if (in_array($field, ['user_invoice_qty', 'user_invoice_pieces'])) {
            $this->new_user_rows[$index][$field] = max(0, (int)$this->new_user_rows[$index][$field]);

            if (!empty($this->order_item[$index][$field]) && isset($this->order_item[$index][$field])){
                $this->order_item[$index][$field] = max(0, (int)$this->order_item[$index][$field]);
            }
        }

        $this->calculateTotal($index);
    }
    public function productSelected($data)
    {
//        $index = $data['index'];

        if (isset($this->new_user_rows[0])) {
            $this->new_user_rows[0]['user_invoice_product_id'] = $data['product_id'];
            $this->new_user_rows[0]['user_invoice_price'] = number_format($data['price'], 2);
            $this->new_user_rows[0]['user_invoice_stock'] = $data['stock'];
            $this->new_user_rows[0]['user_invoice_companyId'] = $data['company_id'];
            $this->new_user_rows[0]['user_invoice_price'] = $data['price'];
        }
    }
    public function calculateTotal($index): void
    {
        $row = $this->new_user_rows[$index];

        if (!empty($this->order_item[$index])){
            $row = $this->order_item[$index];
        }

        $qty = (float)($row['user_invoice_qty'] ?? 0);
        $pieces = (float)($row['user_invoice_pieces'] ?? 0);
        $price = (float)($row['user_invoice_price'] ?? 0);
        $discount = (float)($row['user_invoice_discount'] ?? 0);
        $productId = $row['user_invoice_product_id'] ?? 0;

        $getProductDetails = Product::find($productId);

        $box_per_pic = $getProductDetails->box_per_pic ?? 1;

        if (!empty($qty)) {
            $this->calculatedBoxQty = $qty * $box_per_pic;
        } else {
            $this->calculatedBoxQty = 0;
        }
        if (!empty($pieces)) {
            $this->calculatedQty = $pieces;
        } else {
            $this->calculatedQty = 0;
        }

        $total = ($this->calculatedBoxQty + $this->calculatedQty) * $price;

        if (!is_array($this->totalPriceWithoutDiscount)) {
            $this->totalPriceWithoutDiscount = []; // Initialize as an empty array
        }

        $this->totalPriceWithoutDiscount[$index] = $total;

        $total -= $total * ($discount / 100);

        $this->new_user_rows[$index]['user_invoice_total'] = $total;

        if (isset($this->order_item[$index]['user_invoice_total']) && !empty($this->order_item[$index]['user_invoice_total'])){
            $this->order_item[$index]['user_invoice_total'] = $total;
        }

        $this->totalPrice[$index] = $total;

        $this->user_invoice_totalMedicinePrice = number_format(array_sum($this->totalPrice),2);

        $this->updateGrandTotal();

        $this->totalDiscount[$index] = !empty($discount) ? $discount : 0;
    }
    public function updateDueAmount($data=null , $type = null)
    {
        if (empty($data)) {
            $finalAmt = (double)$this->grandTotal;
            if (!empty($this->paidAmount)) {
                $finalAmt = (double)$this->grandTotal - (double)$this->paidAmount;
            }
            $this->dueAmount = number_format((($finalAmt + (double)$this->otherCharge) - $this->paidAmount), 2);
        } elseif ($type == 'addOtherCharge') {
            $finalAmt = (double)$this->grandTotal;
            if (!empty($this->paidAmount)) {
                $finalAmt = (double)$this->grandTotal - (double)$this->paidAmount;
            }
            $this->dueAmount = number_format($finalAmt , 2);
        } else {
            $this->dueAmount = number_format((double)$this->grandTotal - (double)$this->paidAmount, 2);
        }
    }
    public function updateFinalGrandTotal($data)
    {
        $amt = (double)$data;
        $latestGrandTotal = $this->totalMedicinePrice + $amt;
        $this->grandTotal = $latestGrandTotal;
    }
    private function updateGrandTotal()
    {
        $totalPrice = [];
        foreach ($this->new_user_rows as $row){
            $totalPrice[]=$row['user_invoice_total'];
        }

        if (!empty($totalPrice)){
            $this->user_invoice_grandTotal = array_sum($totalPrice) + $this->otherCharge;
            $this->user_invoice_dueAmount = $this->user_invoice_grandTotal;
            $this->updateDueAmount();
        }
    }
    private function updateTotalDiscount()
    {
        $totalDiscountPrice = [];
        foreach ($this->new_user_rows as $row){
            $totalDiscountPrice[]=$row['discount'];
        }

        if (!empty($totalDiscountPrice)){
            $this->totalDiscount = array_sum($totalDiscountPrice);
        }

        return $this->totalDiscount;
    }
    private function totalProductPriceWithoutDiscount()
    {
        $totalDiscountPrice = [];

        foreach ($this->new_user_rows as $row){

            $qty = $row['qty'];
            $pieces = $row['pieces'];
            $price = $row['price'];

            $getProductDetails = Product::find($row['product_id']);

            $box_per_pic = $getProductDetails->box_per_pic ?? 1;

            if (!empty($qty)) {
                $this->calculatedBoxQty = $qty * $box_per_pic;
            } else {
                $this->calculatedBoxQty = 0;
            }
            if (!empty($pieces)) {
                $this->calculatedQty = $pieces;
            } else {
                $this->calculatedQty = 0;
            }

            $total = ($this->calculatedBoxQty + $this->calculatedQty) * $price;

            $totalDiscountPrice[] = $total;
        }

        if (!empty($totalDiscountPrice)){
            $this->totalPriceWithoutDiscount = array_sum($totalDiscountPrice);
        }

        return $this->totalPriceWithoutDiscount;
    }
    public function doubleSpacePressed()
    {
        $this->addRow();
    }

    public function createUserInvoiceItem()
    {

//        $this->validate();

        DB::beginTransaction();

        try {

            $invoice = Invoices::find($this->invoiceId);

            if (empty($invoice)){
                $this->dispatch('toast', type: 'error', message: 'Invoice not found!');
                return;
            }

            $this->invoiceDetails = $invoice;

            $orderItemArray = [];

            foreach ($this->new_user_rows as $row) {

                $getMedicine = Product::find($row['user_invoice_product_id']);
                $boxPerPic = $getMedicine->box_per_pic;
                $unitPrice = $getMedicine->unit_price;
                $calculatedBoxQty = 0;
                $calculatedQty = 0;

                if((int)$row['user_invoice_pieces'] > 0){
                    $calculatedQty =  (int)$row['user_invoice_pieces'];
                }
                if (!empty($row['user_invoice_qty']) && (int)$row['user_invoice_qty'] > 0){
                    $calculatedBoxQty = $row['user_invoice_qty'] * $boxPerPic;
                }

                $row['user_invoice_total'] = (($calculatedBoxQty + $calculatedQty) * $unitPrice);


                $orderItemArray[] = [
                    'user_id' => auth()->user()->id,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['user_invoice_companyId'] ?? null,
                    'product_id' => $row['user_invoice_product_id'] ?? null,
                    'box_qty' => !empty($row['user_invoice_qty']) ? $row['user_invoice_qty'] : 0,
                    'quantity' => !empty($row['user_invoice_pieces']) ? $row['user_invoice_pieces']: 0,
                    'price' => $row['user_invoice_price'],
                    'discount' => $row['discount'] ?? 0,
                    'final_total' => $row['user_invoice_total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

//            if ($this->dueAmount != 0) {
//                $temTransactionData = [
//                    'user_id' => auth()->user()->id ?? null,
//                    'amount' => $this->grandTotal,
//                    'type' => DEBIT,
//                    'note' => $this->note ?? '',
//                    'paid_date' => Carbon::now(),
//                ];
//
//                createTransaction($temTransactionData);
//            }


//            if (!empty($this->userId)){
//
//                $this->updateUserBalance();
//            }

            $this->orderItems = OrderItem::with(['product'])->where('invoice_id', $this->invoiceId)->get();
            $sumOfFinalAmt = OrderItem::where('invoice_id', $this->invoiceId)->sum('final_total');

            $orderItems = [];

            foreach ($this->orderItems as $item) {
                $orderItems [] = [
                    'product' => $item->product->name,
                    'companyId' => $item->product->company_id,
                    'product_id' => $item->product->id,
                    'order_item_id' => $item->id,
                    'qty' => $item->box_qty,
                    'pieces' => $item->quantity,
                    'price' => $item->product->unit_price,
                    'stock' => $item->product->stock,
                    'discount' => $item->discount,
                    'total' => (double)$item->final_total
                ];
            }
            if (!empty($orderItems)) {
                $this->new_order_items = $orderItems;
            }

            $this->new_user_rows = [
                ['product' => '', 'user_invoice_qty' => '', 'user_invoice_pieces' => '', 'user_invoice_price' => 0, 'stock' => '', 'discount' => 0, 'user_invoice_total' => 0, 'user_invoice_companyId' => '', 'user_invoice_product_id' => 0],
            ];

            $invoice->update([
                'final_total' => $sumOfFinalAmt
            ]);

            $this->grandTotal = number_format($sumOfFinalAmt,2);

            $this->resetValidation();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Item added successfully');
            $this->dispatch('reloadPage');
            $this->dispatch('closeModal', (object)[
                'modalId' => 'createModal'
            ]);
            $this->dispatch('pg:eventRefresh-invoices');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error
            $this->dispatch('toast', type: 'error', message: 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function updateInvoiceOrderItem()
    {

        $this->validate([
            'order_item.*.user_invoice_product_id.required' => 'Please select a product.',
            'order_item.*.user_invoice_product_id.integer' => 'Please select a product.',
            'order_item.*.user_invoice_product_id.min' => 'Please select a product.',
        ]);

        DB::beginTransaction();

        try {

            $orderItem = OrderItem::find($this->orderInvoiceItemId);

            if (empty($orderItem)){
                $this->dispatch('toast', type: 'error', message: 'Invoice item not found!');
                return;
            }

            $orderItemArray = [];

            foreach ($this->order_item as $row) {

                $orderItemArray[] = [
                    'final_total' => $row['user_invoice_total'],
                ];

                $orderItem->update([
                    'user_id' => auth()->user()->id,
                    'invoice_id' => $orderItem->invoice_id,
                    'company_id' => $row['user_invoice_companyId'] ?? null,
                    'product_id' => $row['user_invoice_product_id'] ?? null,
                    'box_qty' => !empty($row['user_invoice_qty']) ? $row['user_invoice_qty'] : 0,
                    'quantity' => !empty($row['user_invoice_pieces']) ? $row['user_invoice_pieces']: 0,
                    'price' => $row['user_invoice_price'],
                    'discount' => $row['discount'] ?? 0,
                    'final_total' => $row['user_invoice_total'],
                    'updated_at' => now(),
                ]);
            }

            $finalTotal = $orderItemArray[0]['final_total'] ?? 0;


//            if ($this->dueAmount != 0) {
//                $temTransactionData = [
//                    'user_id' => auth()->user()->id ?? null,
//                    'amount' => $finalTotal,
//                    'type' => DEBIT,
//                    'note' => $this->note ?? '',
//                    'paid_date' => Carbon::now(),
//                ];
//
//                createTransaction($temTransactionData);
//            }


//            if (!empty($this->userId)){
//                $this->updateUserBalance();
//            }

            $this->orderItems = OrderItem::with(['product'])->where('invoice_id', $this->invoiceId)->get();

            $sumOfFinalAmt = OrderItem::where('invoice_id', $this->invoiceId)->sum('final_total');

            $orderItems = [];

            foreach ($this->orderItems as $item) {
                $orderItems [] = [
                    'product' => $item->product->name,
                    'companyId' => $item->product->company_id,
                    'product_id' => $item->product->id,
                    'order_item_id' => $item->id,
                    'qty' => $item->box_qty,
                    'pieces' => $item->quantity,
                    'price' => $item->product->unit_price,
                    'stock' => $item->product->stock,
                    'discount' => $item->discount,
                    'total' => (double)$item->final_total
                ];
            }
            if (!empty($orderItems)) {
                $this->new_order_items = $orderItems;
            }

            Invoices::find($orderItem->invoice_id)->update([
                'final_total' => $sumOfFinalAmt
            ]);

            $this->grandTotal = number_format($sumOfFinalAmt,2);

            $this->resetValidation();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Invoice created successfully');

            $this->dispatch('closeModal', (object)[
                'modalId' => 'updateInvoiceOrderItem'
            ]);
            $this->dispatch('pg:eventRefresh-invoices');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error
            $this->dispatch('toast', type: 'error', message: 'Failed to create invoice: ' . $e->getMessage());
        }
    }

//    #[Validate(['rows.*.qty' => 'required|integer|min:0'])]
//    #[Validate(['rows.*.pieces' => 'required|integer|min:0'])]
//    #[Validate(['rows.*.price' => 'required|numeric|min:0'])]
//    #[Validate(['rows.*.discount' => 'nullable|numeric|min:0|max:100'])]
    public function updateInvoice()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // Step 1: Update the invoice
            $orderInvoice =  Invoices::find($this->invoiceDetails->id);

            $invoiceData = [
                'total_amount' => $this->totalProductPriceWithoutDiscount() ?? 0,
                'custom_discount' => $this->updateTotalDiscount() ?? 0,
                'other_charges' => $this->otherCharge,
                'final_total' => $this->grandTotal,
                'paid_amount' => $this->paidAmount,
                'due_amount' => $this->dueAmount,
                'note' => $this->note,
                'status' => PENDING_STATUS,
            ];

            if ($orderInvoice->status == DRAFT_STATUS || (\Auth::user()->role == STAFF_ROLE || \Auth::user()->role == USER_ROLE)) {
                $invoiceData['status'] = $orderInvoice->status;
            }

            $orderInvoice->update($invoiceData);

            // Step 2: Fetch existing items from DB
            $existingItems = OrderItem::where('invoice_id', $this->invoiceDetails->id)->get()->keyBy('product_id');

            // Step 3: Loop through updated rows and process each
            foreach ($this->new_user_rows as $row) {
                $productId = $row['product_id'];
                $product = Product::find($productId);
                $boxPerPiece = $product->box_per_pic ?? 0;

                $newBoxQty = !empty($row['qty']) ? (int)$row['qty'] : 0;
                $newPieces = !empty($row['pieces']) ? (int)$row['pieces']: 0;
                $newTotalQty = ($newBoxQty * $boxPerPiece) + $newPieces;

                $oldItem = $existingItems[$productId] ?? null;
                $oldBoxQty = (int)($oldItem->box_qty ?? 0);
                $oldPieces = (int)($oldItem->quantity ?? 0);
                $oldTotalQty = ($oldBoxQty * $boxPerPiece) + $oldPieces;

                $diffQty = $newTotalQty - $oldTotalQty;

                // Adjust stock based on quantity difference
                if ($diffQty > 0) {
                    $product->decrement('stock', $diffQty);
                } elseif ($diffQty < 0) {
                    $product->increment('stock', abs($diffQty));
                }

                // Update or create the OrderItem
                OrderItem::updateOrCreate(
                    ['invoice_id' => $this->invoiceDetails->id, 'product_id' => $productId],
                    [
                        'user_id' => $this->invoiceDetails->user_id ?? null,
                        'company_id' => $row['companyId'] ?? null,
                        'box_qty' => $newBoxQty,
                        'quantity' => $newPieces,
                        'price' => $row['price'],
                        'discount' => $row['discount'] ?? 0,
                        'final_total' => $row['total'],
                        'updated_at' => now(),
                    ]
                );
            }

            // Optional: remove any deleted items that were removed from the updated invoice
            $updatedProductIds = collect($this->rows)->pluck('product_id')->toArray();
            $deletedItems = $existingItems->whereNotIn('product_id', $updatedProductIds);

            foreach ($deletedItems as $deletedItem) {
                $product = Product::find($deletedItem->product_id);
                $boxPerPiece = $product->box_per_pic ?? 0;
                $totalQty = ((int)$deletedItem->box_qty * $boxPerPiece) + (int)$deletedItem->quantity;
                $product->increment('stock', $totalQty); // restore stock
                $deletedItem->delete(); // remove the item
            }

            // Step 4: If fully paid, update status
            if ((double)$this->grandTotal == (double)$this->paidAmount) {
                Invoices::find($this->invoiceDetails->id)->update([
                    'status' => ACTIVE_STATUS
                ]);
            }

            // Step 5: Log transaction
            if ($this->dueAmount != 0) {
                $transactionData = [
                    'user_id' => $this->invoiceDetails->user_id ?? null,
                    'amount' => $this->paidAmount,
                    'type' => DEBIT,
                    'note' => $this->note,
                    'paid_date' => Carbon::now(),
                ];

                \App\Models\Transaction::create($transactionData);
            }

            if (!empty($this->invoiceDetails->user_id)){
                $this->updateUserBalance();
            }

            $this->new_user_rows = OrderItem::where('invoice_id', $this->invoiceDetails->id)->get()
                ->map(function ($item) {
                    $product = Product::find($item->product_id);
                    return [
                        'companyId'     => $item->company_id,
                        'product'       => $product,
                        'product_id'    => $item->product_id,
                        'order_item_id' => $item->id,
                        'qty'           => $item->box_qty,
                        'pieces'        => $item->quantity,
                        'price'         => $item->price,
                        'discount'      => $item->discount,
                        'total'         => $item->final_total,
                        'stock'         => $product->stock ?? 0,
                    ];
                })->toArray();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Invoice updated successfully');

        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function updateMyInvoice()
    {
        $orderInvoice =  Invoices::find($this->invoiceDetails->id);

        if (empty($orderInvoice)){
            $this->dispatch('toast', type: 'error', message: 'Invoice not found!');
            return;
        }

        $data=[
            'status'=>PENDING_STATUS,
            'total_amount'=>$this->totalMedicinePrice,
            'final_total'=>$this->totalMedicinePrice,
            'due_amount'=>$this->totalMedicinePrice,
        ];

        $orderInvoice->update($data);

        $this->dispatch('toast', type: 'success', message: 'Invoice status updated successfully!');
    }
    private function updateUserBalance()
    {
        $invoiceDueAmount = Invoices::where('user_id',$this->invoiceDetails->user_id)->sum('due_amount');
        \App\Models\User::find($this->invoiceDetails->user_id)->update([
            'balance'=>$invoiceDueAmount
        ]);
    }
    public function addProduct()
    {
        $this->showEditModal = false;
        $this->dispatch('openModal', (object)[
            'modalId' => 'createModal',
        ]);
    }
    public function updateInvoiceItem($orderItemId)
    {

        $this->showEditModal = false;

        $orderItem = OrderItem::with('product')->find($orderItemId);

        $this->order_item[0]['user_invoice_product_id'] = $orderItem->product_id;
        $this->order_item[0]['user_invoice_companyId'] = $orderItem->company_id;
        $this->order_item[0]['user_invoice_order_item_id'] = $orderItemId;

        $this->order_item[0]['user_invoice_qty'] = $orderItem->box_qty;
        $this->order_item[0]['user_invoice_pieces'] = $orderItem->quantity == 0 || $orderItem->quantity == '' ? '' : $orderItem->quantity;
        $this->order_item[0]['user_invoice_price'] = $orderItem->price;
        $this->order_item[0]['product'] = $orderItem->product->name;
        $this->order_item[0]['user_invoice_total'] = $orderItem->final_total;

        $this->orderInvoiceItemId = $orderItemId;

//        dd($this->order_item,$orderItem->price);

        $this->dispatch('openModal', (object)[
            'modalId' => 'updateInvoiceOrderItem',
        ]);
    }

    public function closeModal()
    {
//        $this->reset();
//        $this->resetValidation();
//        $this->rows = [
//            ['product' => '', 'qty' => 0, 'pieces' => '', 'price' => 0,'total' => 0, 'product_id'=>0,'companyId'=>0]
//        ];
    }

    public function getInvoiceDetails($id)
    {
        $this->invoiceId = $id;
        $this->invoiceDetails = Invoices::with(['user'])->find($this->invoiceId);
        $this->orderItems = OrderItem::with(['product.company'])->where('invoice_id', $this->invoiceId)->get();
        $this->totalMedicinePrice = OrderItem::where('invoice_id', $this->invoiceId)->sum('final_total');

        $data['userName'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->name : 'Unknown';
        $data['userId'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->id : null;
        $data['phone'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->phone : 'Unknown';
        $data['address'] = !empty($this->invoiceDetails->user) ? $this->invoiceDetails->user->address : 'Unknown';
        $data['invoiceStatus'] = $this->invoiceDetails->status == ACTIVE_STATUS ? ACTIVE_STATUS : ($this->invoiceDetails->status == PENDING_STATUS ? PENDING_STATUS : DRAFT_STATUS);

        $data['grandTotal'] = $this->invoiceDetails->final_total ?? 0;
        $data['paidAmount'] = !empty($this->invoiceDetails) ? $this->invoiceDetails->paid_amount : 0;
        $data['otherCharge'] = !empty($this->invoiceDetails) ? $this->invoiceDetails->other_charges : 0;
        $data['dueAmount'] = $this->invoiceDetails->due_amount;
        $data['totalMedicinePrice'] = $this->totalMedicinePrice;
        $data['note'] = $this->invoiceDetails->note ?? '';
        $data['total_amount_without_discount'] = $this->invoiceDetails->total_amount;
        $data['custom_total_discount'] = $this->invoiceDetails->custom_total_discount;
        $data['orderItems'] = $this->orderItems;
        $data['invoiceId'] = $this->invoiceId;
        $data['invoiceDetailsId'] = $this->invoiceDetails->invoice_id;
        $data['invoiceDetails'] = $this->invoiceDetails;
        $data['page'] = 'Invoice Details';
        $data['page1'] = 'Customer Details';
        $data['title'] = 'Invoice Details';


        return view('livewire.user-invoice-details', $data);
    }


    #[Layout('layout.app')]
    #[Title('Invoice Details')]
    public function render()
    {
        return view('livewire.user-invoice-details', [
            'page' => 'Invoice Details',
            'page1' => 'Customer Details',
            'index' => 0,
            'invoiceDetailsId' => !empty($this->invoiceDetails) ? $this->invoiceDetails->invoice_id : ''
        ]);
    }

}
