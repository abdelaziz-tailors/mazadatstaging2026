<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class MobileNumber
{
    public static function normalize($phone)
    {
        if (! is_string($phone)) {
            return $phone;
        }
        $digits = preg_replace('/[\s()-]/', '', trim($phone));
        if (preg_match('/^(?:\+966|00966|966|0)?(5[0-9]{8})$/D', $digits, $matches)) {
            return '+966'.$matches[1];
        }
        // Existing Egyptian local numbers remain usable without guessing a Saudi prefix.
        if (preg_match('/^01[0-9]{9}$/D', $digits)) {
            return '+20'.substr($digits, 1);
        }
        if (preg_match('/^(?:\+|00)?([1-9][0-9]{7,14})$/D', $digits, $matches)) {
            return '+'.$matches[1];
        }
        return trim($phone);
    }

    public static function valid($phone): bool
    {
        $phone = self::normalize($phone);
        if (! is_string($phone) || ! preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone)) {
            return false;
        }
        return strpos($phone, '+966') !== 0 || preg_match('/^\+9665[0-9]{8}$/D', $phone) === 1;
    }

    /** Same expression for legacy lookups, duplicate audits and generated unique keys. */
    public static function sqlKey(string $column = 'phone', string $driver = 'mysql'): string
    {
        $value = 'TRIM(`'.$column.'`)';
        foreach ([' ', '(', ')', '-'] as $character) {
            $value = "REPLACE($value, '".$character."', '')";
        }
        foreach ([9, 10, 11, 12, 13] as $code) {
            $value = "REPLACE($value, CHAR($code), '')";
        }
        $concat = function ($prefix, $suffix) use ($driver) {
            return $driver === 'sqlite' ? "('$prefix' || $suffix)" : "CONCAT('$prefix', $suffix)";
        };
        return "CASE WHEN $value IS NULL OR $value = '' THEN NULL "
            ."WHEN $value LIKE '+%' THEN $value "
            ."WHEN $value LIKE '00%' THEN ".$concat('+', "SUBSTR($value, 3)").' '
            ."WHEN LENGTH($value) = 9 AND $value LIKE '5%' THEN ".$concat('+966', $value).' '
            ."WHEN LENGTH($value) = 10 AND $value LIKE '05%' THEN ".$concat('+966', "SUBSTR($value, 2)").' '
            ."WHEN LENGTH($value) = 11 AND $value LIKE '01%' THEN ".$concat('+20', "SUBSTR($value, 2)").' '
            ."WHEN $value NOT LIKE '0%' THEN ".$concat('+', $value)." ELSE $value END";
    }

    public static function matching($query, string $phone)
    {
        return $query->whereRaw(self::sqlKey('phone', $query->getConnection()->getDriverName()).' = ?', [self::normalize($phone)]);
    }

    public static function find(string $model, string $phone, bool $lock = false)
    {
        $query = $model::query();
        if ($lock) {
            $query->lockForUpdate();
        }
        $exact = (clone $query)->where('phone', $phone)->first();
        if ($exact) {
            return $exact;
        }
        $matches = self::matching($query, $phone)->limit(2)->get();
        return $matches->count() === 1 ? $matches->first() : null;
    }

    public static function rules($ignore = null, string $table = 'users'): array
    {
        return ['bail', 'required', 'string',
            function ($attribute, $value, $fail) {
                if (! self::valid($value)) {
                    $fail(TranslationHelper::translate('please_enter_valid_phone_with_country_code'));
                }
            },
            function ($attribute, $value, $fail) use ($ignore, $table) {
                $query = self::matching(DB::table($table), $value);
                if ($ignore !== null) {
                    $query->where('id', '<>', $ignore);
                }
                if ($query->exists()) {
                    $fail(TranslationHelper::translate('phone_number_exists'));
                }
            }];
    }

    public static function emailRules($ignore = null, string $table = 'users'): array
    {
        return ['nullable', 'email', 'max:255',
            function ($attribute, $value, $fail) use ($ignore, $table) {
                $query = DB::table($table)->whereNull('deleted_at')
                    ->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower(trim($value), 'UTF-8')]);
                if ($ignore !== null) {
                    $query->where('id', '<>', $ignore);
                }
                if ($query->exists()) {
                    $fail(TranslationHelper::translate('email_address_exists'));
                }
            }];
    }

    public static function prepare($request): void
    {
        $email = $request->input('email');
        $request->merge([
            'phone' => self::normalize($request->input('phone')),
            'email' => is_string($email) ? (trim($email) === '' ? null : trim($email)) : $email,
        ]);
    }
}
