<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'branch_name',
        'iban',
        'swift_code',
        'bank_account_number',
        'bank_account_name',
        'logo',
        'phone',
        'whatsapp',
        'facebook',
        'instagram',
        'tiktok',
    ];
}
