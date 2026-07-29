<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\TranslationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Slider\StoreSliderRequest;
use App\Http\Requests\Dashboard\Slider\UpdateSliderRequest;
use App\Models\Slider;
use App\Traits\ActionTrait;
use App\Traits\AuthorizeTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SliderController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    public function index()
    {
        return view('dashboard.pages.sliders.index');
    }

    public function get_data(Request $request)
    {
        $sliders = Slider::select('id', 'image', 'link', 'position', 'is_active', 'created_at')
            ->orderBy('position')
            ->orderByDesc('id');

        return Datatables::of($sliders)
            ->editColumn('image', function (Slider $item) {
                return view('dashboard.pages.sliders.image')->with(['item' => $item]);
            })
            ->editColumn('link', function (Slider $item) {
                return $item->link ?: '-';
            })
            ->editColumn('is_active', function (Slider $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.sliders.active_toogler', $item->id)]);
            })
            ->editColumn('created_at', function (Slider $item) {
                return optional($item->created_at)->format('Y-m-d');
            })
            ->addColumn('action', function (Slider $item) {
                return view('dashboard.pages.sliders.actions')->with(['item' => $item]);
            })
            ->rawColumns(['image', 'is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('dashboard.pages.sliders.create');
    }

    public function store(StoreSliderRequest $request)
    {
        Slider::create([
            'image' => $request->file('image')->store('sliders', 'public'),
            'link' => $request->link,
            'position' => $request->position ?? 0,
            'is_active' => request()->has('is_active'),
        ]);

        Toastr::success(TranslationHelper::translate('New slider created successfully'));

        return redirect('admin/sliders');
    }

    public function edit($id)
    {
        $slider = Slider::findOrFail($id);

        return view('dashboard.pages.sliders.edit', compact('slider'));
    }

    public function update(UpdateSliderRequest $request, $id)
    {
        $slider = Slider::findOrFail($id);

        $slider->update([
            'image' => $request->hasFile('image')
                ? $request->file('image')->store('sliders', 'public')
                : $slider->image,
            'link' => $request->link,
            'position' => $request->position ?? 0,
            'is_active' => request()->has('is_active'),
        ]);

        Toastr::success(TranslationHelper::translate('Slider updated successfully'));

        return redirect('admin/sliders');
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();

        Toastr::success(TranslationHelper::translate('Slider deleted successfully'));

        return redirect()->back();
    }

    public function active_toogler($id, Request $request)
    {
        $item = Slider::findOrFail($id);
        $this->trait_active_toogler($item);
    }
}
