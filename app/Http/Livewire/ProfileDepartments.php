<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Department;
use App\Models\ProviderService;
use App\Models\User\User;
use Livewire\Component;

class ProfileDepartments extends Component
{
    public $profile_id;
    public $provider;
    public $departments = [];
    public $categories = [];
    public $sub_categories = [];
    public $sub_categories_prices = [];


    public $department;
    public $category;
    public $sub_category = [];
    

    protected $listeners = [
        'selecteddepartment', 'selectedcategory','selectedsubcategories'
    ];
    
    public function mount($profile = null)
    {
        if ($profile) {
            $this->profile_id = $profile->id;
            if ($profile->department_id) {
                $this->selecteddepartment($profile->department_id);
            }
            if ($profile->category_id) {
                $this->selectedcategory($profile->category_id);
            }
            if ($profile->services()) {
                $this->sub_category = $profile->services()->pluck('id')->toArray();
                // $this->sub_categories_prices = $profile->categories()->pluck('price','service_id')->toArray();
                // dd($this->sub_categories_prices);
                $this->selectedsubcategories($this->sub_category);
            }
        }
    }

    // public function hydrate()
    // {
    //     $this->emit('select2');
    // }

    public function selecteddepartment($department)
    {
        $this->department = $department;
        $this->categories = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->where('department_id', $department)->whereNull('category_id')->get();
        $this->sub_categories = [];
    }


    public function selectedcategory($category)
    {
        if (is_numeric($category)) {
            $this->category = $category;
        }else{
            $this->sub_categories = [];
        }
    }

    function selectedsubcategories($sub_category) {
        $this->sub_categories_prices = [];
        $sub_categories_prices = Category::select('id', 'name->'.app()->getLocale().' as name')->whereIn('id',$sub_category)->get();
        
        foreach($sub_categories_prices as $sub_categories){
            if($this->profile_id){
                $provider_service = ProviderService::where('provider_id',$this->profile_id)->where('service_id', $sub_categories->id)->first();
                if($provider_service){
                    $this->sub_categories_prices[$sub_categories->id]['price'] = $provider_service->price;
                    $this->sub_categories_prices[$sub_categories->id]['category_name'] = $sub_categories->name;
                }else{
                    $this->sub_categories_prices[$sub_categories->id]['price'] = null;
                    $this->sub_categories_prices[$sub_categories->id]['category_name'] = $sub_categories->name;
                }
            }else{
                $this->sub_categories_prices[$sub_categories->id]['price'] = null;
                $this->sub_categories_prices[$sub_categories->id]['category_name'] = $sub_categories->name;
            }
        }
    }


    public function render()
    {
        if (is_numeric($this->category)) {
            $this->categories = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->where('department_id', $this->department)->whereNull('category_id')->get();
            $this->sub_categories = Category::select('id', 'name->'.app()->getLocale().' as name')->where('is_active', 1)->where('category_id', $this->category)->whereNotNull('category_id')->get();
        }
        $this->departments = Department::select('id', 'name->'.app()->getLocale().' as name')->whereIn('slug', ['medical_section', 'services_section'])->where('is_active', 1)->get();
        return view('dashboard.livewire.profile-departments');
    }
}
