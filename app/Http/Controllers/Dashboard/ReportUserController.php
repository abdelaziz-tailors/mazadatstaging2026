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
use App\Models\UserReport;
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
class ReportUserController extends Controller
{
    use AuthorizeTrait, ActionTrait;
    public function index()
    {
        $this->authorizable('view user_reports');
        return view('dashboard.pages.report-users.index');
    }

    // get index data by ajax
    public function get_data ( Request $request) {
        $Gift = UserReport::select('id','name->'.app()->getLocale().' as name', 'is_active')
         ->get();
        return Datatables::of($Gift)
            ->editColumn('is_active', function(UserReport $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.report.users.active_toogler', $item->id)]);
            })
            ->editColumn('sound', function(UserReport $item) {
                return view('dashboard.pages.report-users.audio')
                    ->with(['item' => $item]);
            })



            ->addColumn('action', function(UserReport $item) {
                return view('dashboard.pages.report-users.actions')
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
        $this->authorizable('add user_report');
        return view('dashboard.pages.report-users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Dashboard\City\StoreCityRequest  $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(StoreReportRequest $request)
    {
        $this->authorizable('add user_report');
        UserReport::create([
            'name' => json_encode($request->name),
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('New Report Created Successfully'));
        return redirect('admin/report-users');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $this->authorizable('edit user_report');
        $gift = UserReport::findorfail($id);
        return view('dashboard.pages.report-users.edit', compact(['gift']));
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
        $this->authorizable('edit user_report');
        $Gift = UserReport::findorfail($id);
        $Gift->update([
            'name' => json_encode($request->name),
            'is_active' => (request()->has('is_active')) ? true : false]);

        Toastr::success(TranslationHelper::translate('Report Updated Successfully'));
        return redirect('admin/report-users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorizable('delete user_report');
        $city = UserReport::findorfail($id);
        $city->delete();
        Toastr::success(TranslationHelper::translate('Report Deleted Successfully'));
        return redirect()->back();
    }

    public function active_toogler ($id, Request $request) {
        $this->authorizable('view user_reports');
        $item = UserReport::findorfail($id);
        $this->trait_active_toogler($item);
    }
}
