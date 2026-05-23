<?php

namespace App\Livewire\User;

use App\Models\Invoices;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class CreateInvoice extends Component
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
    public bool $hasPendingChanges = false;

    public function mount(): void
    {

        $this->rows = [
            ['product' => '', 'qty' => '', 'pieces' => '', 'price' => 0,'total' => 0, 'product_id'=>0,'companyId'=>0]
        ];


        $this->new_order_items = [
            ['product' => '', 'qty' => '', 'pieces' => '', 'price' => 0, 'stock' => '', 'discount' => 0, 'total' => 0, 'companyId' => '', 'product_id' => 0],
        ];

    }

    public function rules()
    {
        return [
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
        ];
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
            dd($e->getMessage());
            DB::rollBack();
//            $this->dispatch('toast', type: 'error', message: 'Auto-save failed');
            logger()->error('Auto-save failed: ' . $e->getMessage());
        }
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


    public function createInvoice()
    {

        $this->validate();

        DB::beginTransaction();

        try {
            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => \Auth::user()->id,
                'total_amount' => customRoundNumber($this->totalMedicinePrice),
                'final_total' => customRoundNumber($this->grandTotal),
                'due_amount' => customRoundNumber($this->dueAmount),
                'note' => $this->note ?? '',
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

            return $this->redirectRoute('user.invoice-details',$invoice->id);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error
            $this->dispatch('toast', type: 'error', message: 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function messages()
    {
        return [
            'rows.*.price.required' => 'The price field is required.',
            'rows.*.price.numeric' => 'The price must be a number.',
            'rows.*.price.min' => 'The price must be at least 0.',

            'rows.*.product_id.required' => 'Please select a product.',
            'rows.*.product_id.integer' => 'Please select a product.',
            'rows.*.product_id.min' => 'Please select a product.',

        ];
    }

    public function addProduct()
    {
        $this->showEditModal = false;
        $this->dispatch('openModal', (object)[
            'modalId' => 'createModal',
        ]);
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


    public function closeModal()
    {
//        $this->reset();
//        $this->resetValidation();
//        $this->rows = [
//            ['product' => '', 'qty' => 0, 'pieces' => '', 'price' => 0,'total' => 0, 'product_id'=>0,'companyId'=>0]
//        ];
    }


    #[Layout('layout.app')]
    #[Title('Create New Invoice')]
    public function render()
    {
        return view('livewire.user.create-invoice',[
            'page' => 'Create Invoice',
            'page1' => 'Customer Details',
            'index' => 0,
        ]);
    }
}
