<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['id', 'name', 'image', 'job_id', 'parent_id', 'slug', 'is_active'];
    // protected $appends = ['trans_name'];

    /**
     * Get all of the categories for the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'department_id')->whereNull('category_id')->select('id', 'name->'.app()->getLocale().' as name', 'category_id', 'department_id', 'image');
    }

    public function getTransNameAttribute()
    {
        // return app()->getLocale();
        // return json_decode($this->name)?->{app()->getLocale()}    ;
    }
}
