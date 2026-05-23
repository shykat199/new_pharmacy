<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InvoiceDetails extends Component
{
    public $invoiceId = 0;
    public $invoiceDetails = null;
    public $orderItems = null;

    public $rows = [];
    public $searchedUsers = [];
    public $selectedUsers = null;

    public $userName = '';
    public $address = '';
    public $phone = '';
    public $name = '';
    public $userId = '';

    protected $listeners = ['productSelected', 'saveCustomer', 'doubleSpacePressed', 'addNewRow', 'updateDueAmount', 'addOtherChargeAmount', 'fileDownloaded', 'updateGrandTotal', 'updateFinalGrandTotal', 'approveInvoice', 'sendAdminApproval'];
    public $calculatedBoxQty = 0;
    public $calculatedQty = 0;

    public $grandTotal = 0;
    public $paidAmount = '';
    public $otherCharge = '';
    public $dueAmount = 0;
    public $totalProductQty = 0;
    public $totalPrice = [];
    public $totalPriceWithoutDiscount = [];
    public $totalDiscount = [];
    public $removedOrderItemIds = [];
    public $totalMedicinePrice = 0;
    public $note = '';
    public $total_amount_without_discount = 0;
    public $custom_total_discount = 0;

    public function mount($id): void
    {
        $this->invoiceId = $id;
        $this->invoiceDetails = Invoices::with(['user'])->find($this->invoiceId);
        $this->total_amount_without_discount = $this->invoiceDetails->total_amount;
        $this->custom_total_discount = $this->invoiceDetails->custom_discount;
        $this->orderItems = OrderItem::with(['product'])->where('invoice_id', $this->invoiceId)->get();
        $this->rows = [
            ['product' => '', 'qty' => '', 'pieces' => '', 'price' => '', 'stock' => '', 'discount' => '', 'total' => '',
                'product_id' => 0, 'companyId' => 0, 'unit_price' => '', 'box_per_pic' => '']
        ];
        $orderItems = [];

        foreach ($this->orderItems as $item) {
            $orderItems [] = [
                'product' => $item->product->name,
                'companyId' => $item->product->company_id,
                'product_id' => $item->product->id,
                'order_item_id' => $item->id,
                'qty' => $item->box_qty == 0 ? '' : $item->box_qty,
                'pieces' => $item->quantity == 0 ? '' : $item->quantity,
                'price' => $item->product->unit_price,
                'unit_price' => $item->product->unit_price,
                'box_per_pic' => $item->product->box_per_pic,
                'stock' => $item->product->stock,
                'discount' => $item->discount == 0 || $item->discount == '' ? '' : $item->discount,
                'total' => (double)$item->final_total
            ];
        }

        if (!empty($orderItems)) {
            $this->rows = $orderItems;
        }

        $this->userName = !empty($this->invoiceDetails->user->name) ? $this->invoiceDetails->user->name : 'Unknown';
        $this->phone = !empty($this->invoiceDetails->user->phone) ? $this->invoiceDetails->user->phone : 'Unknown';
        $this->address = !empty($this->invoiceDetails->user->address) ? $this->invoiceDetails->user->address : 'Unknown';

        $this->grandTotal = $this->invoiceDetails->final_total ?? 0;
        $this->paidAmount = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->paid_amount, 2) : 0;
        $this->otherCharge = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->other_charges, 2) : 0;
        $this->dueAmount = !empty($this->invoiceDetails) ? number_format($this->invoiceDetails->due_amount, 2) : 0;
        $this->totalMedicinePrice = number_format(array_sum(array_column($orderItems, 'total')), 2);
        $this->note = $this->invoiceDetails->note ?? '';
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


        return view('livewire.admin.invoice-details', $data);
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'rows.')) {
            $this->recalculateRowTotal($propertyName);
        }
    }

    public function recalculateRowTotal($propertyName)
    {
        preg_match('/rows\.(\d+)\./', $propertyName, $matches);
        if (!isset($matches[1])) return;

        $index = $matches[1];

        $row = $this->rows[$index];

        $qty = floatval($row['qty'] ?? 0);
        $pieces = floatval($row['pieces'] ?? 0);
        $boxPerPic = floatval($row['box_per_pic'] ?? 0);
        $unitPrice = floatval($row['price'] ?? 0);
        $discount = floatval($row['discount'] ?? 0);

        $calculatedBoxQty = $qty * $boxPerPic;
        $calculatedQty = $pieces;
        $total = (($calculatedBoxQty + $calculatedQty) * $unitPrice) - $discount;

        $this->rows[$index]['total'] = number_format($total, 2);
    }


    public function updatedQuery()
    {
        $this->searchedUsers = \App\Models\User::where(function ($q) {
            $q->where('name', 'like', '%' . $this->query . '%');
            $q->orWhere('phone', 'like', '%' . $this->query . '%');
        })->where('role', USER_ROLE)->get()->toArray();
    }

    public function addRow()
    {
//        $this->rows[] = [
//            'product' => '',
//            'qty' => '',
//            'pieces' => '',
//            'price' => '',
//            'stock' => '',
//            'discount' => '',
//            'total' => ''
//        ];

        $this->rows[] = [
            'product' => '', 'qty' => '', 'pieces' => '', 'price' => '',
            'stock' => '', 'discount' => '', 'total' => '', 'product_id' => 0,
            'companyId' => 0, 'unit_price' => '', 'box_per_pic' => ''
        ];
    }

    public function removeRow($index)
    {
        $currentRow = $this->rows[$index];

        if ($currentRow) {

            $removedTotal = $currentRow['total'];
            $removedDiscount = $currentRow['discount'];


            unset($this->rows[$index]);

            $this->rows = array_values($this->rows);

            $this->dispatch('update-invoice', [$removedTotal, $removedDiscount]);
        }
    }

    public function updateField($index, $field)
    {
        if (!isset($this->rows[$index][$field])) {
            return;
        }

        if (in_array($field, ['qty', 'pieces'])) {
            $this->rows[$index][$field] = max(0, (int)$this->rows[$index][$field]);
        }
        if ($field === 'discount') {

            $this->rows[$index][$field] = $this->rows[$index][$field];
        }

        $this->calculateTotal($index);
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

    public function calculateTotal($index): void
    {
        $row = $this->rows[$index];

        $qty = (float)($row['qty'] ?? 0);
        $pieces = (float)($row['pieces'] ?? 0);
        $price = (float)($row['price'] ?? 0);
        $discount = $row['discount'] == 0 || $row['discount'] == '' ? '' : (float)($row['discount']);
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

//        $this->rows[$index]['total'] = number_format($total, 2);

        if (!is_array($this->totalPriceWithoutDiscount)) {
            $this->totalPriceWithoutDiscount = []; // Initialize as an empty array
        }

        $this->totalPriceWithoutDiscount[$index] = $total;

        $total -= $total * ((float)$discount / 100);

        $this->rows[$index]['total'] = customRoundNumber($total);

        $this->totalPrice[$index] = customRoundNumber($total);

//        $this->grandTotal = !empty($this->totalPrice) ? number_format(array_sum($this->totalPrice), 2) : 0;

        $totalPrice = [];
        foreach ($this->rows as $row) {
            $totalPrice[] = $row['total'];
        }

        $this->totalMedicinePrice = customRoundNumber(array_sum($totalPrice));

        $finalAmt = (float)$this->grandTotal;
        if (!empty($this->paidAmount)) {
            $finalAmt = (float)$this->grandTotal - (float)$this->paidAmount;
        }

//        $this->dueAmount = ($finalAmt + $this->otherCharge) ?? 0 ;
        $this->dueAmount = !(float)$this->otherCharge ? number_format($finalAmt + (float)$this->otherCharge, 2) : 0;

        $this->updateGrandTotal();

        if (!is_array($this->totalDiscount)) {
            $this->totalDiscount = [];
        }

        $this->totalDiscount[$index] = !empty($discount) ? (float)$discount : 0;
    }

    public function updateDueAmount($data, $type = null)
    {
        if (empty($data)) {
            $finalAmt = (float)$this->grandTotal;
            if (!empty($this->paidAmount)) {
                $finalAmt = customRoundNumber((float)$this->grandTotal - (float)$this->paidAmount);
            }
//            $this->dueAmount = number_format($finalAmt + (float)$this->otherCharge, 2);
            $this->dueAmount = customRoundNumber($finalAmt);
        } elseif ($type == 'addOtherCharge') {

            $finalAmt = (float)$this->grandTotal;
            if (!empty($this->paidAmount)) {
                $finalAmt = (float)$this->grandTotal - (float)$this->paidAmount;
            }
            $this->dueAmount = customRoundNumber($finalAmt);
        } else {
            if ($data == 0 || $data == '') {
                $this->dueAmount = $this->grandTotal;
            } else {
                $this->dueAmount = customRoundNumber(((float)$this->grandTotal - (float)$this->paidAmount));
            }
        }
    }

    public function updateFinalGrandTotal($data)
    {
        $amt = (float)$data;
        $latestGrandTotal = $this->totalMedicinePrice + $amt;
        $this->grandTotal = customRoundNumber($latestGrandTotal);
    }

    public function updateGrandTotal()
    {
        $totalPrice = [];
        foreach ($this->rows as $row) {
            $totalPrice[] = $row['total'];
        }

        if (!empty($totalPrice)) {
            $this->grandTotal = customRoundNumber(array_sum($totalPrice) + (float)$this->otherCharge);
//            $this->updateDueAmount();
        }

        if (!empty($this->grandTotal && !empty($this->paidAmount))) {
            $this->dueAmount = (float)$this->grandTotal - (float)$this->paidAmount;
        }
    }

    public function addNewRow()
    {
        $this->addRow();
    }

    private function updateTotalDiscount()
    {
        $totalDiscountPrice = [];
        foreach ($this->rows as $row) {
            $totalDiscountPrice[] = $row['discount'];
        }

        if (!empty($totalDiscountPrice)) {
            $this->totalDiscount = customRoundNumber(array_sum($totalDiscountPrice));
        }

        return customRoundNumber($this->totalDiscount);
    }

    private function totalProductPriceWithoutDiscount()
    {
        $totalDiscountPrice = [];

        foreach ($this->rows as $row) {

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

        if (!empty($totalDiscountPrice)) {
            $this->totalPriceWithoutDiscount = array_sum($totalDiscountPrice);
        }

        return customRoundNumber($this->totalPriceWithoutDiscount);
    }

    public function doubleSpacePressed()
    {
        $this->addRow();
    }

    public function rules()
    {
        return [
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'rows.*.price.required' => 'The price field is required.',
            'rows.*.price.numeric' => 'The price must be a number.',
            'rows.*.price.min' => 'The price must be at least 0.',

            'rows.*.product_id.required' => 'Please select a medicine.',
            'rows.*.product_id.integer' => 'Please select a medicine.',
            'rows.*.product_id.min' => 'Please select a medicine.',

        ];
    }

//    #[Validate(['rows.*.qty' => 'required|integer|min:0'])]
//    #[Validate(['rows.*.pieces' => 'required|integer|min:0'])]
//    #[Validate(['rows.*.price' => 'required|numeric|min:0'])]
//    #[Validate(['rows.*.discount' => 'nullable|numeric|min:0|max:100'])]
    public function updateInvoice($id, Request $request)
    {
        $request->validate([
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ]);


        DB::beginTransaction();

        try {

            $rows = $request->input('rows', []);
            $keyGroups = [];
            $duplicateIndexes = [];

            foreach ($rows as $index => $row) {
                $productId = $row['product_id'] ?? null;

                // Treat null values as empty strings to normalize keys
                $key = $productId;

                // Group indexes by the key
                if (!isset($keyGroups[$key])) {
                    $keyGroups[$key] = [];
                }

                $keyGroups[$key][] = $index;
            }

            foreach ($keyGroups as $indexes) {
                if (count($indexes) > 1) {
                    $duplicateIndexes = array_merge($duplicateIndexes, $indexes);
                }
            }

            if (!empty($duplicateIndexes)) {
                $duplicateIndexes = array_values(array_unique($duplicateIndexes));

                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate product rows found.',
                    'duplicate_indexes' => $duplicateIndexes,
                ]);
            }

            $this->rows = $request->post('rows');
            $userId = $request->post('userId') ?? null;

            $orderInvoice = Invoices::find($id);


            $this->invoiceDetails = $orderInvoice;

            if ($orderInvoice->status == ACTIVE_STATUS){
                return response()->json([
                    'status'=>false,
                    'message'=>'This invoice is already approved by Admin! Kindly contact with admin.',
                ]);
            }

            $invoiceOrderItems = $request->post('rows');
            $this->userId = !empty($request->userId) ? $request->userId : null;

            if ($this->userId == null && $request->post('dueAmount') > 0 && \auth()->user()->role == ADMIN_ROLE) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unknown user detected. Due amount should be zero for unknown users!',
                ]);
            }

            if (\Auth::user()->role == ADMIN_ROLE){

                $getResponse =  $this->checkMedicineStock($invoiceOrderItems);

                $productList =  $getResponse['productList'] ?? false;

                if ($getResponse && !empty($productList)){
                    return response()->json([
                        'status'=>false,
                        'message'=> 'Cannot create invoice. The following medicine have insufficient stock: '.$productList,
                        'productList'=> $productList,
                    ]);
                }
            }

            $status = Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS;

            $invoiceData = [
                'total_amount' => $request->totalMedicinePrice ? round((float) str_replace(',', '', $request->totalMedicinePrice), 2) : 0,
                'custom_discount' => $request->custom_discount ? round((float) str_replace(',', '', $request->custom_discount), 2) : 0,
                'other_charges' => $request->otherCharge ? round((float) str_replace(',', '', $request->otherCharge), 2) : 0,
                'final_total' => $request->grandTotal ? round((float) str_replace(',', '', $request->grandTotal), 2) : 0,
                'paid_amount' => $request->paidAmount ? round((float) str_replace(',', '', $request->paidAmount), 2) : 0,
                'due_amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                'note' => $request->note ?? '',
                'status' => $status,
//                'created_at' => Carbon::now(),
            ];

            $orderInvoice->update($invoiceData);

            OrderItem::where('invoice_id', $id)->delete();
            $invoiceItems = [];
           foreach ($request->post('rows') as $item){

               $invoiceItems[]=[
                   'invoice_id'=>$id,
                   'user_id'=>$request->userId,
                   'company_id'=>$item['companyId'],
                   'product_id'=>$item['product_id'],
                   'box_qty'=>$item['qty'] ?? 0,
                   'quantity'=>$item['pieces'] ?? 0,
                   'price'=>$item['price'],
                   'discount'=>$item['discount'] ?? 0,
                   'final_total'=>$item['total'],
                   'updated_at'=>now(),
               ];

//               OrderItem::updateOrCreate(['id' => $item['orderItemId']], $invoiceItems);

           }

           OrderItem::insert($invoiceItems);

            if (\Auth::user()->role == ADMIN_ROLE) {

                $orderInvoice->update([
                    'status' => ACTIVE_STATUS
                ]);

                $this->reduceMedicineStock();
            }

            if (\Auth::user()->role == ADMIN_ROLE && $userId) {

                $userInvoiceTransaction = Transaction::where('user_id', $userId)
                    ->where('invoice_id', $id)->first();

                $user = \App\Models\User::where('id', $userId)->first();

                if ($request->dueAmount > 0 && !empty($userId) && empty($userInvoiceTransaction)) {
                    $temTransactionData = [
                        'user_id' => !empty($userId) ? $userId : null,
                        'invoice_id' => $id,
                        'amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                        'type' => DEBIT,
                        'note' => $request->note,
                        'paid_date' => Carbon::now(),
                    ];

                    createTransaction($temTransactionData);

                    $user->update([
                        'balance' => ((float)$user->balance + ($request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0))
                    ]);

                } else {
                    if ($request->dueAmount > 0 && (float)$userInvoiceTransaction->amount != (float)$request->dueAmount) {
                        $temTransactionData = [
                            'user_id' => !empty($userId) ? $userId : null,
                            'invoice_id' => $id,
                            'amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                            'type' => DEBIT,
                            'note' => $request->note,
                            'paid_date' => Carbon::now(),
                        ];

                        createTransaction($temTransactionData);

                        $user->update([
                            'balance' => ((float)$user->balance + ($request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0))
                        ]);
                    }
                }
            }

            DB::commit();

            if (Auth::user()->role == ADMIN_ROLE){

                Invoices::find($id)->update([
                   'created_at'=> Carbon::now(),
                ]);
            }

            return response()->json([
                'status'=>true,
                'message'=>'Invoice updated successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage(),
            ]);
        }
    }

    public function updateUserInvoice($id, Request $request)
    {
        $request->validate([
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ]);


        DB::beginTransaction();

        try {

            $rows = $request->input('rows', []);
            $keyGroups = [];
            $duplicateIndexes = [];

            foreach ($rows as $index => $row) {
                $productId = $row['product_id'] ?? null;

                // Treat null values as empty strings to normalize keys
                $key = $productId;

                // Group indexes by the key
                if (!isset($keyGroups[$key])) {
                    $keyGroups[$key] = [];
                }

                $keyGroups[$key][] = $index;
            }

            foreach ($keyGroups as $indexes) {
                if (count($indexes) > 1) {
                    $duplicateIndexes = array_merge($duplicateIndexes, $indexes);
                }
            }

            if (!empty($duplicateIndexes)) {
                $duplicateIndexes = array_values(array_unique($duplicateIndexes));

                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate product rows found.',
                    'duplicate_indexes' => $duplicateIndexes,
                ]);
            }

            $this->rows = $request->post('rows');
            $userId = $request->post('userId') ?? null;

            $orderInvoice = Invoices::find($id);

            if ($orderInvoice->attempt_by_admin == ACTIVE_STATUS){
                return response()->json([
                    'status'=>false,
                    'message'=>'Admin has already approved!',
                ]);
            }

            $this->invoiceDetails = $orderInvoice;

            $invoiceOrderItems = $request->post('rows');
            $this->userId = !empty($request->userId) ? $request->userId : null;

            $status = Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS;

            $invoiceData = [
                'total_amount' => $request->totalMedicinePrice ? round((float) str_replace(',', '', $request->totalMedicinePrice), 2) : 0,
                'custom_discount' => ((float)$request->custom_discount <= 0) ? $orderInvoice->custom_discount : round((float) str_replace(',', '', $request->custom_discount), 2),
                'other_charges' => empty($request->otherCharge) ? $orderInvoice->other_charges : round((float) str_replace(',', '', $request->otherCharge), 2),
                'final_total' => $request->grandTotal ? round((float) str_replace(',', '', $request->grandTotal), 2) : 0,
                'paid_amount' => empty($request->paidAmount) ? $orderInvoice->paid_amount : round((float) str_replace(',', '', $request->paidAmount), 2),
                'due_amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                'note' => $request->note ?? '',
                'status' => $status,
            ];

            $orderInvoice->update($invoiceData);

            OrderItem::where('invoice_id', $id)->delete();
            $invoiceItems = [];

            foreach ($request->post('rows') as $item){

                $invoiceItems[]=[
                    'invoice_id'=>$id,
                    'user_id'=>$request->userId,
                    'company_id'=>$item['companyId'],
                    'product_id'=>$item['product_id'],
                    'box_qty'=>$item['qty'] ?? 0,
                    'quantity'=>$item['pieces'] ?? 0,
                    'price'=>$item['price'],
                    'discount'=>$item['discount'] ?? 0,
                    'final_total'=>$item['total'],
                    'updated_at'=>now(),
                ];

            }

            OrderItem::insert($invoiceItems);

            DB::commit();

            return response()->json([
                'status'=>true,
                'message'=>'Invoice updated successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage(),
            ]);
        }
    }


    function checkMedicineStock($invoiceOrderItems)
    {

        $outOfStockItems = [];

        foreach ($invoiceOrderItems as $item) {

            $availableStock = (int)$item['stock'] ?? 0;
            $box_per_pic = (int)$item['box_per_pic'] ?? 0;
            $totalBoxQty = 0;


            if (!empty($item['qty'])){
                $totalBoxQty = (int)$item['qty'] * $box_per_pic ;
            }

            $totalRequired = $totalBoxQty + (int)$item['pieces'];


            if ($totalRequired > $availableStock) {
                $outOfStockItems[] = $item['productName'];
            }
        }

        if (!empty($outOfStockItems)) {
            $productList = implode(', ', $outOfStockItems);

            return [
                'productList'=>$productList
            ];
        }
    }

    public function generatePdf(Request $request)
    {

        $discount = $request->post('type');
        $data = $request->all();

        if (!empty($discount) && $discount == 'withDiscount') {

            $pdfContent = $this->generateInvoicePdfWithDiscount($data);

        } else {

            $pdfContent = $this->generateInvoicePdf($data);

        }

        if (!$pdfContent) {
            return response()->json([
                'status'=>true,
                'message'=> 'Failed to generate PDF!'
            ]);
        }

        // URL-safe base64 encoding
        $base64 = base64_encode($pdfContent);
        $base64 = rtrim($base64, '=');
        $base64 = strtr($base64, '+/', '-_');

//        $this->dispatch('show-pdf', $base64);

        return response()->json([
            'status'=>true,
            'message'=> 'Invoice created successfully!',
            'InvoicePdf'=> $base64,
        ]);
    }

    public function generateUserPdf(Request $request)
    {

        $discount = $request->post('type');
        $data = $request->all();

        if (!empty($discount) && $discount == 'withDiscount') {

            $pdfContent = $this->generateInvoicePdfWithDiscount($data);

        } else {

            $pdfContent = $this->generateInvoicePdf($data);

        }

        if (!$pdfContent) {
            return response()->json([
                'status'=>true,
                'message'=> 'Failed to generate PDF!'
            ]);
        }

        // URL-safe base64 encoding
        $base64 = base64_encode($pdfContent);
        $base64 = rtrim($base64, '=');
        $base64 = strtr($base64, '+/', '-_');

//        $this->dispatch('show-pdf', $base64);

        return response()->json([
            'status'=>true,
            'message'=> 'Invoice created successfully!',
            'InvoicePdf'=> $base64,
        ]);
    }

    private function reduceMedicineStock()
    {
        foreach ($this->rows as $row) {
            $product = Product::find($row['product_id']);
            $box_per_pic = $product->box_per_pic;
            $totalQty = $row['pieces'];
            if (!empty($row['qty'])) {
                $boxPrice = (int)$row['qty'] * $box_per_pic;
                $totalQty = (double)$boxPrice + (int)$totalQty;
            }
            Product::find($row['product_id'])->update([
                'stock' => max(0, $product->stock - $totalQty)
            ]);
        }
    }

    private function updateUserBalance($invoiceDueAmount)
    {
        $user = User::where('id', $this->userId)->first();
        $user->update([
            'balance' => ((float)$user->balance + (float)$invoiceDueAmount)
        ]);
    }

    private function generateInvoicePdf($request)
    {
        try {

            $invoiceId = $request['invoiceId'];

            $invoice = Invoices::find($invoiceId);


            $this->rows = OrderItem::where('invoice_id',$invoiceId)->get()->toArray();

            $this->invoiceDetails = $invoice;
            $this->grandTotal = $invoice->final_total;
            $this->totalMedicinePrice = $invoice->total_amount;
            $this->paidAmount = $invoice->paid_amount;
            $this->otherCharge = $invoice->other_charges;
            $this->dueAmount = $invoice->due_amount;

            if (!empty($this->invoiceDetails)) {
                $shopName = getSettingsData('shopName');

                $data = [
                    'title' => mb_convert_encoding($shopName, 'UTF-8', 'auto'),
                    'date' => date('d-M-Y'),
                    'orderItems' => $this->rows,
                    'invoiceDetails' => $invoice,
                    'grandTotal' => $this->grandTotal,
                    'total' => $this->totalMedicinePrice,
                    'paidAmt' => $this->paidAmount,
                    'otherCharge' => $this->otherCharge,
                    'dueAmt' => $this->dueAmount,
                ];

                $pdf = PDF::loadView('pdf.copy-invoice-withOutDiscount', $data)
                    ->setOptions([
                        'defaultFont' => 'Arial',
                        'dpi' => 96,
                        'isHtml5ParserEnabled' => true
                    ])
                    ->setPaper([0, 0, 420, 800]);
//                    ->setPaper('a5', 'portrait');
//                    ->setPaper([0, 0, 600, 1300]);
//
//            $fileName = 'invoice-'.str_replace('#','',$this->invoiceDetails->invoice_id).'_' . time() . '.pdf';

                return $pdf->output();
            }

        } catch (\Exception $e) {
            $this->dispatch('pdf-error', ['message' => $e->getMessage()]);
            dd($e->getMessage());
            return;
        }
    }

    private function generateInvoicePdfWithDiscount($request)
    {
        try {

            $invoiceId = $request['invoiceId'];

            $invoice = Invoices::find($invoiceId);

            $this->rows = OrderItem::where('invoice_id',$invoiceId)->get()->toArray();

            $this->invoiceDetails = $invoice;
            $this->grandTotal = $invoice->final_total;
            $this->totalMedicinePrice = $invoice->total_amount;
            $this->paidAmount = $invoice->paid_amount;
            $this->otherCharge = $invoice->other_charges;
            $this->dueAmount = $invoice->due_amount;


            if (!empty($this->invoiceDetails)) {
                $shopName = getSettingsData('shopName');

                $data = [
                    'title' => mb_convert_encoding($shopName, 'UTF-8', 'auto'),
                    'date' => date('d-M-Y'),
                    'orderItems' => $this->rows,
                    'invoiceDetails' => $invoice,
                    'grandTotal' => $this->grandTotal,
                    'total' => $this->totalMedicinePrice,
                    'paidAmt' => $this->paidAmount,
                    'otherCharge' => $this->otherCharge,
                    'dueAmt' => $this->dueAmount,
                    'discount' => 'withDiscount'
                ];

                $pdf = PDF::loadView('pdf.copy-invoice', $data)
                    ->setOptions([
                        'defaultFont' => 'Arial',
                        'dpi' => 96,
                        'isHtml5ParserEnabled' => true
                    ])
                    ->setPaper([0, 0, 420, 800]);
//                    ->setPaper('a5', 'portrait');
//                    ->setPaper([0, 0, 600, 1500]);


                return $pdf->output();
            }

        } catch (\Exception $e) {

            dd($e->getMessage());
            return;
        }
    }

    public function fileDownloaded($fileUrl)
    {
        $filePath = str_replace('/storage', '', parse_url($fileUrl, PHP_URL_PATH));

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
            \Log::info("PDF file deleted after download: " . $filePath);
        } else {
            \Log::error("File not found for deletion: " . $filePath);
        }
    }

    public function approveInvoice()
    {

        $invoiceOrderItems = OrderItem::with('product')->where('invoice_id', $this->invoiceId)->get();
        $outOfStockItems = [];

        foreach ($invoiceOrderItems as $item) {

            $availableStock = $item->product->stock ?? 0;
            $box_per_pic = $item->product->box_per_pic ?? 0;
            $totalBoxQty = 0;

            if (!empty($item->box_qty)) {
                $totalBoxQty = $item->box_qty * $box_per_pic;
            }

            $totalRequired = $totalBoxQty + $item->quantity;

            if ($totalRequired > $availableStock) {
                $outOfStockItems[] = $item->product->name;
            }
        }

        if (!empty($outOfStockItems)) {
            $productList = implode(', ', $outOfStockItems);
            $this->dispatch('toast', type: 'error', message: "Cannot approve invoice. The following medicine have insufficient stock: $productList");
            return;
        }


        $this->reduceMedicineStock();

        Invoices::find($this->invoiceId)->update([
            'status' => ACTIVE_STATUS
        ]);

        if ($this->dueAmount != 0) {
            $temTransactionData = [
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'amount' => $this->paidAmount,
                'type' => DEBIT,
                'note' => $this->note,
                'paid_date' => Carbon::now(),
            ];

            createTransaction($temTransactionData);
        }

        if (!empty($this->userId)) {

            $this->updateUserBalance();
        }

        $this->dispatch('toast', type: 'success', message: 'Invoice approved successfully!');
    }

    public function sendAdminApproval()
    {
        $this->reduceMedicineStock();
        Invoices::find($this->invoiceId)->update([
            'status' => PENDING_STATUS
        ]);

        $this->dispatch('toast', type: 'success', message: 'Invoice send for admin approval successfully!');
    }


    #[Layout('layout.app')]
    #[Title('Invoice Details')]
    public function render()
    {
        return view('livewire.admin.invoice-details', [
            'page' => 'Invoice Details',
            'page1' => 'Customer Details',
            'index' => 0,
            'invoiceDetailsId' => !empty($this->invoiceDetails) ? $this->invoiceDetails->invoice_id : ''
        ]);
    }
}
