<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Gift\StoreGiftRequest;
use App\Http\Requests\Dashboard\Gift\UpdateGiftRequest;
use App\Http\Requests\Dashboard\Sound\StoreSoundRequest;
use App\Http\Requests\Dashboard\Sound\UpdateSoundRequest;
use App\Models\Gift;
use App\Models\Sound;
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
class SoundController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {
        $this->authorizable('view sounds');
        return view('dashboard.pages.sounds.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $Gift = Sound::select('id', 'sound','name->'.app()->getLocale().' as name', 'is_active')
         ->get();
        return Datatables::of($Gift)
            ->editColumn('is_active', function(Sound $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.sounds.active_toogler', $item->id)]);
            })
            ->editColumn('sound', function(Sound $item) {
                return view('dashboard.pages.sounds.audio')
                    ->with(['item' => $item]);
            })



            ->addColumn('action', function(Sound $item) {
                return view('dashboard.pages.sounds.actions')
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
        $this->authorizable('add sound');
        return view('dashboard.pages.sounds.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreSoundRequest $request)
    {
        $this->authorizable('add gift');
        Sound::create([
            'name' => json_encode($request->name),
            'artist_name' => json_encode($request->artist_name),
            'sound' => ($request->hasFile('sound')) ? Storage::disk('public')->putFile('sounds', $request->file('sound')) : 'admins/default.png',
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('sounds', $request->file('image')) : 'admins/default.png',
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('New Sound Created Successfully'));
        return redirect('admin/sounds');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit sound');
        $gift = Sound::findorfail($id);
        return view('dashboard.pages.sounds.edit', compact(['gift']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateSoundRequest $request, $id)
    {
        $this->authorizable('edit gift');
        $Gift = Sound::findorfail($id);
        $Gift->update([
            'name' => json_encode($request->name),
            'artist_name' => json_encode($request->artist_name),
            'sound' => ($request->hasFile('sound')) ? Storage::disk('public')->putFile('sounds', $request->file('sound')) : $Gift->sound,
            'image' => ($request->hasFile('image')) ? Storage::disk('public')->putFile('sounds', $request->file('image')) : $Gift->image,
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('Sound Updated Successfully'));
        return redirect('admin/sounds');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete sound');
        $city = Sound::findorfail($id);
        $city->delete();
        Toastr::success(TranslationHelper::translate('Sound Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        $this->authorizable('view sounds');
        $item = Sound::findorfail($id);
        $this->trait_active_toogler($item);
    }
}
