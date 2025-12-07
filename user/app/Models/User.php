<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable {
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'is_active'
    ];

    protected $hidden = [
        'password',
    ];

    protected $dates = ['deleted_at'];

    public function setPasswordAttribute($value) {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function categoryAssignments() {
        return $this->hasMany(UserCategoryAssignment::class);
    }
}
