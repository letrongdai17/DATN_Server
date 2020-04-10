<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClassesService;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    protected $lessonService;
    protected $classService;

    public function __construct(LessonService $lessonService, ClassesService $classService)
    {
        $this->lessonService = $lessonService;
        $this->classService  = $classService;
    }

    public function index(Request $request)
    {
        $_class = $this->classService->getById($request->classId);

        if (!$_class) {
            return response()->json(['error'=>'Class not found'], 404);
        }

        $users = $_class->users->toArray();
        Log::info('2222222'.print_r($_class->toArray(), true));
        if (!$this->isBelongToCurrentUser($users)) {
            return response()->json(['error'=>'Permission denied'], 403);
        }

        $lessons = $this->lessonService->getLessonsByClassId($request->classId);

        return response()->json(['data'=>$lessons], 200);
    }

    protected function isBelongToCurrentUser($users)
    {
        $check = false;
        if (!$users || count($users) == 0) {
            return false;
        }

        foreach ($users as $user) {
            if ($user['id'] == Auth::id()) {
                $check = true;
                break;
            }
        }

        return $check;
    }
}
