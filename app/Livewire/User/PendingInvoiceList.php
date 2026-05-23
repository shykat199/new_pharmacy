<?php

namespace App\Livewire\User;

use App\Models\Invoices;
use App\Models\OrderItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PendingInvoiceList extends Component
{

    public $showEditModal = false;

    public $rows = [];
    public $orderItems ;
    public $userId ;
    public $invoiceId ;
    public $calculatedBoxQty = 0;
    public $calculatedQty = 0;

    public $grandTotal = 0;
    public $paidAmount = 0;
    public $otherCharge = 0;
    public $dueAmount = 0;
    public $totalPrice = [];
    public $totalPriceWithoutDiscount = [];
    public $totalDiscount = [];
    public $invoiceDetails = null;
    public $totalMedicinePrice = 0;
    public $note = '';
    public bool $hasPendingChanges = false;

    protected $listeners = ['productSelected','updateDueAmount','delete','updateGrandTotal','editProduct'];

    public function mount(): void
    {
        $this->rows = [
            ['product' => '', 'qty' => 0, 'pieces' => '', 'price' => 0,'total' => 0, 'product_id'=>0,'companyId'=>0]
        ];
        $this->userId = \Auth::user()->id;
    }

    public function updated()
    {

        if (!$this->hasPendingChanges) {
            $this->hasPendingChanges = true;

//            $this->dispatch('triggerAutoSave');
        }
    }

    #[\Livewire\Attributes\On('triggerAutoSave')]
    public function autoSave()
    {
        sleep(5);

        $this->dispatch('pg:eventRefresh-invoices');

        if ($this->hasPendingChanges) {
            $this->saveInvoiceSilently();
            $this->hasPendingChanges = false;
            $this->dispatch('pg:eventRefresh-invoices');
        }
    }

    public function saveInvoiceSilently()
    {
        try {
            DB::beginTransaction();

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => \Auth::user()->id,
                'total_amount' => customRoundNumber($this->totalMedicinePrice),
                'final_total' =>customRoundNumber( $this->grandTotal),
                'due_amount' => customRoundNumber($this->dueAmount),
                'note' => $this->note ?? '',
                'status' => (\Auth::user()->role == STAFF_ROLE || \Auth::user()->role == USER_ROLE) ? DRAFT_STATUS : PENDING_STATUS,
            ];

            $invoice = Invoices::updateOrCreate(
                ['id' => $this->invoiceDetails->id ?? null],
                $invoiceData
            );

            $this->invoiceDetails = $invoice;

            OrderItem::where('invoice_id', $invoice->id)->delete();

            $orderItemArray = array_map(function ($row) use ($invoice) {
                return [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces']: 0,
                    'price' => $row['price'],
                    'discount' => $row['discount'] ?? 0,
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $this->rows);

            OrderItem::insert($orderItemArray);
//            $this->dispatch('toast', type: 'success', message: 'Invoice auto save successfully');

            DB::commit();

            $this->dispatch('pg:eventRefresh-invoices');
            $this->dispatch('invoiceSaved');

        } catch (\Exception $e) {
            DB::rollBack();
//            $this->dispatch('toast', type: 'error', message: 'Auto-save failed');
            logger()->error('Auto-save failed: ' . $e->getMessage());
        }
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
        $this->rows = [
            ['product' => '', 'qty' => 0, 'pieces' => '', 'price' => 0,'total' => 0, 'product_id'=>0,'companyId'=>0]
        ];
    }

    #[Validate(['rows.*.price' => 'required|numeric|min:0'])]
    public function createInvoice()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => \Auth::user()->id,
                'total_amount' => $this->totalMedicinePrice,
                'final_total' => $this->grandTotal,
                'due_amount' => $this->dueAmount,
                'note' => $this->note,
                'status' => PENDING_STATUS,
            ];

            $invoice = Invoices::updateOrCreate(
                ['id' => $this->invoiceDetails->id ?? null],
                $invoiceData
            );

//            $invoice = Invoices::create($invoiceData);

            $this->invoiceDetails = $invoice;

            OrderItem::where('invoice_id', $invoice->id)->delete();

            $orderItemArray = [];

            foreach ($this->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces']: 0,
                    'price' => $row['price'],
                    'discount' => $row['discount'] ?? 0,
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

//            if ($this->dueAmount != 0) {
//                $temTransactionData = [
//                    'user_id' => !empty($this->userId) ? $this->userId : null,
//                    'amount' => $this->paidAmount,
//                    'type' => DEBIT,
//                    'note' => $this->note,
//                    'paid_date' => Carbon::now(),
//                ];
//
//                createTransaction($temTransactionData);
//            }

//            $this->generateInvoicePdf();

//            if (!empty($this->userId)){
//
//                $this->updateUserBalance();
//            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Invoice created successfully');
            $this->dispatch('closeModal', (object)[
                'modalId' => 'createModal'
            ]);
            $this->dispatch('pg:eventRefresh-invoices');
            $this->dispatch('refresh-browser');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error
            $this->dispatch('toast', type: 'error', message: 'Failed to create invoice: ' . $e->getMessage());
        }
    }

