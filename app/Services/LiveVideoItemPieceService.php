<?php

namespace App\Services;

use App\Models\LiveVideoItem;
use App\Models\LiveVideoItemPiece;

class LiveVideoItemPieceService
{
    public static function syncPieces(LiveVideoItem $item, array $pieces): void
    {
        $item->pieces()->delete();

        $normalized = self::normalizePieces($pieces);

        foreach ($normalized as $index => $piece) {
            $item->pieces()->create([
                'piece_number' => $index + 1,
                'age' => $piece['age'] ?? null,
                'weight' => $piece['weight'] ?? null,
                // 'piece_multiplier_number' => $piece['piece_multiplier_number'] ?? $item->piece_multiplier_number,
                'identifier' => $piece['identifier'] ?? null,
                'baham_count' => $piece['baham_count'] ?? null,
            ]);
        }

        $count = count($normalized);
        if ($count > 0 && (int) $item->quantity !== $count) {
            $item->update(['quantity' => $count]);
        }
    }

    /**
     * Add a single piece to an item, independent of the others — used by
     * the mobile "add one piece" endpoint (as opposed to syncPieces(), which
     * replaces the whole set). quantity is recomputed from the real piece
     * count afterward.
     */
    public static function addPiece(LiveVideoItem $item, array $attributes): LiveVideoItemPiece
    {
        $nextPieceNumber = ((int) $item->pieces()->max('piece_number')) + 1;

        $piece = $item->pieces()->create([
            'piece_number' => $nextPieceNumber,
            'age' => $attributes['age'] ?? null,
            'weight' => $attributes['weight'] ?? null,
            'identifier' => $attributes['identifier'] ?? null,
            'baham_count' => $attributes['baham_count'] ?? null,
        ]);

        self::syncQuantityToPieceCount($item);

        return $piece;
    }

    /**
     * Update a single piece's own fields — only the keys actually present
     * in $attributes are changed (partial update), so omitted fields are
     * left untouched. piece_number/live_video_item_id are never touched
     * here since this doesn't renumber or move the piece.
     */
    public static function updatePiece(LiveVideoItemPiece $piece, array $attributes): LiveVideoItemPiece
    {
        if (! empty($attributes)) {
            $piece->update($attributes);
        }

        return $piece;
    }

    /**
     * Delete a single piece and recompute the parent item's quantity from
     * the real remaining piece count (which can drop to 0).
     */
    public static function deletePiece(LiveVideoItemPiece $piece): void
    {
        $item = $piece->item;
        $piece->delete();

        if ($item) {
            self::syncQuantityToPieceCount($item);
        }
    }

    private static function syncQuantityToPieceCount(LiveVideoItem $item): void
    {
        $count = $item->pieces()->count();
        if ((int) $item->quantity !== $count) {
            $item->update(['quantity' => $count]);
        }
    }

    public static function buildSinglePiecePayload(array $attributes): array
    {
        return [[
            'age' => $attributes['age'] ?? null,
            'weight' => $attributes['weight'] ?? null,
            'identifier' => $attributes['identifier'] ?? null,
            // 'piece_multiplier_number' => $attributes['piece_multiplier_number'] ?? null,
            'baham_count' => $attributes['baham_count'] ?? null,
        ]];
    }

    public static function normalizePieces(array $pieces): array
    {
        $normalized = [];

        foreach ($pieces as $piece) {
            if (! is_array($piece)) {
                continue;
            }

            if (self::pieceIsEmpty($piece)) {
                continue;
            }

            $normalized[] = $piece;
        }

        return $normalized;
    }

    public static function pieceIsEmpty(array $piece): bool
    {
        $fields = ['age', 'weight', 'identifier', 'baham_count'];

        foreach ($fields as $field) {
            if (filled($piece[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
