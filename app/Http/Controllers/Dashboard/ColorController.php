<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Color\StoreColorRequest;
use App\Http\Requests\Dashboard\Color\UpdateColorRequest;
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
class ColorController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {

        //$this->authorizable('view colors');
        return view('dashboard.pages.colors.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $packages = Color::select('id','name->'.app()->getLocale().' as name', 'is_active');
        return Datatables::of($packages)
            ->editColumn('is_active', function(Color $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.colors.active_toogler', $item->id)]);
            })
            ->addColumn('action', function(Color $item) {
                return view('dashboard.pages.colors.actions')
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
        //$this->authorizable('add color');
        return view('dashboard.pages.colors.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {


        //$this->authorizable('add color');
        Color::create([
            'name' => json_encode($request->name),
            'admin_id' => Auth::guard('admin')->user()->id,
            'color' =>$request->color,
            'is_active' => (request()->has('is_active')) ? true : false]);


        Toastr::success(TranslationHelper::translate('New color Created Successfully'));
        return redirect('admin/colors');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        //$this->authorizable('edit color');
        $data = Color::findorfail($id);
        // if ($data->admin_id !== Auth::guard('admin')->user()->id) {
        //     abort(403, 'Unauthorized access.');
        // }

        return view('dashboard.pages.colors.edit', compact(['data']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateColorRequest $request, $id)
    {
        //$this->authorizable('edit color');
        $data = Color::findorfail($id);
        // if ($data->admin_id !== Auth::guard('admin')->user()->id) {
        //     abort(403, 'Unauthorized access.');
        // }

        $data->update([
            'name' => json_encode($request->name),
            'color' =>$request->color,
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('color Updated Successfully'));
        return redirect('admin/colors');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //$this->authorizable('delete color');
        $data = Color::findorfail($id);
        $data->delete();
        Toastr::success(TranslationHelper::translate('color Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        //$this->authorizable('view colors');
        $data = Color::findorfail($id);
        $this->trait_active_toogler($data);
    }
}