//    #[Validate(['rows.*.price' => 'required|numeric|min:0'])]
    public function updateInvoice()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // Step 1: Update the invoice
            $invoiceData = [
                'total_amount' => $this->totalProductPriceWithoutDiscount() ?? 0,
                'final_total' => $this->grandTotal,
                'due_amount' => $this->dueAmount,
                'note' => $this->note,
            ];

            Invoices::find($this->invoiceDetails->id)->update($invoiceData);

            // Step 2: Fetch existing items from DB
            $existingItems = OrderItem::where('invoice_id', $this->invoiceDetails->id)->get()->keyBy('product_id');

            // Step 3: Loop through updated rows and process each
            foreach ($this->rows as $row) {
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
                        'final_total' => $row['total'],
                        'updated_at' => now(),
                    ]
                );
            }

            // Optional: remove any deleted items that were removed from the updated invoice
            $updatedProductIds = collect($this->rows)->pluck('product_id')->toArray();
            $deletedItems = $existingItems->whereNotIn('product_id', $updatedProductIds);

            foreach ($deletedItems as $deletedItem) {
                $deletedItem->delete();
            }

            if (!empty($this->invoiceDetails->user_id)){
                $this->updateUserBalance();
            }

            $this->rows = OrderItem::where('invoice_id', $this->invoiceDetails->id)->get()
                ->map(function ($item) {
                    $product = Product::find($item->product_id);
                    return [
                        'companyId'     => $item->company_id,
                        'product'       => $product->name,
                        'product_id'    => $item->product_id,
                        'order_item_id' => $item->id,
                        'qty'           => $item->box_qty,
                        'pieces'        => $item->quantity,
                        'price'         => $item->price,
                        'total'         => $item->final_total,
                    ];
                })->toArray();

            $this->generateInvoicePdf();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Invoice updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    private function totalProductPriceWithoutDiscount()
    {
        $totalDiscountPrice = [];

        foreach ($this->rows as $row){

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

    private function updateUserBalance()
    {
        $invoiceDueAmount = Invoices::where('user_id',\Auth::user()->id)->sum('due_amount');
        \App\Models\User::find(\Auth::user()->id)->update([
            'balance'=>$invoiceDueAmount
        ]);
    }

    private function generateInvoicePdf()
    {
        try {

            $shopName = getSettingsData('shopName');

            $data = [
                'title' => mb_convert_encoding('Welcome to '.$shopName, 'UTF-8', 'auto'),
                'date' => date('d-M-Y'),
                'orderItems' => $this->rows,
                'invoiceDetails'=>$this->invoiceDetails,
                'total' => $this->totalMedicinePrice,
                'grandTotal' => $this->grandTotal,
                'paidAmt' => $this->paidAmount,
                'otherCharge' => $this->otherCharge,
                'dueAmt' => $this->dueAmount,
            ];

            $pdf = PDF::loadView('pdf.invoice', $data)->setOptions([
                'defaultFont' => 'Arial'
            ])->setPaper('a4', 'landscape');

            $fileName = 'invoice-'.str_replace('#','',$this->invoiceDetails->invoice_id).'_' . time() . '.pdf';
            $filePath = 'invoice/' . $fileName;

            // Store PDF in local disk
            if (Storage::disk('public')->put($filePath, $pdf->output())) {
                $fileUrl = Storage::url($filePath);
                $this->dispatch('downloadPdf', [asset($fileUrl),$fileName]);
                Log::info("PDF saved successfully to: " . $filePath);
            } else {
                Log::error("Failed to save PDF.");
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function updateField($index, $field, $type='')
    {

        if ($type == 'update'){

            if (!isset($this->rows[$index][$field])) {
                return;
            }

            if (in_array($field, ['qty', 'pieces'])) {
                $this->rows[$index][$field] = max(0, (int)$this->rows[$index][$field]);
            }

            $this->calculateTotal($index,$type);

        }else{
            if (!isset($this->rows[$index][$field])) {
                return;
            }

            if (in_array($field, ['qty', 'pieces'])) {
                $this->rows[$index][$field] = max(0, (int)$this->rows[$index][$field]);
            }

            $this->calculateTotal($index);
        }
    }

    public function calculateTotal($index , $type = ''): void
    {

        if ($type == 'update'){

            $row = $this->rows[$index];

            $qty = (float)($row['qty'] ?? 0);
            $pieces = (float)($row['pieces'] ?? 0);
            $price = (float)($row['price'] ?? 0);
            $productId = $row['product_id'] ?? 0;
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

            $this->rows[$index]['total'] = $total;

            $this->totalPrice[$index] = $total;

            $this->updateNewGrandTotal();

            $this->totalDiscount[$index] = !empty($discount) ? $discount : 0;

        } else{
            $row = $this->rows[$index];

            $qty = (float)($row['qty'] ?? 0);
            $pieces = (float)($row['pieces'] ?? 0);
            $price = (float)($row['price'] ?? 0);
            $productId = $row['product_id'] ?? 0;
            $getProductDetails = Product::find($productId);
            $box_per_pic = $getProductDetails->box_per_pic ?? 1;

            if (!empty($qty)) {
                $this->calculatedBoxQty = $qty * $box_per_pic;
            }else{
                $this->calculatedBoxQty = 0;
            }
            if (!empty($pieces)) {
                $this->calculatedQty = $pieces;
            }else{
                $this->calculatedQty = 0;
            }

            $total = ($this->calculatedBoxQty + $this->calculatedQty) * $price;

            $this->rows[$index]['total'] = number_format($total, 2);

            $this->totalPrice[$index] = $total;

            $this->totalMedicinePrice = number_format(array_sum($this->totalPrice),2);

            $this->grandTotal = !empty($this->totalPrice) ? number_format(array_sum($this->totalPrice) + $this->otherCharge,2) : 0;

            $finalAmt = (double)$this->grandTotal;

            $this->dueAmount = number_format(($finalAmt + $this->otherCharge),2) ?? 0 ;
        }

    }

    private function updateNewGrandTotal()
    {
        $totalPrice = [];
        foreach ($this->rows as $row){
            $totalPrice[]=$row['total'];
        }

        if (!empty($totalPrice)){
            $this->grandTotal = number_format(array_sum($totalPrice),2);
            $this->totalMedicinePrice = number_format($this->grandTotal,2);
            $this->updateNewDueAmount();
        }
    }

    public function updateNewDueAmount($data=null)
    {
        if (empty($data)) {
            $finalAmt = (double)$this->grandTotal;
            $this->dueAmount = number_format($finalAmt, 2);
        }else {
            $this->dueAmount = number_format($this->grandTotal, 2);
        }
    }

    public function addRow()
    {
        $this->rows[] = [
            'product' => '',
            'qty' => 0,
            'pieces' => '',
            'price' => 0,
            'total' => 0,
        ];
    }

    public function removeRow($index)
    {
        $currentRow = $this->rows[$index];
        $currentRemovedTotal = $currentRow['total'];

        $this->grandTotal = number_format($this->grandTotal - $currentRemovedTotal, 2);
        $this->dueAmount = number_format($this->dueAmount - $currentRemovedTotal, 2);

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->dispatch('update-totals');
    }
    public function productSelected($data)
    {
        $index = $data['index'];
        if (isset($this->rows[$index])) {
            $this->rows[$index]['product_id'] = $data['product_id'];
            $this->rows[$index]['price'] = number_format($data['price'], 2);
            $this->rows[$index]['stock'] = $data['stock'];
            $this->rows[$index]['companyId'] = $data['company_id'];
        }
    }

    public function updateDueAmount($data, $type = null)
    {
        if (empty($data)) {
            $finalAmt = (double)$this->grandTotal;
            if (!empty($this->paidAmount)) {
                $finalAmt = (double)$this->grandTotal - (double)$this->paidAmount;
            }
            $this->dueAmount = number_format($finalAmt + (double)$this->otherCharge, 2);
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

    public function updateGrandTotal($data)
    {
        $amt = (double)$data;
        $latestGrandTotal = $this->totalMedicinePrice + $amt;
        $this->grandTotal = $latestGrandTotal;
    }

    public function editProduct($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        $this->showEditModal = true;

        $this->invoiceDetails = Invoices::with(['user'])->find($this->invoiceId);
        $this->orderItems = OrderItem::with(['product'])->where('invoice_id', $this->invoiceId)->get();
        $this->rows = [
            ['product' => '', 'qty' => 0, 'pieces' => '', 'price' => 0, 'total' => 0, 'companyId' => '', 'product_id' => 0],
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
                'total' => (double)$item->final_total
            ];
        }

        if (!empty($orderItems)) {
            $this->rows = $orderItems;
        }


        $this->grandTotal = $this->invoiceDetails->final_total ?? 0;
        $this->paidAmount = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->paid_amount,2) : 0;
        $this->dueAmount = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->due_amount,2) : 0;
        $this->totalMedicinePrice = number_format(array_sum(array_column($orderItems, 'total')),2);
        $this->note = $this->invoiceDetails->note;

    }

//    public function delete(\App\Models\Invoices $invoice): void
//    {
//        $dltInvoice= $invoice->update([
//            'status'=>DELETE_STATUS
//        ]);
//        if ($dltInvoice){
//            $this->dispatch('pg:eventRefresh-companies');
//            $this->dispatch('toast',type:'success',message:'Company deleted successfully');
//        }else{
//            $this->dispatch('toast',type:'error',message:'Company not deleted');
//        }
//    }


    #[Layout('layout.app')]
    #[Title('Pending Invoice List')]
    public function render()
    {
        return view('livewire.user.pending-invoice-list',[
            'page' => 'Pending Invoice List',
        ]);
    }

}
