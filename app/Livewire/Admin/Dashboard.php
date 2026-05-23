<?php

namespace App\Livewire\Admin;

use App\Models\Invoices;
use App\Models\Product;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalAdmin = 0;
    public $totalStaff = 0;
    public $totalUser = 0;
    public $totalMedicine = 0;
    public $totalOutOfMedicine = 0;
    public $totalInactiveMedicine = 0;
    public $totalPaidAmount = 0;
    public $totalDueAmount = 0;

    public $userWithDueAmount = [];
    public $outOfStockProduct = [];

    public function mount(): void
    {
        $this->totalAdmin = \App\Models\User::where('role', ADMIN_ROLE)->count();
        $this->totalStaff = \App\Models\User::where('role', STAFF_ROLE)->count();
        $this->totalUser = \App\Models\User::where('role', USER_ROLE)->count();
        $this->totalMedicine = Product::count();
        $this->totalOutOfMedicine = Product::where('stock', '=', 0)->count();
        $this->totalInactiveMedicine = Product::where('status', '=', INACTIVE_STATUS)->count();
//        $this->totalPaidAmount = Invoices::sum('paid_amount');
//        $this->totalDueAmount = Invoices::sum('due_amount');
        $this->totalDueAmount = \App\Models\User::where('role', USER_ROLE)->where('status', ACTIVE_STATUS)->sum('balance');

        $this->userWithDueAmount = Invoices::with('user')->select('due_amount', 'user_id', 'invoice_id', 'id')
            ->where('due_amount','>',0)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $this->outOfStockProduct = Product::with('company')->select('name', 'company_id', 'unit_price', 'id')
            ->where('stock', '=', 0)
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }

    public function verifyAmountPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admin = User::with('userAccessPassword')
            ->where('role', ADMIN_ROLE)
            ->firstOrFail();

        $amount = 0;

        if (Hash::check($request->password, $admin->userAccessPassword->password)) {
            if ($request->type == 'medicine') {
                $amount = totalMedicinePrice();
            } else {
                $amount = \App\Models\User::where('role', USER_ROLE)->where('status', ACTIVE_STATUS)->sum('balance');
            }
            return response()->json(
                [
                    'success' => true,
                    'type' => $request->type,
                    'amount' => number_format($amount),
                ]);
        }

        return response()->json(['success' => false]);

    }

    #[Layout('layout.app')]
    #[Title('Dashboard')]
    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
