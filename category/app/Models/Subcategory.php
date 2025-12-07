<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model {
    use SoftDeletes;

    protected $fillable = ['name', 'category_id', 'is_active'];

    protected $dates = ['deleted_at'];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}

