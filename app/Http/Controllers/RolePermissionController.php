<?php

namespace App\Http\Controllers;

use App\Events\RoleAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Imports\MedicinesImport;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{

    public function addRole(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:roles',
        ]);

        Role::create([
            'name' => Str::slug(strtolower($request->post('name'))),
            'guard_name'=>'web',
        ]);
        toast('Successfully added new role','success');
        return redirect()->back();
    }
    public function updateRole(Request $request)
    {
        $id = $request->post('id');
        $role = Role::find($id);

        if ($role) {

            $role->update([
                'name' => Str::slug(strtolower($request->post('name'))),
                'guard_name' => 'web',
            ]);

            toast('Successfully updated the role', 'success');
        } else {
            toast('Role not found', 'error');
        }

        return redirect()->back();
    }

    public function getAllRole($id)
    {
        $data['allPermissions'] = Permission::get()->toArray();

        $data['chunkData'] = array_chunk($data['allPermissions'], 6);
        $data['role'] = Role::findById($id);
        $data['rolePermissionsIds'] = $data['role']->permissions->pluck('id')->toArray();


        return view('livewire.admin.add-permission',$data);
    }

    public function updateRolePermission(Request $request,$id)
    {
        $role = Role::findOrFail($id);
        $permissions = $request->input('permission', []);
        $role->permissions()->sync($permissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        toast('Successfully add permission to the role','success');
        return redirect()->back();
    }

    public function importMedicine(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        try {
            Excel::import(new MedicinesImport, $request->file('csv_file'));
            toast('Import successfully', 'success');
        } catch (\Exception $e) {

            dd($e->getMessage());
            // Log error for debugging (optional)
            \Log::error('CSV Import Error: ' . $e->getMessage());

            toast('Import failed: ' . $e->getMessage(), 'error');
        }

        return redirect()->back();
    }

}
