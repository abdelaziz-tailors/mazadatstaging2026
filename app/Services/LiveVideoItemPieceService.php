<?php

namespace App\Services;

use App\Models\LiveVideoItem;

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
                'piece_multiplier_number' => $piece['piece_multiplier_number'] ?? null,
                'identifier' => $piece['identifier'] ?? null,
                'baham_count' => $piece['baham_count'] ?? null,
            ]);
        }

        if (! empty($normalized)) {
            $first = $normalized[0];
            $item->update([
                'quantity' => count($normalized),
                'age' => $first['age'] ?? $item->age,
                'weight' => $first['weight'] ?? $item->weight,
                'piece_multiplier_number' => $first['piece_multiplier_number'] ?? $item->piece_multiplier_number,
                'identifier' => $first['identifier'] ?? $item->identifier,
                'baham_count' => $first['baham_count'] ?? $item->baham_count,
            ]);
        }
    }

    public static function syncSinglePieceFromItem(LiveVideoItem $item): void
    {
        if ($item->pieces()->exists()) {
            return;
        }

        if ((int) $item->quantity > 1) {
            return;
        }

        $item->pieces()->create([
            'piece_number' => 1,
            'age' => $item->age,
            'weight' => $item->weight,
            'piece_multiplier_number' => $item->piece_multiplier_number,
            'identifier' => $item->identifier,
            'baham_count' => $item->baham_count,
        ]);
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
        $fields = ['age', 'weight', 'piece_multiplier_number', 'identifier', 'baham_count'];

        foreach ($fields as $field) {
            if (filled($piece[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
