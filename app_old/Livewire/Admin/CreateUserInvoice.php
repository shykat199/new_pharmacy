<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use function Symfony\Component\Translation\t;

class CreateUserInvoice extends Component
{
    public $rows = [];
    public $searchedUsers = [];
    public $selectedUsers = null;

    public $query = '';
    public $address = '';
    public $phone = '';
    public $name = '';
    public $userId = '';

    protected $listeners = ['productSelected', 'saveCustomer', 'addNewRow', 'doubleSpacePressed', 'updateDueAmount', 'addOtherChargeAmount', 'delete', 'fileDownloaded', 'updateGrandTotal'];
    public $calculatedBoxQty = 0;
    public $calculatedQty = 0;

    public $grandTotal = 0;
    public $paidAmount = '';
    public $otherCharge = '';
    public $dueAmount = 0;
    public $totalPrice = [];
    public $totalPriceWithoutDiscount = [];
    public $totalDiscount = [];
    public $invoiceDetails = null;
    public $totalMedicinePrice = 0;
    public $total_amount_without_discount = 0;
    public $custom_total_discount = 0;
    public $note = '';
    public bool $hasPendingChanges = false;

    public $userName = '';
    public $userEmail = '';
    public $userPhone = '';
    public $userAddress = '';
    public $userStatus = '';
    public $userPassword = '';


    public function mount()
    {
        $this->rows = [
            ['product' => '', 'qty' => '', 'pieces' => '', 'price' => '', 'stock' => '', 'discount' => '', 'total' => '', 'product_id' => 0, 'companyId' => 0, 'unit_price' => '', 'box_per_pic' => '']
        ];
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
        $total = (($calculatedBoxQty + $calculatedQty) * $unitPrice);

        if (!empty($discount) && !empty($total)) {
            $total = $total - ($total * $discount / 100);
        }

        $finalTotal = customRoundNumber($total);

        $this->rows[$index]['total'] = number_format($finalTotal, 2);
    }


    #[\Livewire\Attributes\On('triggerAutoSave')]
    public function autoSave()
    {
        sleep(3);

        if ($this->hasPendingChanges) {
            $this->saveInvoiceSilently();
            $this->hasPendingChanges = false;
        }
    }

    public function updatedQuery()
    {
        $this->searchedUsers = \App\Models\User::where(function ($q) {
            $q->where('name', 'like', '%' . $this->query . '%');
            $q->orWhere('phone', 'like', '%' . $this->query . '%');
        })->where('role', USER_ROLE)->get()->toArray();
    }

