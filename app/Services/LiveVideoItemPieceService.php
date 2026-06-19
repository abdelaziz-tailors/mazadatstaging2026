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
                'piece_multiplier_number' => $piece['piece_multiplier_number'] ?? $item->piece_multiplier_number,
                'identifier' => $piece['identifier'] ?? null,
                'baham_count' => $piece['baham_count'] ?? null,
            ]);
        }

        $count = count($normalized);
        if ($count > 0 && (int) $item->quantity !== $count) {
            $item->update(['quantity' => $count]);
        }
    }

    public static function buildSinglePiecePayload(array $attributes): array
    {
        return [[
            'age' => $attributes['age'] ?? null,
            'weight' => $attributes['weight'] ?? null,
            'identifier' => $attributes['identifier'] ?? null,
            'piece_multiplier_number' => $attributes['piece_multiplier_number'] ?? null,
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
        $fields = ['age', 'weight', 'piece_multiplier_number', 'identifier', 'baham_count'];

        foreach ($fields as $field) {
            if (filled($piece[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
