<?php


namespace App\Services;


use App\Models\Classes;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ClassesService
{
    protected $_class;
    protected $user;

    public function __construct(Classes $_class, User $user)
    {
        $this->_class = $_class;
        $this->user = $user;
    }

    public function getClassByUserId($userId)
    {
        $user = $this->user->find($userId);

        return $user->classes;
    }

    public function getClassDetailByClassId($classId)
    {
        $classDetail = $this->_class->find($classId);

        return $classDetail->detailClasses;
    }
}
