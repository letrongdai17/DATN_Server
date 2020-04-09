<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class _Class extends Model
{
    protected $table = 'classes';
    protected $fillable = ['name', 'subject_name', 'subject_code', 'class_code'];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_class', 'user_id', 'class_id');
    }

    public function lessons()
    {
        return $this->hasMany('App\Models\Lesson');
    }
}
