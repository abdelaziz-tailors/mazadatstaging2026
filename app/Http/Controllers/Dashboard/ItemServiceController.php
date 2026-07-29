<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ItemService\StoreItemServiceRequest;
use App\Http\Requests\Dashboard\ItemService\UpdateItemServiceRequest;
use App\Models\Admin;
use App\Models\ItemService;
use App\Support\PartnerDashboardScope;
use App\Traits\ActionTrait;
use App\Traits\AuthorizeTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Helpers\TranslationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ItemServiceController extends Controller
{
    use AuthorizeTrait, ActionTrait;

    public function index()
    {
        return view('dashboard.pages.item-services.index', [
            'showPartnerColumn' => PartnerDashboardScope::isSuperAdmin(),
        ]);
    }

    public function get_data(Request $request)
    {
        $types = ItemService::query()
            ->with('admin')
            ->select(
                'id',
                'name->'.app()->getLocale().' as name',
                'default_price',
                'is_active',
                'admin_id'
            );

        PartnerDashboardScope::scopeItemServices($types);

        $datatable = Datatables::of($types)
            ->editColumn('default_price', function (ItemService $item) {
                return $item->default_price !== null ? number_format($item->default_price, 2) : '—';
            })
            ->editColumn('is_active', function (ItemService $item) {
                return view('dashboard.partials.actions.is_active')
                    ->with(['item' => $item, 'action' => route('admin.item-services.active_toogler', $item->id)]);
            })
            ->addColumn('action', function (ItemService $item) {
                return view('dashboard.pages.item-services.actions')
                    ->with(['item' => $item]);
            });

        if (PartnerDashboardScope::isSuperAdmin()) {
            $datatable->addColumn('partner', function (ItemService $item) {
                return $item->admin->name ?? '—';
            });
        }

        return $datatable
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('dashboard.pages.item-services.create', $this->formContext());
    }

    public function store(StoreItemServiceRequest $request)
    {
        $adminId = $this->resolveOwnerAdminId($request);

        ItemService::create([
            'name' => json_encode($request->name),
            'default_price' => $request->input('default_price'),
            'sort_order' => (int) ($request->input('sort_order') ?? 0),
            'admin_id' => $adminId,
            'is_active' => $request->has('is_active'),
        ]);

        Toastr::success(TranslationHelper::translate('item_service_created_successfully'));

        return redirect()->route('admin.item-services.index');
    }

    public function edit($id)
    {
        $data = ItemService::findOrFail($id);
        PartnerDashboardScope::ensureOwnItemService($data);

        return view('dashboard.pages.item-services.edit', array_merge(
            ['data' => $data],
            $this->formContext($data)
        ));
    }

    public function update(UpdateItemServiceRequest $request, $id)
    {
        $data = ItemService::findOrFail($id);
        PartnerDashboardScope::ensureOwnItemService($data);

        $payload = [
            'name' => json_encode($request->name),
            'default_price' => $request->input('default_price'),
            'sort_order' => (int) ($request->input('sort_order') ?? 0),
            'is_active' => $request->has('is_active'),
        ];

        if (PartnerDashboardScope::isSuperAdmin()) {
            $payload['admin_id'] = $this->resolveOwnerAdminId($request);
        }

        $data->update($payload);

        Toastr::success(TranslationHelper::translate('item_service_updated_successfully'));

        return redirect()->route('admin.item-services.index');
    }

    public function destroy($id)
    {
        $data = ItemService::findOrFail($id);
        PartnerDashboardScope::ensureOwnItemService($data);
        $data->delete();

        Toastr::success(TranslationHelper::translate('item_service_deleted_successfully'));

        return redirect()->back();
    }

    public function active_toogler($id, Request $request)
    {
        $data = ItemService::findOrFail($id);
        PartnerDashboardScope::ensureOwnItemService($data);
        $this->trait_active_toogler($data);
    }

    protected function formContext(?ItemService $data = null): array
    {
        return [
            'partners' => PartnerDashboardScope::isSuperAdmin()
                ? Admin::query()->where('type', 'partner')->orderBy('name')->get(['id', 'name'])
                : collect(),
            'showPartnerSelect' => PartnerDashboardScope::isSuperAdmin(),
            'selectedPartnerId' => old('admin_id', $data->admin_id ?? null),
        ];
    }

    protected function resolveOwnerAdminId(Request $request): int
    {
        if (PartnerDashboardScope::isPartner()) {
            return (int) Auth::guard('admin')->id();
        }

        $request->validate([
            'admin_id' => 'required|integer|exists:admins,id',
        ]);

        $partner = Admin::query()
            ->where('type', 'partner')
            ->whereKey($request->input('admin_id'))
            ->firstOrFail();

        return (int) $partner->id;
    }
}
