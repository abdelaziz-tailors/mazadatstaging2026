<?php

namespace App\Services;

use App\Models\LiveVideoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/** Dashboard adapter: never replaces the collection or writes parent quantity. */
class DashboardItemPieceService
{
    public static function enabled(Request $request): bool
    {
        // Older Dashboard callers may send pieces without the new switch.
        return ! $request->has('individual_pieces') || $request->boolean('individual_pieces');
    }

    public static function rules(Request $request): array
    {
        $rules = [
            'individual_pieces' => 'sometimes|boolean',
            'deleted_piece_ids' => 'nullable|array',
            'deleted_piece_ids.*' => 'required|integer|min:1|distinct',
        ];

        if (self::enabled($request)) {
            $rules += [
                'pieces' => 'nullable|array',
                'pieces.*' => 'required|array',
                'pieces.*.id' => 'nullable|integer|min:1|distinct',
                'pieces.*.age' => 'nullable|string|max:255',
                'pieces.*.gender' => 'nullable|string|max:255',
                'pieces.*.identifier' => 'nullable|string|max:100',
                'pieces.*.baham_count' => 'nullable|integer|min:0',
            ];
        }

        return $rules;
    }

    /** Caller holds the parent lock and encloses this operation in its transaction. */
    public static function save(LiveVideoItem $item, Request $request): void
    {
        $rows = self::enabled($request) ? ($request->input('pieces') ?? []) : [];
        $deleted = array_map('intval', $request->input('deleted_piece_ids') ?? []);
        $existing = $item->pieces()->lockForUpdate()->get()->keyBy('id');

        // Resolve every ID through this parent before performing any mutation.
        foreach (array_merge($deleted, array_filter(array_column($rows, 'id'))) as $id) {
            if (! $existing->has((int) $id)) {
                throw ValidationException::withMessages([
                    'pieces' => trans('app.dashboard_pieces_stale'),
                ]);
            }
        }

        $nextNumber = (int) $existing->max('piece_number');
        foreach ($deleted as $id) {
            $existing[$id]->delete();
        }

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if (in_array($id, $deleted, true)) {
                continue;
            }
            $attributes = Arr::only($row, ['age', 'gender', 'identifier', 'baham_count']);
            if ($id) {
                // Preserve IDs, numbering, legacy weight and all unsubmitted fields.
                $existing[$id]->update($attributes);
            } else {
                if (! collect($attributes)->contains(function ($value) { return filled($value); })) {
                    continue;
                }
                $item->pieces()->create($attributes + ['piece_number' => ++$nextNumber]);
            }
        }
    }
}
