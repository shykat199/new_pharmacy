<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
use function Laravel\Prompts\alert;

class User extends Component
{

    public $isOpen = false;
    protected $listeners = ['addCustomer', 'editCustomer','saveCustomer','updateCustomer','delete','openFinanceModal','resetField'];
    public  $userName ='';
    public $dueAmount=0;
    public $totalAmount=0;
    public $paidAmount=0;
    public $totalPendingInvoice=0;
    public $totalCompletedInvoice=0;
    public $financeAmount=0;
    public $financeType='credit';
    public $userId;
    public $note;
    public $userDebitList=[];
    public $userCreditList=[];

    function addCustomer()
    {
        $this->dispatch('openModal','Add New Customer', 'saveCustomer');
    }

    public function editCustomer($id)
    {
        $this->dispatch('openModal', 'Edit Customer', 'updateCustomer');
    }

    public function saveCustomer($data)
    {
        if (!empty($data)){
            $newUser = \App\Models\User::create($data);
            $userRole = $newUser->role == ADMIN_ROLE ? 'admin' : ($newUser->role == USER_ROLE ? 'user' : 'staff');
            $newUser->syncRoles([$userRole]);
            $this->dispatch('toast',type:'success',message:'New customer added successfully');
        }
        $this->reset();
        $this->dispatch('closeModal');
        $this->dispatch('pg:eventRefresh-users');
    }

    public function updateCustomer($data)
    {
        if (!empty($data)){
            $userId = $data['userId'];
            $userData=[
                'name' => $data['name'],
                'slug' => $data['slug'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'status' => $data['status'],
            ];

            if (!empty($data['password'])){
                $userData['password'] = $data['password'];
            }
            \App\Models\User::find($userId)->update($userData);
        }
        $this->reset();
        $this->dispatch('closeModal');
        $this->dispatch('toast',type:'success',message:'Customer updated successfully');
        $this->dispatch('pg:eventRefresh-users');
    }

    public function delete(\App\Models\User $user)
    {
        $delete = $user->delete();
        if ($delete){
            $this->dispatch('pg:eventRefresh-users');
            $this->dispatch('toast',type:'success',message:'Customer deleted successfully');
        }else{
            $this->dispatch('toast',type:'error',message:'Customer not deleted');
        }
    }

    public function openFinanceModal($userId)
    {
        $user = \App\Models\User::find($userId);

        $this->totalAmount  = Invoices::where('user_id',$userId)->where('status',ACTIVE_STATUS)->sum('total_amount');

        $this->dueAmount = $user->balance ?? 0;

        $this->userName = $user->name;

        $this->userId = $userId;

        $this->userDebitList = Transaction::where('user_id',$user->id)
            ->where('transactions.type',DEBIT)->orderByDesc('id')->get();

        $this->userCreditList = Transaction::where('user_id',$user->id)
            ->where('transactions.type',CREDIT)->orderByDesc('id')->get();

    }

    #[Validate(['financeAmount' => 'required'])]
    #[Validate(['financeType' => 'required'])]
    public function submitFinance()
    {
        $this->validate();

        \DB::beginTransaction();
        try {
            $user = \App\Models\User::findOrFail($this->userId);

            if ($this->financeType === 'debit') {

                $user->balance = max(0,$user->balance + $this->financeAmount);

            } else {

                $newBalance = $user->balance - $this->financeAmount;

                $user->balance = $newBalance;
            }

            $user->save();

            $transactionData = [
                'user_id' => $this->userId,
                'amount' => $this->financeAmount,
                'type' => $this->financeType === 'debit' ? DEBIT : CREDIT,
                'note' => $this->note,
                'paid_date' => Carbon::now(),
            ];

            \App\Models\Transaction::create($transactionData);

            $debitAmt =  Transaction::where('user_id',$this->userId)->where('type',DEBIT)->sum('amount');

//            $this->totalAmount  = Invoices::where('user_id',$this->userId)->whereIn('status',[ACTIVE_STATUS,PENDING_STATUS])->sum('final_total');
//
//            if (!empty($debitAmt) && $debitAmt > 0){
//                $this->totalAmount+=$debitAmt;
//            }

            $user = \App\Models\User::findOrFail($this->userId);

            $this->dueAmount = $user->balance;

            $this->userDebitList = Transaction::where('user_id',$user->id)
                ->where('transactions.type',DEBIT)->orderByDesc('id')->get();

            $this->userCreditList = Transaction::where('user_id',$user->id)
                ->where('transactions.type',CREDIT)->orderByDesc('id')->get();


            \DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Finance updated successfully.');

            $this->financeAmount = '';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function resetField()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[Layout('layout.app')]
    #[Title('Customers')]
    public function render()
    {
        return view('livewire.admin.user',[
            'page'=>'Customer List'
        ]);
    }
}
