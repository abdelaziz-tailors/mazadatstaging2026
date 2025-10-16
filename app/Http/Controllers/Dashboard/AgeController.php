<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Color\StoreColorRequest;
use App\Http\Requests\Dashboard\Color\UpdateColorRequest;
use App\Http\Requests\Dashboard\Name\StoreNameRequest;
use App\Http\Requests\Dashboard\Name\UpdateNameRequest;
use App\Models\Age;
use App\Models\Color;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;

use App\Http\Requests\Dashboard\Category\StoreCategoryRequest;
use App\Http\Requests\Dashboard\Category\UpdateCategoryRequest;
class AgeController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {

        //$this->authorizable('view ages');
        return view('dashboard.pages.ages.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $packages = Age::select('id','name->'.app()->getLocale().' as name', 'is_active')
            ->where('admin_id',Auth::guard('admin')->user()->id);
        return Datatables::of($packages)
            ->editColumn('is_active', function(Age $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.ages.active_toogler', $item->id)]);
            })
            ->addColumn('action', function(Age $item) {
                return view('dashboard.pages.ages.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name','action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        //$this->authorizable('add age');
        return view('dashboard.pages.ages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreNameRequest $request)
    {


        //$this->authorizable('add age');
        Age::create([
            'name' => json_encode($request->name),
            'admin_id' => Auth::guard('admin')->user()->id,

            'is_active' => (request()->has('is_active')) ? true : false]);


        Toastr::success(TranslationHelper::translate('New age Created Successfully'));
        return redirect('admin/ages');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        //$this->authorizable('edit age');
        $data = Age::findorfail($id);
        if ($data->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('dashboard.pages.ages.edit', compact(['data']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateNameRequest $request, $id)
    {
        //$this->authorizable('edit age');
        $data = Age::findorfail($id);
        if ($data->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access.');
        }

        $data->update([
            'name' => json_encode($request->name),
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('age Updated Successfully'));
        return redirect('admin/ages');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //$this->authorizable('delete age');
        $data = Age::findorfail($id);
        $data->delete();
        Toastr::success(TranslationHelper::translate('age Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        //$this->authorizable('view ages');
        $data = Age::findorfail($id);
        $this->trait_active_toogler($data);
    }
}
