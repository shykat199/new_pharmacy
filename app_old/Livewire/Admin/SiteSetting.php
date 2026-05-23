<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class SiteSetting extends Component
{
    use WithFileUploads;

    public $site_logo;
    public $site_favicon;
    public $existing_logo;
    public $existing_favicon;
    public $shopName;
    public $email;
    public $phone;
    public $address;


    public function mount()
    {
        $siteSettings = getSettingsData(['shopName', 'email', 'phone', 'address','site_logo','site_favicon']);

        $this->existing_logo =$siteSettings['site_logo'] ?? '';
        $this->existing_favicon =$siteSettings['site_favicon'] ?? '';
        $this->shopName =$siteSettings['shopName'] ?? '';
        $this->email =$siteSettings['email'] ?? '';
        $this->phone =$siteSettings['phone'] ?? '';
        $this->address =$siteSettings['address'] ?? '';
    }

    public function saveSetting()
    {

        $this->validate([
            'site_logo' => 'nullable|image|max:12288|mimes:jpg,jpeg,png,svg',
            'site_favicon' => 'nullable|image|max:12288|mimes:jpg,jpeg,png,svg',
            'shopName' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);


        if ($this->site_logo) {
            $logoPath = $this->site_logo->store('settings', 'public');
            \App\Models\SiteSetting::updateOrCreate(
                ['key' => 'site_logo'],
                ['value' => $logoPath]
            );
            $this->existing_logo = $logoPath;
        }


        if ($this->site_favicon) {
            $faviconPath = $this->site_favicon->store('settings', 'public');
            \App\Models\SiteSetting::updateOrCreate(
                ['key' => 'site_favicon'],
                ['value' => $faviconPath]
            );
            $this->existing_favicon = $faviconPath;
        }


        $fields = ['shopName', 'email', 'phone', 'address'];
        foreach ($fields as $field) {
            \App\Models\SiteSetting::updateOrCreate(
                ['key' => $field],
                ['value' => $this->$field]
            );
        }

        $this->dispatch('toast', type: 'success', message: 'Settings updated successfully!');
    }

    #[Layout('layout.app')]
    #[Title('Site Setting')]
    public function render()
    {
        return view('livewire.admin.site-setting',[
            'page' => 'Site setting'
        ]);
    }
}
