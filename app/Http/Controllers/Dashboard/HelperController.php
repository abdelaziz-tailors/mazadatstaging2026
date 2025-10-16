<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\City;
use App\Models\Department;
use App\Models\Category;

class HelperController extends Controller
{
    // Get Area Cities
    public function country_cities (Request $request) {
        $cities = City::select('id', 'name->'.app()->getLocale().' as name')->where('country_id', $request->country_id)->get();
        $cities_options = view('dashboard.partials.helpers.cities', compact(['cities']))->render();
        return response()->json(['cities' => $cities_options]);
    }

    // Banner-DropDowns
    public function banner_dropdowns (Request $request) {

        $positions = view('dashboard.partials.helpers.positions')->render();
        $page = $request->page;
        $departments = Department::select('id', 'name->'.app()->getLocale().' as name')->get();
        $options = view('dashboard.partials.helpers.options', compact(['departments', 'page']))->render();

        return response()->json(['options' => $options, 'positions' => $positions]);        
    }

    // Get Departments
    public function departments (Request $request) {
        $departments = Department::select('id', 'name->'.app()->getLocale().' as name')->get();
        $departments_options = view('dashboard.partials.helpers.departments', compact(['departments']))->render();
        return response()->json(['departments' => $departments_options]);
    }

    // Get Categories
    public function categories (Request $request) {
        if ($request->category_id == NULL) {
            $categories = Category::select('id', 'name->'.app()->getLocale().' as name')
            ->whereNull('category_id')->where('department_id', $request->department_id)
            ->get();
            $main_categories = view('dashboard.partials.helpers.main_categories', compact(['categories']))->render();
            return response()->json(['categories' => $main_categories]);    
        }
        else if ($request->category_id != NULL) {
            $categories = Category::select('id', 'name->'.app()->getLocale().' as name')
            ->where('category_id', $request->category_id)->where('department_id', $request->department_id)
            ->get();
            $sub_categories = view('dashboard.partials.helpers.sub_categories_selector', compact(['categories']))->render();
            return response()->json(['sub_categories' => $sub_categories]);    
        }
    }
}
