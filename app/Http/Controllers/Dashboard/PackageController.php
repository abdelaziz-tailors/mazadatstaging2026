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
use Illuminate\Support\Facades\Storage;

use App\Helpers\TranslationHelper;
use Yajra\DataTables\DataTables;

use App\Traits\AuthorizeTrait;
use App\Traits\ActionTrait;
use App\Models\Department;
use App\Models\Category;

use App\Http\Requests\Dashboard\Category\StoreCategoryRequest;
use App\Http\Requests\Dashboard\Category\UpdateCategoryRequest;
class PackageController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {
        $this->authorizable('view packages');
        return view('dashboard.pages.packages.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $packages = Package::select('id', 'coin','price','name->'.app()->getLocale().' as name', 'is_active', 'subscription_type', 'auctions_limit', 'monthly_price', 'annual_price')
         ->get();
        return Datatables::of($packages)
            ->editColumn('subscription_type', function(Package $item) {
                if ($item->subscription_type) {
                    return $item->subscription_type == 'monthly' ? TranslationHelper::translate('Monthly') : TranslationHelper::translate('Annual');
                }
                return '-';
            })
            ->editColumn('auctions_limit', function(Package $item) {
                return $item->auctions_limit ?? '-';
            })
            ->editColumn('monthly_price', function(Package $item) {
                return $item->monthly_price ? number_format($item->monthly_price, 2) : '-';
            })
            ->editColumn('annual_price', function(Package $item) {
                return $item->annual_price ? number_format($item->annual_price, 2) : '-';
            })
            ->editColumn('is_active', function(Package $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.packages.active_toogler', $item->id)]);
            })
            ->addColumn('action', function(Package $item) {
                return view('dashboard.pages.packages.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name','coin','price', 'subscription_type', 'auctions_limit', 'monthly_price', 'annual_price', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add package');
        return view('dashboard.pages.packages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StorePackageRequest $request)
    {
        $this->authorizable('add package');
        Package::create([
            'name' => json_encode($request->name),
            'description' => json_encode($request->description),
            'coin'=>$request->coin,
            'price'=>$request->price,
            'subscription_type' => $request->subscription_type,
            'auctions_limit' => $request->auctions_limit ?? 0,
            'monthly_price' => $request->monthly_price,
            'annual_price' => $request->annual_price,
            'image' => ($request->hasFile('image_png')) ? Storage::disk('public')->putFile('packages', $request->file('image_png')) : 'admins/default.png',
            'is_active' => (request()->has('is_active')) ? true : false]);


        Toastr::success(TranslationHelper::translate('New Package Created Successfully'));
        return redirect('admin/packages');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit package');
        $packages = Package::findorfail($id);
        return view('dashboard.pages.packages.edit', compact(['packages']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdatePackageRequest $request, $id)
    {
        $this->authorizable('edit package');
        $packages = Package::findorfail($id);
        $packages->update([
            'name' => json_encode($request->name),
            'description' => json_encode($request->description),
            'coin'=>$request->coin,
            'price'=>$request->price,
            'subscription_type' => $request->subscription_type,
            'auctions_limit' => $request->auctions_limit ?? 0,
            'monthly_price' => $request->monthly_price,
            'annual_price' => $request->annual_price,
            'image' => ($request->hasFile('image_png')) ? Storage::disk('public')->putFile('packages', $request->file('image_png')) : $packages->image,
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('package Updated Successfully'));
        return redirect('admin/packages');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete package');
        $city = Package::findorfail($id);
        $city->delete();
        Toastr::success(TranslationHelper::translate('package Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        $this->authorizable('view packages');
        $item = Package::findorfail($id);
        $this->trait_active_toogler($item);
    }
}
