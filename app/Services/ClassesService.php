<?php


namespace App\Services;


use App\Models\_Class;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ClassesService
{
    protected $_class;
    protected $user;

    public function __construct(_Class $_class, User $user)
    {
        $this->_class = $_class;
        $this->user = $user;
    }

    public function getClassByUserId($userId)
    {
        $user = $this->user->find($userId);

        return $user->classes;
    }

    public function createClass($data)
    {
        $newClass = new _Class();
        $newClass->subject_name = $data['subject_name'];
        $newClass->subject_code = $data['subject_code'];
        $newClass->class_code   = $data['class_code'];

        $newClass->save();
    }
}
