<?php

namespace App\Livewire\Admin\Modal;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UserModal extends Component
{
    public $isOpen = false;
    public $modalTitle = 'Modal Title';
    public $actionMethod = '';
    protected $listeners = ['openModal','closeModal'];

    public $userId = '';
    #[Validate('required')]
    public $name = '';
    #[Validate('nullable|email')]
    public $email = '';
    #[Validate('required|unique:users,phone')]
    public $phone = '';
    #[Validate('required')]
    public $address = '';
    #[Validate('required|min:8')]
    public $password = '';
    public $status = '';

    public function openModal($title, $method, $user = null)
    {
        $this->modalTitle = $title;
        $this->actionMethod = $method;

        if ($user) {
            $this->userId   = $user['id'];
            $this->name     = $user['name'] ?? '';
            $this->email    = $user['email'] ?? '';
            $this->phone    = $user['phone'] ?? '';
            $this->address  = $user['address'] ?? '';
            $this->status  = $user['status'];
        } else {
            $this->reset(['userId', 'name', 'email', 'phone', 'address']);
        }
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset();
        $this->resetValidation();
    }

    public function executeAction()
    {
        if ($this->actionMethod) {
            $this->dispatch($this->actionMethod);
        }
        $this->closeModal();
    }

    public function saveStaff()
    {
        $this->validate();
        $submittedData = [
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'email' => $this->email??'',
            'balance' => 0,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => STAFF_ROLE,
            'status' => ACTIVE_STATUS,
            'password' => \Hash::make($this->password),
        ];
        $this->dispatch('saveStaff', $submittedData);
    }

    public function updateStaff()
    {

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|unique:users,phone,' . $this->userId,
        ]);

        $submittedData = [
            'name' => $this->name,
            'userId' => $this->userId,
            'slug' => \Str::slug($this->name),
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => STAFF_ROLE,
            'status' => $this->status,
            'password' => \Hash::make($this->password),
        ];

        $this->dispatch('updateStaff', $submittedData);
    }

    public function saveCustomer()
    {
        $this->validate();
        $submittedData = [
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'email' => $this->email ?? '',
            'balance' => 0,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => USER_ROLE,
            'status' => ACTIVE_STATUS,
            'password' => \Hash::make($this->password),
        ];

        $this->dispatch('saveCustomer', $submittedData);
    }

    public function updateCustomer()
    {

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|unique:users,phone,' . $this->userId,
        ]);

        $submittedData = [
            'name' => $this->name,
            'userId' => $this->userId,
            'slug' => \Str::slug($this->name),
            'email' => $this->email ?? '',
            'phone' => $this->phone ?? '',
            'address' => $this->address ?? '',
            'password' => \Hash::make($this->password),
            'role' => USER_ROLE,
            'status' => $this->status,
        ];

        $this->dispatch('updateCustomer', $submittedData);
    }

    public function render()
    {
        return view('livewire.admin.modal.user-modal');
    }
}
