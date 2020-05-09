<?php


namespace App\Services;


use App\Models\_Class;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public function getClassByUserId($params)
    {
        $classesObject = DB::table('users')
            ->leftjoin('user_class', 'users.id', '=', 'user_class.user_id')
            ->leftjoin('classes', 'classes.id', '=', 'user_class.class_id')
            ->where('users.id', '=', $params['id'])
            ->paginate($params['per_page'], ['*'], 'current_page', $params['current_page']);

        $classesData = json_decode(json_encode($classesObject, true), true);

        return [
            'data'          =>      $classesData['data'],
            'total'         =>      $classesData['total'],
            'per_page'      =>      $classesData['per_page'],
            'current_page'  =>      $classesData['current_page'],
        ];
    }

    public function createClass($data, $userId)
    {
        $user = $this->user->find($userId);
        $newClass = new _Class();
        $newClass->subject_name = $data['subject_name'];
        $newClass->subject_code = $data['subject_code'];
        $newClass->class_code   = $data['class_code'];

        $user->classes()->save($newClass);
    }

    public function getById($id)
    {
        return $this->_class->findOrFail($id);
    }
}
