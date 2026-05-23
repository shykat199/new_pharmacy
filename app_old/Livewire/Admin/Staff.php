<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use function Laravel\Prompts\alert;

class Staff extends Component
{
    public $isOpen = false;
    protected $listeners = ['addStaff', 'editStaff','saveStaff','updateStaff','delete'];

    public function addStaff()
    {
        $this->dispatch('openModal', 'Add New Staff', 'saveStaff');
    }

    public function saveStaff($data)
    {
        if (!empty($data)){
            $newUser = \App\Models\User::create($data);
            $userRole = $newUser->role == ADMIN_ROLE ? 'admin' : ($newUser->role == USER_ROLE ? 'user' : 'staff');
            $newUser->syncRoles([$userRole]);
        }
        $this->reset();
        $this->dispatch('closeModal');
        $this->dispatch('toast',type:'success',message:'New staff added successfully');
        $this->dispatch('pg:eventRefresh-users');
    }

    public function updateStaff($data)
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
        $this->dispatch('toast',type:'success',message:'Staff updated successfully');
        $this->dispatch('pg:eventRefresh-users');
    }

    public function delete(\App\Models\User $user)
    {
        $delete = $user->delete();
        if ($delete){
            $this->dispatch('pg:eventRefresh-users');
            $this->dispatch('toast',type:'success',message:'Staff deleted successfully');
        }else{
            $this->dispatch('toast',type:'error',message:'Staff not deleted');
        }
    }
    #[Layout('layout.app')]
    #[Title('Staff')]
    public function render()
    {
        return view('livewire.admin.staff',[
            'page'=>'Staff List'
        ]);
    }

    function addNew()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetValidation();
    }
}
