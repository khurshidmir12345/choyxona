<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $user;
    public $company;
    
    // User fields
    public $name;
    public $email;
    public $phone_number;
    public $current_password;
    public $new_password;
    public $confirm_password;
    
    // Company fields
    public $company_name;
    public $company_address;
    public $company_phone;
    public $company_email;
    public $company_description;
    
    public $showPasswordForm = false;
    public $showCompanyForm = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->company = $this->user->getCompany();
        
        // Load user data
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone_number = $this->user->phone_number;
        
        // Load company data
        if ($this->company) {
            $this->company_name = $this->company->name;
            $this->company_address = $this->company->address;
            $this->company_phone = $this->company->phone_number;
            $this->company_email = $this->company->email;
            $this->company_description = $this->company->description;
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'phone_number' => 'required|string|max:20',
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
        ]);

        session()->flash('message', 'Profil muvaffaqiyatli yangilandi!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'confirm_password' => 'required',
        ]);

        if (!Hash::check($this->current_password, $this->user->password)) {
            session()->flash('error', 'Joriy parol noto\'g\'ri!');
            return;
        }

        $this->user->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->confirm_password = '';
        $this->showPasswordForm = false;

        session()->flash('message', 'Parol muvaffaqiyatli yangilandi!');
    }

    public function updateCompany()
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_description' => 'nullable|string|max:1000',
        ]);

        if ($this->company) {
            $this->company->update([
                'name' => $this->company_name,
                'address' => $this->company_address,
                'phone_number' => $this->company_phone,
                'email' => $this->company_email,
                'description' => $this->company_description,
            ]);
        }

        $this->showCompanyForm = false;
        session()->flash('message', 'Kompaniya ma\'lumotlari muvaffaqiyatli yangilandi!');
    }

    public function togglePasswordForm()
    {
        $this->showPasswordForm = !$this->showPasswordForm;
        $this->showCompanyForm = false;
    }

    public function toggleCompanyForm()
    {
        $this->showCompanyForm = !$this->showCompanyForm;
        $this->showPasswordForm = false;
    }

    public function render()
    {
        return view('livewire.admin.profile');
    }
} 