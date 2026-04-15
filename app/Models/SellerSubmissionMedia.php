<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerSubmissionMedia extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function submission()
    {
        return $this->belongsTo(SellerSubmission::class, 'seller_submission_id');
    }
}
