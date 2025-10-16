<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Gift\StoreGiftRequest;
use App\Http\Requests\Dashboard\Gift\UpdateGiftRequest;
use App\Http\Requests\Dashboard\Report\StoreReportRequest;
use App\Http\Requests\Dashboard\Report\UpdateReportRequest;
use App\Http\Requests\Dashboard\Sound\StoreSoundRequest;
use App\Http\Requests\Dashboard\Sound\UpdateSoundRequest;
use App\Models\Gift;
use App\Models\Report;
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
class ReportController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {
        $this->authorizable('view sounds');
        return view('dashboard.pages.reports.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $Gift = Report::select('id','name->'.app()->getLocale().' as name', 'is_active')
         ->get();
        return Datatables::of($Gift)
            ->editColumn('is_active', function(Report $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.reports.active_toogler', $item->id)]);
            })
            ->editColumn('sound', function(Report $item) {
                return view('dashboard.pages.reports.audio')
                    ->with(['item' => $item]);
            })



            ->addColumn('action', function(Report $item) {
                return view('dashboard.pages.reports.actions')
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
        $this->authorizable('add report');
        return view('dashboard.pages.reports.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreReportRequest $request)
    {
        $this->authorizable('add report');
        Report::create([
            'name' => json_encode($request->name),
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('New Sound Created Successfully'));
        return redirect('admin/reports');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit report');
        $gift = Report::findorfail($id);
        return view('dashboard.pages.reports.edit', compact(['gift']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\UpdateCityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UpdateReportRequest $request, $id)
    {
        $this->authorizable('edit report');
        $Gift = Report::findorfail($id);
        $Gift->update([
            'name' => json_encode($request->name),
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('Report Updated Successfully'));
        return redirect('admin/reports');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete report');
        $city = Report::findorfail($id);
        $city->delete();
        Toastr::success(TranslationHelper::translate('Report Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        $this->authorizable('view reports');
        $item = Report::findorfail($id);
        $this->trait_active_toogler($item);
    }
}
