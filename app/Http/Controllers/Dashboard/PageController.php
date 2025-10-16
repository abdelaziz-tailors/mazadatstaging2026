<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Gift\StoreGiftRequest;
use App\Http\Requests\Dashboard\Gift\UpdateGiftRequest;
use App\Http\Requests\Dashboard\Package\StorePackageRequest;
use App\Http\Requests\Dashboard\Package\UpdatePackageRequest;
use App\Http\Requests\Dashboard\Page\UpdatePageRequest;
use App\Models\Gift;
use App\Models\Package;
use App\Models\Page;
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
class PageController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit page');
        $packages = Page::findorfail($id);
        return view('dashboard.pages.pages.edit', compact(['packages']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdatePageRequest $request, $id)
    {
        $this->authorizable('edit page');
        $packages = Page::findorfail($id);
        $packages->update([
            'name' => json_encode($request->name),
            'description' => json_encode($request->description),
            'image' => ($request->hasFile('image_png')) ? Storage::disk('public')->putFile('pages', $request->file('image_png')) : $packages->image,

        ]);

        Toastr::success(TranslationHelper::translate('package Updated Successfully'));
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
}
