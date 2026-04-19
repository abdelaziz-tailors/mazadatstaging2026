<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Gift\StoreGiftRequest;
use App\Http\Requests\Dashboard\Gift\UpdateGiftRequest;
use App\Http\Requests\Dashboard\Package\StorePackageRequest;
use App\Http\Requests\Dashboard\Package\UpdatePackageRequest;
use App\Models\Gift;
use App\Models\Package;
use Illuminate\Http\Request;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Helpers\TranslationHelper;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;
use App\Models\Department;
use App\Models\Category;
use App\Support\PartnerDashboardScope;

use App\Http\Requests\Dashboard\Category\StoreCategoryRequest;
use App\Http\Requests\Dashboard\Category\UpdateCategoryRequest;
class CategoryController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {
        //$this->authorizable('view categories');
        return view('dashboard.pages.categories.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $packages = Category::query()->select('id','name->'.app()->getLocale().' as name', 'is_active');
        PartnerDashboardScope::scopeCategories($packages);
        return Datatables::of($packages)
            ->editColumn('is_active', function(Category $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.categories.active_toogler', $item->id)]);
            })
            ->addColumn('action', function(Category $item) {
                return view('dashboard.pages.categories.actions')
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
        //$this->authorizable('add category');
        return view('dashboard.pages.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreCategoryRequest $request)
    {
        //$this->authorizable('add category');
        Category::create([
            'name' => json_encode($request->name),
            'admin_id' => Auth::guard('admin')->user()->id,

            'is_active' => (request()->has('is_active')) ? true : false]);


        Toastr::success(TranslationHelper::translate('New category Created Successfully'));
        return redirect('admin/categories');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        //$this->authorizable('edit category');

        $data = Category::findorfail($id);
        PartnerDashboardScope::ensureOwnCategory($data);

        return view('dashboard.pages.categories.edit', compact(['data']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        //$this->authorizable('edit category');
        $data = Category::findorfail($id);
        PartnerDashboardScope::ensureOwnCategory($data);

        $data->update([
            'name' => json_encode($request->name),
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('category Updated Successfully'));
        return redirect('admin/categories');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //$this->authorizable('delete category');
        $data = Category::findorfail($id);
        PartnerDashboardScope::ensureOwnCategory($data);
        $data->delete();
        Toastr::success(TranslationHelper::translate('category Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        //$this->authorizable('view categories');
        $data = Category::findorfail($id);
        PartnerDashboardScope::ensureOwnCategory($data);
        $this->trait_active_toogler($data);
    }
}
