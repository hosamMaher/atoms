<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model {
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'mobile', 'photo', 'category_id', 'subcategory_id', 'status',
        'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'reject_reason'
    ];

    protected $hidden = [
        'password',
    ];

    protected $dates = ['deleted_at', 'approved_at', 'rejected_at'];
}

