<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Gift\StoreGiftRequest;
use App\Http\Requests\Dashboard\Gift\UpdateGiftRequest;
use App\Models\Gift;
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
class GiftController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {
        $this->authorizable('view gifts');
        return view('dashboard.pages.gifts.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $Gift = Gift::select('id', 'coin','name->'.app()->getLocale().' as name', 'is_active')
         ->get();
        return Datatables::of($Gift)
            ->editColumn('is_active', function(Gift $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.gifts.active_toogler', $item->id)]);
            })
            ->addColumn('action', function(Gift $item) {
                return view('dashboard.pages.gifts.actions')
                    ->with(['item' => $item]);
            })
            ->rawColumns(['id', 'name','coin', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorizable('add gift');
        return view('dashboard.pages.gifts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreGiftRequest $request)
    {
        $this->authorizable('add gift');
        Gift::create(['name' => json_encode($request->name),
            'coin'=>$request->coin,
            'image_svg' => ($request->hasFile('image_svg')) ? Storage::disk('public')->putFile('gifts', $request->file('image_svg')) : 'admins/default.png',
            'image_png' => ($request->hasFile('image_png')) ? Storage::disk('public')->putFile('gifts', $request->file('image_png')) : 'admins/default.png',
            'is_active' => (request()->has('is_active')) ? true : false]);


        Toastr::success(TranslationHelper::translate('New Gift Created Successfully'));
        return redirect('admin/gifts');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit gift');
        $gift = Gift::findorfail($id);
        return view('dashboard.pages.gifts.edit', compact(['gift']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateGiftRequest $request, $id)
    {
        $this->authorizable('edit gift');
        $Gift = Gift::findorfail($id);
        $Gift->update([
            'name' => json_encode($request->name),
            'coin'=>$request->coin,
            'image_svg' => ($request->hasFile('image_svg')) ? Storage::disk('public')->putFile('gifts', $request->file('image_svg')) : $Gift->image_svg,
            'image_png' => ($request->hasFile('image_png')) ? Storage::disk('public')->putFile('gifts', $request->file('image_png')) : $Gift->image_png,
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('Gift Updated Successfully'));
        return redirect('admin/gifts');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete gift');
        $city = Gift::findorfail($id);
        $city->delete();
        Toastr::success(TranslationHelper::translate('Gift Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        $this->authorizable('view gifts');
        $item = Gift::findorfail($id);
        $this->trait_active_toogler($item);
    }
}