    public function selectUser($userId): void
    {
        $this->selectedUsers = \App\Models\User::find($userId);
        $this->query = $this->selectedUsers->name;
        $this->searchedUsers = [];
        $this->phone = $this->selectedUsers->phone;
        $this->address = $this->selectedUsers->address;
        $this->userId = $this->selectedUsers->id;
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
//            'total' => '',
//            'unit_price'=>'',
//            'box_per_pic'=>''
//        ];

        $this->rows[] = [
            'product' => '', 'qty' => '', 'pieces' => '', 'price' => '',
            'stock' => '', 'discount' => '', 'total' => '', 'product_id' => 0,
            'companyId' => 0, 'unit_price' => '', 'box_per_pic' => ''
        ];
    }

//    public function removeRow($index)
//    {
//        $currentRow = $this->rows[$index];
//        $currentRemovedTotal = $currentRow['total'];
//
//        $this->grandTotal = number_format($this->grandTotal - $currentRemovedTotal, 2);
//        $this->dueAmount = number_format((float)$this->dueAmount - $currentRemovedTotal, 2);
//
//        unset($this->rows[$index]);
//        $this->rows = array_values($this->rows);
//        $this->dispatch('update-totals');
//    }

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

//        $this->dispatch('triggerAutoSave');
    }

    public function productSelected($data)
    {
        $index = $data['index'];
        if (isset($this->rows[$index])) {
            $this->rows[$index]['product_id'] = $data['product_id'];
            $this->rows[$index]['price'] = number_format($data['price'], 2);
            $this->rows[$index]['stock'] = $data['stock'];
            $this->rows[$index]['companyId'] = $data['company_id'];
            $this->rows[$index]['unit_price'] = $data['unit_price'];
            $this->rows[$index]['box_per_pic'] = $data['box_per_pic'];
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

        $this->totalPriceWithoutDiscount[$index] = $total;

        $total -= $total * ((float)$discount / 100);

//        $this->rows[$index]['total'] = number_format($total, 2);
        $this->rows[$index]['total'] = customRoundNumber($total);

        $this->totalPrice[$index] = customRoundNumber($total);

        $this->totalMedicinePrice = number_format(array_sum($this->totalPrice), 2);

        $this->grandTotal = !empty($this->totalPrice) ? number_format(array_sum($this->totalPrice) + (float)$this->otherCharge, 2) : 0;

        $finalAmt = (float)$this->grandTotal;
        if (!empty($this->paidAmount)) {
            $finalAmt = (float)$this->grandTotal - (float)$this->paidAmount;
        }

//        $this->dueAmount = ($finalAmt + (float)$this->otherCharge) ?? 0 ;

        $this->dueAmount = !(float)$this->otherCharge ? number_format($finalAmt + (float)$this->otherCharge, 2) : 0;

        if (!empty($this->grandTotal && !empty($this->paidAmount))) {
            $this->dueAmount = (float)$this->grandTotal - (float)$this->paidAmount;
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

    public function updateGrandTotal($data)
    {
        $amt = (float)$data;
        $latestGrandTotal = $this->totalMedicinePrice + $amt;
        $this->grandTotal = customRoundNumber($latestGrandTotal);
    }

    public function addCustomer()
    {
        $this->dispatch('openModal', (object)[
            'modalId' => 'createModal',
        ]);
    }

    public function resetUser()
    {
        $this->query = 'Unknown';
        $this->searchedUsers = [];
        $this->phone = 'Unknown';
        $this->address = 'Unknown';
        $this->userId = '';
    }

    public function saveCustomer()
    {

        $validated = $this->validate([
            'userName' => 'required|string|max:255',
            'userEmail' => 'nullable|email',
            'userPhone' => 'required|string|max:20|unique:users,phone',
            'userAddress' => 'nullable|string|max:500',
//            'userStatus'   => 'required',
            'userPassword' => 'required',
        ]);

        $submittedData = [
            'name' => $this->userName,
            'slug' => \Str::slug($this->userName),
            'email' => $this->userEmail ?? '',
            'balance' => 0,
            'phone' => $this->userPhone,
            'address' => $this->userAddress,
            'role' => USER_ROLE,
            'status' => ACTIVE_STATUS,
            'password' => \Hash::make($this->userPassword),
        ];

        \App\Models\User::create($submittedData);

        $this->dispatch('toast', type: 'success', message: 'New customer added successfully');
        $this->reset();
        $this->dispatch('closeModal', (object)[
            'modalId' => 'createModal'
        ]);

    }

    public function doubleSpacePressed()
    {
        $this->addRow();
    }

    public function addNewRow()
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

//    public function createInvoice()
//    {
//        $this->validate();
//
//        DB::beginTransaction();
//
//        try {
//            $invoiceData = [
//                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
//                'created_by' => \Auth::user()->id,
//                'user_id' => !empty($this->userId) ? $this->userId : null,
//                'total_amount' => $this->total_amount_without_discount,
//                'custom_discount' => $this->custom_total_discount,
//                'other_charges' => $this->otherCharge,
//                'final_total' => $this->grandTotal,
//                'paid_amount' => $this->paidAmount,
//                'due_amount' => $this->dueAmount ?? 0,
//                'note' => $this->note ?? '',
////                'status' => \Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS
//            ];
//
//            $invoice = Invoices::create($invoiceData);
//
//            $this->invoiceDetails = $invoice;
//
//            $orderItemArray = [];
//
//            foreach ($this->rows as $row) {
//                $orderItemArray[] = [
//                    'user_id' => $this->userId,
//                    'invoice_id' => $invoice->id,
//                    'company_id' => $row['companyId'] ?? null,
//                    'product_id' => $row['product_id'] ?? null,
//                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
//                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
//                    'price' => $row['price'],
//                    'discount' => $row['discount'] == '' ? 0 : $row['discount'],
//                    'final_total' => $row['total'],
//                    'created_at' => now(),
//                    'updated_at' => now(),
//                ];
//            }
//
//            OrderItem::insert($orderItemArray);
//
//            if (\Auth::user()->role == ADMIN_ROLE) {
//
//                if ((float)$this->grandTotal == (float)$this->paidAmount) {
//                    Invoices::find($invoice->id)->update([
//                        'status' => ACTIVE_STATUS
//                    ]);
//                }
//
//                $this->reduceMedicineStock();
//            }
//
//            if (\Auth::user()->role == ADMIN_ROLE) {
//
//                $userInvoiceTransaction = Transaction::where('user_id', $this->userId)
//                    ->where('invoice_id', $invoice->id)->first();
//
//                if ($this->dueAmount != 0 && !empty($this->userId) && empty($userInvoiceTransaction)) {
//                    $temTransactionData = [
//                        'user_id' => !empty($this->userId) ? $this->userId : null,
//                        'invoice_id' => $invoice->id,
//                        'amount' => (float)$this->dueAmount,
//                        'type' => DEBIT,
//                        'note' => $this->note,
//                        'paid_date' => Carbon::now(),
//                    ];
//
//                    createTransaction($temTransactionData);
//
//                    $this->updateUserBalance();
//
//                } else {
//                    if ((float)$userInvoiceTransaction->amount != (float)$this->dueAmount) {
//                        $temTransactionData = [
//                            'user_id' => !empty($this->userId) ? $this->userId : null,
//                            'invoice_id' => $invoice->id,
//                            'amount' => (float)$this->dueAmount,
//                            'type' => DEBIT,
//                            'note' => $this->note,
//                            'paid_date' => Carbon::now(),
//                        ];
//
//                        createTransaction($temTransactionData);
//
//                        $this->updateUserBalance();
//                    }
//                }
//            }
//
//            DB::commit();
//
//            $this->dispatch('toast', type: 'success', message: 'Invoice created successfully');
//            $this->redirectRoute('admin.invoice-details', ['id' => $invoice->id]);
////            $this->reset(['rows', 'totalPrice', 'grandTotal']);
//
//        } catch (\Exception $e) {
//            DB::rollBack(); // Rollback Transaction on Error
//            $this->dispatch('toast', type: 'error', message: 'Failed to create invoice: ' . $e->getMessage());
//        }
//    }

    public function saveInvoice(Request $request)
    {
        $request->validate([
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ]);

        DB::beginTransaction(); // Commit Transaction
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

            if (!empty($request->post('invoiceId'))){
                return response()->json([
                    'status'=>true,
                    'message'=>'Success',
                    'routeUrl'=>route('admin.invoice-details',$request->post('invoiceId'))
                ]);
            }

            $getUser = \App\Models\User::where('phone',$request->phone)->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request->rows;

            if ($this->userId == null && $request->post('dueAmount') > 0 && Auth::user()->role == ADMIN_ROLE) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unknown user detected. Due amount should be zero for unknown users!',
                ]);
            }

            if (\Auth::user()->role == ADMIN_ROLE){

                $invoiceOrderItems = $this->rows;
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

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request->total_amount ? round((float) str_replace(',', '', $request->total_amount), 2) : 0,
                'custom_discount' => $request->custom_discount ? round((float) str_replace(',', '', $request->custom_discount), 2) : 0,
                'other_charges' => $request->otherCharge ? round((float) str_replace(',', '', $request->otherCharge), 2) : 0,
                'final_total' => $request->grandTotal ? round((float) str_replace(',', '', $request->grandTotal), 2) : 0,
                'paid_amount' => $request->paidAmount ? round((float) str_replace(',', '', $request->paidAmount), 2) : 0,
                'due_amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                'note' => $request->note ?? '',
                'status' => \Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS
            ];

            $invoice = Invoices::create($invoiceData);

            $this->invoiceDetails = $invoice;

            $orderItemArray = [];

            foreach ($request->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

            if (\Auth::user()->role == ADMIN_ROLE) {

                Invoices::find($invoice->id)->update([
                    'status' => ACTIVE_STATUS
                ]);

                $this->reduceMedicineStock();
            }

            if (\Auth::user()->role == ADMIN_ROLE && $this->userId) {

                $userInvoiceTransaction = Transaction::where('user_id', $this->userId)
                    ->where('invoice_id', $invoice->id)->first();

                $user = \App\Models\User::where('id', $this->userId)->first();

                if ($request->dueAmount != 0.00 && !empty($this->userId) && empty($userInvoiceTransaction)) {
                    $temTransactionData = [
                        'user_id' => !empty($this->userId) ? $this->userId : null,
                        'invoice_id' => $invoice->id,
                        'amount' => (float)$request->dueAmount,
                        'type' => DEBIT,
                        'note' => $request->note,
                        'paid_date' => Carbon::now(),
                    ];

                    createTransaction($temTransactionData);

                    $user->update([
                        'balance' => ((float)$user->balance + (float)$request->post('dueAmount'))
                    ]);

                } else {
                    if ($request->dueAmount != 0.00 && (float)$userInvoiceTransaction->amount != (float)$request->dueAmount) {
                        $temTransactionData = [
                            'user_id' => !empty($request->userId) ? $request->userId : null,
                            'invoice_id' => $invoice->id,
                            'amount' => (float)$request->dueAmount,
                            'type' => DEBIT,
                            'note' => $request->note,
                            'paid_date' => Carbon::now(),
                        ];

                        createTransaction($temTransactionData);

                        $user->update([
                            'balance' => ((float)$user->balance + (float)$request->post('dueAmount'))
                        ]);
                    }
                }
            }

            DB::commit();

//            $this->dispatch('toast', type: 'success', message: 'Invoice created successfully');
//            $this->redirectRoute('admin.invoice-details', ['id' => $invoice->id]);

            $route = (\Auth::user()->role == ADMIN_ROLE || \Auth::user()->role == STAFF_ROLE) ?  route('admin.invoice-details',$invoice->id) : route('user.invoice-details',$invoice->id);

            return response()->json([
                'status'=>true,
                'message'=>'Success',
                'routeUrl'=>$route
            ]);
//            $this->reset(['rows', 'totalPrice', 'grandTotal']);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error

            dd($e->getMessage());

//            return response()->json([
//                'status'=>false,
//                'message'=> $e->getMessage(),
//            ]);
//            $this->dispatch('toast', type: 'error', message: 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function saveUserInvoice(Request $request)
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

            $getUser = \App\Models\User::where('phone',$request->phone)->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request->rows;

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => Auth::user()->role == USER_ROLE ? \Auth::user()->id : null,
                'total_amount' => $request->totalMedicinePrice ? round((float) str_replace(',', '', $request->totalMedicinePrice), 2) : 0,
                'custom_discount' => $request->custom_discount ? round((float) str_replace(',', '', $request->custom_discount), 2) : 0,
                'other_charges' => $request->otherCharge ? round((float) str_replace(',', '', $request->otherCharge), 2) : 0,
                'final_total' => $request->grandTotal ? round((float) str_replace(',', '', $request->grandTotal), 2) : 0,
                'paid_amount' => $request->paidAmount ? round((float) str_replace(',', '', $request->paidAmount), 2) : 0,
                'due_amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                'note' => $request->note ?? '',
                'status' => PENDING_STATUS
            ];

            $invoice = Invoices::create($invoiceData);

            $this->invoiceDetails = $invoice;

            $orderItemArray = [];

            foreach ($request->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

            DB::commit();

            $route = route('user.invoice-details',$invoice->id);

            return response()->json([
                'status'=>true,
                'message'=>'Success',
                'routeUrl'=>$route
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error

            dd($e->getMessage());

        }
    }

    public function pendingInvoice(Request $request)
    {
        $request->validate([
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ]);

        DB::beginTransaction(); // Commit Transaction
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

            if (!empty($request->post('invoiceId'))){
                return response()->json([
                    'status'=>true,
                    'message'=>'Success',
                    'routeUrl'=>route('admin.invoice-details',$request->post('invoiceId'))
                ]);
            }

            $getUser = \App\Models\User::where('phone',$request->phone)->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request->rows;

            if ($this->userId == null && $request->post('dueAmount') > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unknown user detected. Due amount should be zero for unknown users!',
                ]);
            }

            if (\Auth::user()->role == ADMIN_ROLE){

                $invoiceOrderItems = $this->rows;
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

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request->total_amount ? round((float) str_replace(',', '', $request->total_amount), 2) : 0,
                'custom_discount' => $request->custom_discount ? round((float) str_replace(',', '', $request->custom_discount), 2) : 0,
                'other_charges' => $request->otherCharge ? round((float) str_replace(',', '', $request->otherCharge), 2) : 0,
                'final_total' => $request->grandTotal ? round((float) str_replace(',', '', $request->grandTotal), 2) : 0,
                'paid_amount' => $request->paidAmount ? round((float) str_replace(',', '', $request->paidAmount), 2) : 0,
                'due_amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                'note' => $request->note ?? '',
                'status' =>PENDING_STATUS
            ];

            $invoice = Invoices::create($invoiceData);

            $this->invoiceDetails = $invoice;

            $orderItemArray = [];

            foreach ($request->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

            if (\Auth::user()->role == ADMIN_ROLE) {

                Invoices::find($invoice->id)->update([
                    'status' => PENDING_STATUS
                ]);

            }

            DB::commit();


            return response()->json([
                'status'=>true,
                'message'=>'Success',
                'routeUrl'=>route('admin.invoice-details',$invoice->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error

            dd($e->getMessage());

        }
    }

    public function saveUserPendingInvoice(Request $request)
    {
        $request->validate([
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ]);

        DB::beginTransaction(); // Commit Transaction
        try {

            $getUser = Auth::user();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request->rows;


            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request->total_amount,
                'custom_discount' => $request->custom_discount,
                'other_charges' => $request->otherCharge,
                'final_total' => $request->grandTotal,
                'paid_amount' => $request->paidAmount,
                'due_amount' => $request->dueAmount ?? 0,
                'note' => $request->note ?? '',
                'status' =>PENDING_STATUS
            ];

            $invoice = Invoices::create($invoiceData);

            $this->invoiceDetails = $invoice;

            $orderItemArray = [];

            foreach ($request->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

            DB::commit();


            return response()->json([
                'status'=>true,
                'message'=>'Success',
                'routeUrl'=>route('user.invoice-details',$invoice->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error

            dd($e->getMessage());

        }
    }

    public function updatePendingInvoice(Request $request)
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

            $getUser = \App\Models\User::where('phone',$request->phone)->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request->rows;

            if ($this->userId == null && $request->post('dueAmount') > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unknown user detected. Due amount should be zero for unknown users!',
                ]);
            }
            $invoicesDetails = Invoices::find($request->invoiceId);

            if (\Auth::user()->role == ADMIN_ROLE){

                $invoiceOrderItems = $this->rows;
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

            $invoiceData = [
//                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => $invoicesDetails->created_by == \Auth::user()->id ? \Auth::user()->id : $invoicesDetails->created_by,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request->totalMedicinePrice ? round((float) str_replace(',', '', $request->totalMedicinePrice), 2) : 0,
                'custom_discount' => $request->custom_discount ? round((float) str_replace(',', '', $request->custom_discount), 2) : 0,
                'other_charges' => $request->otherCharge ? round((float) str_replace(',', '', $request->otherCharge), 2) : 0,
                'final_total' => $request->grandTotal ? round((float) str_replace(',', '', $request->grandTotal), 2) : 0,
                'paid_amount' => $request->paidAmount ? round((float) str_replace(',', '', $request->paidAmount), 2) : 0,
                'due_amount' => $request->dueAmount ? round((float) str_replace(',', '', $request->dueAmount), 2) : 0,
                'note' => $request->note ?? '',
                'status' =>PENDING_STATUS,
                'attempt_by_admin'=>Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS
            ];


            $invoice = Invoices::updateOrCreate(['id'=>$request->invoiceId],$invoiceData);

            $this->invoiceDetails = $invoice;

            OrderItem::where('invoice_id',$request->invoiceId)->delete();

            $orderItemArray = [];

            foreach ($request->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);

            if (\Auth::user()->role == ADMIN_ROLE) {

                Invoices::find($invoice->id)->update([
                    'status' => PENDING_STATUS
                ]);

            }

            DB::commit();


            return response()->json([
                'status'=>true,
                'message'=>'Success',
                'routeUrl'=>route('admin.invoice-details',$invoice->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error

            dd($e->getMessage());

        }
    }

    public function updateUserPendingInvoice(Request $request)
    {
        $request->validate([
            'rows.*.price' => 'required|numeric|min:0',
            'rows.*.product_id' => 'required|integer|min:1',
            'rows.*.companyId' => 'required|integer|min:1',
        ]);

        DB::beginTransaction(); // Commit Transaction
        try {

            $orderInvoice = Invoices::find($request->invoiceId);

            if ($orderInvoice->attempt_by_admin == ACTIVE_STATUS){
                return response()->json([
                    'status'=>false,
                    'message'=>'Admin has already approved!',
                ]);
            }


            $getUser = Auth::user();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request->rows;


            $invoiceData = [
//                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => \Auth::user()->id,
                'total_amount' => $request->totalMedicinePrice,
                'custom_discount' => $request->custom_discount,
                'other_charges' => $request->otherCharge,
                'final_total' => $request->grandTotal,
                'paid_amount' => $request->paidAmount,
                'due_amount' => $request->dueAmount ?? 0,
                'note' => $request->note ?? '',
                'status' =>PENDING_STATUS
            ];


            $invoice = Invoices::updateOrCreate(['id'=>$request->invoiceId],$invoiceData);

            $this->invoiceDetails = $invoice;

            OrderItem::where('invoice_id',$request->invoiceId)->delete();

            $orderItemArray = [];

            foreach ($request->rows as $row) {
                $orderItemArray[] = [
                    'user_id' => $this->userId,
                    'invoice_id' => $invoice->id,
                    'company_id' => $row['companyId'] ?? null,
                    'product_id' => $row['product_id'] ?? null,
                    'box_qty' => !empty($row['qty']) ? $row['qty'] : 0,
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrderItem::insert($orderItemArray);


            DB::commit();


            return response()->json([
                'status'=>true,
                'message'=>'Success',
                'routeUrl'=>route('user.invoice-details',$invoice->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback Transaction on Error

            dd($e->getMessage());

        }
    }

    public function saveInvoiceSilently($request)
    {
        try {
            DB::beginTransaction();

            $getUser = \App\Models\User::where('phone',$request['phone'])->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request['rows'];

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request['total_amount'],
                'custom_discount' => $request['custom_discount'],
                'other_charges' => $request['otherCharge'],
                'final_total' => $request['grandTotal'],
                'paid_amount' => $request['paidAmount'],
                'due_amount' => $request['dueAmount'] ?? 0,
                'note' => $request['note'] ?? '',
//                'status' => \Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS
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
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $this->rows);

            OrderItem::insert($orderItemArray);


            if (\Auth::user()->role == ADMIN_ROLE && $this->userId) {

                if ((float)$this->grandTotal == (float)$this->paidAmount) {
                    Invoices::find($invoice->id)->update([
                        'status' => ACTIVE_STATUS
                    ]);
                }


                $userInvoiceTransaction = Transaction::where('user_id', $this->userId)
                    ->where('invoice_id', $this->invoiceDetails->id)->first();


                if ($request['dueAmount'] != 0 && !empty($this->userId) && empty($userInvoiceTransaction)) {

                    $temTransactionData = [
                        'user_id' => !empty($this->userId) ? $this->userId : null,
                        'amount' => (float)$request['dueAmount'] ?? 0,
                        'type' => DEBIT,
                        'note' => $request['note'],
                        'invoice_id' => $this->invoiceDetails->id,
                        'paid_date' => Carbon::now(),
                    ];

                    createTransaction($temTransactionData);

                    if (!empty($this->userId)) {

                        $this->updateUserBalance();
                    }

                } else {

                    if ((float)$userInvoiceTransaction->amount != (float)$request['dueAmount']) {

                        $temTransactionData = [
                            'user_id' => !empty($this->userId) ? $this->userId : null,
                            'amount' => (float)$request['dueAmount'],
                            'type' => DEBIT,
                            'note' => $request['note'],
                            'invoice_id' => $this->invoiceDetails->id,
                            'paid_date' => Carbon::now(),
                        ];

                        createTransaction($temTransactionData);

                        if (!empty($this->userId)) {

                            $this->updateUserBalance();
                        }
                    }
                }
            }


            // Other logic...
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return response()->json([
                'status'=>true,
                'message'=>$e->getMessage(),
            ]);
        }
    }

    public function getMedicineItem(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json(['results' => []]);
        }

        $products = Product::with('company')
            ->where('status', ACTIVE_STATUS)
            ->when(in_array(Auth::user()->role, [ADMIN_ROLE]), function ($query) {
                $query->where('stock', '>', 0);
            })
            ->where('name', 'like', $query . '%')
            ->get();

        $results = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'text' => $product->name.' '.$product->strength.' '.\Illuminate\Support\Str::limit($product->type,3,'') .' - '.\Illuminate\Support\Str::limit($product->company->name,3,''),
                'company_id' => $product->company_id,
                'productName' => $product->name . ' '.$product->strength,
                'box_per_pic' => $product->box_per_pic,
                'unit_price' => $product->unit_price,
                'stock' => $product->stock,
                'type'=>$product->type,
                'strength'=>$product->strength,
                'company'=>\Illuminate\Support\Str::limit($product->company->name,5,''),
            ];
        });

        return response()->json(['results' => $results]);
    }

    private function reduceMedicineStock()
    {
        foreach ($this->rows as $row) {
            $product = Product::find($row['product_id']);
            $box_per_pic = $product->box_per_pic;
            $totalQty = $row['pieces'];
            if (!empty($row['qty'])) {
                $boxPrice = (int)$row['qty'] * $box_per_pic;
                $totalQty = (float)$boxPrice + (int)$totalQty;
            }
            $product->update([
                'stock' => max(0, $product->stock - $totalQty)
            ]);
        }
    }

    private function updateUserBalance($invoiceDueAmount)
    {
        $user = \App\Models\User::where('id', $this->userId)->first();
        $user->update([
            'balance' => ((float)$user->balance + (float)$invoiceDueAmount)
        ]);
    }

    public function delete(\App\Models\Invoices $invoice): void
    {
        $dltInvoice = $invoice->update([
            'status' => DELETE_STATUS
        ]);
        if ($dltInvoice) {
            $this->dispatch('pg:eventRefresh-invoices');
            $this->dispatch('toast', type: 'success', message: 'Company deleted successfully');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Company not deleted');
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

    public function closeModal(): void
    {
        $this->reset();
        $this->resetValidation();
    }


    public function generatePdf(Request $request)
    {

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

        $discount = $request->post('type');
        $data = $request->all();

        if (($data['phone'] == null || $data['address'] == null) && $request->post('dueAmount') > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Unknown user detected. Due amount should be zero for unknown users!',
            ]);
        }

        if (!empty($discount) && $discount == 'withDiscount') {

            $pdfContent = $this->generateInvoicePdfWithDiscount($data);

            if (!empty($pdfContent) && isset($pdfContent['status']) && $pdfContent['status'] == false){
                return response()->json([
                    'status'=>false,
                    'message'=> $pdfContent['message'] ?? '',
                ]);
            }

        } else {

            $pdfContent = $this->generateInvoicePdf($data);

            if (!empty($pdfContent) && isset($pdfContent['status']) && $pdfContent['status'] == false){
                return response()->json([
                    'status'=>false,
                    'message'=> $pdfContent['message'] ?? '',
                ]);
            }

        }

//        if (!$pdfContent) {
//            $this->dispatch('toast', type: 'error', message: 'Failed to generate PDF!');
//            return;
//        }

        $base64 = base64_encode($pdfContent['pdf']);
        $base64 = rtrim($base64, '=');
        $base64 = strtr($base64, '+/', '-_');

//        $this->dispatch('toast', type: 'success', message: 'Invoice created successfully!');
//
//        $this->dispatch('show-pdf', $base64);

        return response()->json([
            'status'=>true,
            'message'=> 'Invoice created successfully!',
            'InvoicePdf'=> $base64,
            'invoiceId'=> $pdfContent['invoice'],
        ]);

    }

    private function generateInvoicePdfWithDiscount($request)
    {
        try {

            $invoiceId = $request['invoiceId'];
            $status = $request['status'];

            $getUser = \App\Models\User::where('phone',$request['phone'])->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request['rows'];

            if (\Auth::user()->role == ADMIN_ROLE){

                $invoiceOrderItems = $request['rows'];

                $getResponse =  $this->checkMedicineStock($invoiceOrderItems);

                $productList =  $getResponse['productList'] ?? false;
                if ($getResponse && !empty($productList)){
                    return[
                        'status'=>false,
                        'message'=>'Cannot create invoice. The following medicine have insufficient stock: '.$productList
                    ];
                }
            }

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request['totalMedicinePrice'],
                'custom_discount' => $request['custom_discount'],
                'other_charges' => $request['otherCharge'],
                'final_total' => $request['grandTotal'],
                'paid_amount' => $request['paidAmount'],
                'due_amount' => $request['dueAmount'] ?? 0,
                'note' => $request['note'] ?? '',
                'status' => \Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS
            ];

            if ($status == 'draft'){
                $invoiceData['status']=DRAFT_STATUS;
            }

            $invoice = Invoices::updateOrCreate(
                ['id' => $invoiceId ?? null],
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
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $this->rows);

            OrderItem::insert($orderItemArray);

            if (\Auth::user()->role == ADMIN_ROLE && empty($request['invoiceId']) && empty($status)) {
                $this->reduceMedicineStock();
            }

            if (\Auth::user()->role == ADMIN_ROLE && !empty($getUser) && empty($invoiceId) && empty($status)) {

                $userInvoiceTransaction = Transaction::where('user_id', $getUser->id)
                    ->where('invoice_id', $this->invoiceDetails->id)->first();


                if ($request['dueAmount'] > 0 && !empty($getUser) && empty($userInvoiceTransaction)) {

                    $temTransactionData = [
                        'user_id' => !empty($getUser) ? $getUser->id : null,
                        'amount' => (float)$request['dueAmount'] ?? 0,
                        'type' => DEBIT,
                        'note' => $request['note'],
                        'invoice_id' => $this->invoiceDetails->id,
                        'paid_date' => Carbon::now(),
                    ];

                    createTransaction($temTransactionData);

//                    $this->updateUserBalance($request['dueAmount']);

                    $getUser->update([
                        'balance' => ((float)$getUser->balance + (float)$request['dueAmount'])
                    ]);

                }
                else {
                    if ($request['dueAmount'] > 0 && (float)$userInvoiceTransaction->amount != (float)$request['dueAmount']) {

                        $temTransactionData = [
                            'user_id' => !empty($this->userId) ? $this->userId : null,
                            'amount' => (float)$request['dueAmount'],
                            'type' => DEBIT,
                            'note' => $request['note'],
                            'invoice_id' => $this->invoiceDetails->id,
                            'paid_date' => Carbon::now(),
                        ];

                        createTransaction($temTransactionData);

//                        $this->updateUserBalance($request['dueAmount']);
                        $getUser->update([
                            'balance' => ((float)$getUser->balance + (float)$request['dueAmount'])
                        ]);
                    }
                }
            }

            if (!empty($this->invoiceDetails)) {
                $shopName = getSettingsData('shopName');

                $data = [
                    'title' => mb_convert_encoding($shopName, 'UTF-8', 'auto'),
                    'date' => date('d-M-Y'),
                    'orderItems' => $this->rows,
                    'invoiceDetails' => $this->invoiceDetails,
                    'grandTotal' => (float)$request['grandTotal'],
                    'total' => (float)$request['totalMedicinePrice'],
                    'paidAmt' => (float)$request['paidAmount'],
                    'otherCharge' => (float)$request['otherCharge'],
                    'dueAmt' => (float)$request['dueAmount'],
                    'discount' => 'withDiscount'
                ];

                $pdf = PDF::loadView('pdf.copy-invoice', $data)
                    ->setOptions(['defaultFont' => 'Arial'])
                    ->setPaper([0, 0, 600, 1300]);

                return [
                    'invoice'=>$invoice->id,
                    'pdf'=>$pdf->output()
                ];
            }

        } catch (\Exception $e) {

            dd($e->getMessage());
            return;
        }
    }

    private function generateInvoicePdf($request)
    {
        try {

            $invoiceId = $request['invoiceId'];
            $status = $request['status'];

            $getUser = \App\Models\User::where('phone',$request['phone'])->first();

            $this->userId = !empty($getUser) ? $getUser->id : null;
            $this->rows = $request['rows'];

            if (\Auth::user()->role == ADMIN_ROLE){

                $invoiceOrderItems = $request['rows'];

                $getResponse =  $this->checkMedicineStock($invoiceOrderItems);


                $productList =  $getResponse['productList'] ?? false;

                if ($getResponse && !empty($productList)){

                    return[
                        'status'=>false,
                        'message'=>'Cannot create invoice. The following medicine have insufficient stock: '.$productList
                    ];
                }

            }

            $invoiceData = [
                'invoice_id' => '#ORD' . generateUniqueInvoiceId(),
                'created_by' => \Auth::user()->id,
                'user_id' => !empty($this->userId) ? $this->userId : null,
                'total_amount' => $request['totalMedicinePrice'],
                'custom_discount' => $request['custom_discount'],
                'other_charges' => $request['otherCharge'],
                'final_total' => $request['grandTotal'],
                'paid_amount' => $request['paidAmount'],
                'due_amount' => $request['dueAmount'] ?? 0,
                'note' => $request['note'] ?? '',
                'status' => \Auth::user()->role == ADMIN_ROLE ? ACTIVE_STATUS : PENDING_STATUS
            ];

            if ($status == 'draft'){
                $invoiceData['status']=DRAFT_STATUS;
            }

            $invoice = Invoices::updateOrCreate(
                ['id' => $invoiceId ?? null],
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
                    'quantity' => !empty($row['pieces']) ? $row['pieces'] : 0,
                    'price' => $row['price'],
                    'discount' => empty($row['discount']) || $row['discount'] == '' ? 0 : $row['discount'],
                    'final_total' => $row['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $this->rows);

            OrderItem::insert($orderItemArray);

            if (\Auth::user()->role == ADMIN_ROLE && empty($request['invoiceId']) && empty($status)) {

                Invoices::find($invoice->id)->update([
                    'status' => ACTIVE_STATUS
                ]);

                $this->reduceMedicineStock();
            }


            if (\Auth::user()->role == ADMIN_ROLE && !empty($getUser) && empty($invoiceId) && empty($status)) {


                $userInvoiceTransaction = Transaction::where('user_id', $getUser->id)
                    ->where('invoice_id', $this->invoiceDetails->id)->first();


                if ($request['dueAmount'] > 0 && !empty($getUser) && empty($userInvoiceTransaction)) {

                    $temTransactionData = [
                        'user_id' => !empty($getUser) ? $getUser->id : null,
                        'amount' => (float)$request['dueAmount'] ?? 0,
                        'type' => DEBIT,
                        'note' => $request['note'],
                        'invoice_id' => $this->invoiceDetails->id,
                        'paid_date' => Carbon::now(),
                    ];

                    createTransaction($temTransactionData);

                    $getUser->update([
                        'balance' => ((float)$getUser->balance + (float)$request['dueAmount'])
                    ]);

                } else {

                    if ($request['dueAmount'] > 0 && (float)$userInvoiceTransaction->amount != (float)$request['dueAmount']) {

                        $temTransactionData = [
                            'user_id' => !empty($this->userId) ? $this->userId : null,
                            'amount' => (float)$request['dueAmount'],
                            'type' => DEBIT,
                            'note' => $request['note'],
                            'invoice_id' => $this->invoiceDetails->id,
                            'paid_date' => Carbon::now(),
                        ];

                        createTransaction($temTransactionData);

                        $getUser->update([
                            'balance' => ((float)$getUser->balance + (float)$request['dueAmount'])
                        ]);
                    }
                }
            }

            if (!empty($this->invoiceDetails)) {

                $shopName = getSettingsData('shopName');

                $data = [
                    'title' => mb_convert_encoding($shopName, 'UTF-8', 'auto'),
                    'date' => date('d-M-Y'),
                    'orderItems' => $this->rows,
                    'invoiceDetails' => $this->invoiceDetails,
                    'grandTotal' => (float)$request['grandTotal'],
                    'total' => (float)$request['totalMedicinePrice'],
                    'paidAmt' => (float)$request['paidAmount'],
                    'otherCharge' => (float)$request['otherCharge'],
                    'dueAmt' => (float)$request['dueAmount'],
                ];

                $pdf = PDF::loadView('pdf.copy-invoice-withOutDiscount', $data)
                    ->setOptions(['defaultFont' => 'Arial'])
                    ->setPaper([0, 0, 600, 1300]);

                return [
                    'invoice'=>$invoice->id,
                    'pdf'=>$pdf->output()
                ];

            }

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
            dd($e->getMessage());
            return;
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


    #[Layout('layout.app')]
    #[Title('Create Invoice')]
    public function render()
    {
        return view('livewire.admin.create-user-invoice', [
            'page' => 'Create Invoice',
            'page1' => 'Customer Details',
            'index' => 0
        ]);
    }
}
