<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategoryAssignment extends Model {
    protected $fillable = ['user_id', 'category_id', 'subcategory_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

