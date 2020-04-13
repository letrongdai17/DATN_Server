<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = ['name', 'student_code', 'class_name', 'birth_day'];

    public function lessons()
    {
        return $this->belongsToMany('App\Models\Lesson', 'lesson_student');
    }

    public function _class()
    {
        return $this->belongsTo('App\Models\_Class');
    }
}
