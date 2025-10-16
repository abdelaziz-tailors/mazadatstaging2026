<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

use App\Helpers\TranslationHelper;
use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;

use App\Models\City;
use App\Models\Country;

use App\Http\Requests\Dashboard\City\StoreCityRequest;
use App\Http\Requests\Dashboard\City\UpdateCityRequest;

class CityController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        //$this->authorizable('view cities');
        return view('dashboard.pages.cities.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $Citys = City::select('id', 'name->'.app()->getLocale().' as name', 'is_active')
            ->where('admin_id',Auth::guard('admin')->user()->id);
        return Datatables::of($Citys)
            ->editColumn('is_active', function(City $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.cities.active_toogler', $item->id)]);
            })
            ->addColumn('action', function(City $item) {
                return view('dashboard.pages.cities.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        //$this->authorizable('add city');
        return view('dashboard.pages.cities.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreCityRequest $request)
    {
        //$this->authorizable('add city');
        City::create(['name' => json_encode($request->name),
            'admin_id' => Auth::guard('admin')->user()->id,

            'is_active' => (request()->has('is_active')) ? true : false]);
        Toastr::success(TranslationHelper::translate('New City Created Successfully'));
        return redirect('admin/cities');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        //$this->authorizable('edit city');
        $city = City::findorfail($id);
        if ($city->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('dashboard.pages.cities.edit', compact(['city']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateCityRequest $request, $id)
    {
        //$this->authorizable('edit city');
        $city = City::findorfail($id);
        if ($city->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }

        $city->update(['name' => json_encode($request->name), 'is_active' => (request()->has('is_active')) ? true : false]);
        Toastr::success(TranslationHelper::translate('City Updated Successfully'));
        return redirect('admin/cities');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //$this->authorizable('delete city');
        $city = City::findorfail($id);
        $city->delete();
        Toastr::success(TranslationHelper::translate('City Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        //$this->authorizable('view cities');
        $item = City::findorfail($id);
        $this->trait_active_toogler($item);
    }
}
