<?php

namespace App\Livewire\User;

use App\Http\Controllers\AuthController;
use App\Models\Invoices;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalInvoice = 0;
    public $totalDueAmount = 0;
    public $totalPaidAmount = 0;
    public $userDebitList = [];
    public $userCreditList = [];

    public function mount(): void
    {
        $authUser = Auth::user();
        $this->totalInvoice = Invoices::where('user_id',$authUser->id)->count();

//        $debitAmt =  Transaction::where('user_id',$authUser->id)->where('type',DEBIT)->sum('amount');

//        $this->totalPaidAmount = Transaction::where('user_id',$authUser->id)->where('type',CREDIT)->sum('amount');

//        $this->totalDueAmount = Invoices::where('user_id',$authUser->id)->whereIn('status',[ACTIVE_STATUS,PENDING_STATUS])->sum('due_amount');
        $this->totalDueAmount = $authUser->balance;

//        if (!empty($this->totalPaidAmount) && $this->totalPaidAmount > 0){
//            $this->totalDueAmount = ($this->totalDueAmount - $this->totalPaidAmount) ;
//
//            if (!empty($debitAmt) && $debitAmt > 0){
//                $this->totalDueAmount+=$debitAmt;
//            }
//
//        }


//        $this->totalPaidAmount = Invoices::where('user_id',$authUser->id)->sum('paid_amount');
        $this->userDebitList = Transaction::where('user_id',$authUser->id)
            ->where('transactions.type',DEBIT)->orderByDesc('id')->get();
        $this->userCreditList = Transaction::where('user_id',$authUser->id)
            ->where('transactions.type',CREDIT)->orderByDesc('id')->get();
    }

    #[Layout('layout.app')]
    #[Title('Dashboard')]
    public function render()
    {
        return view('livewire.user.dashboard');
    }
}
