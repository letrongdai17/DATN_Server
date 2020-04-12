<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ClassesService;
use App\Services\LessonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        if (!$this->isBelongToCurrentUser($users)) {
            return response()->json(['error'=>'Permission denied'], 403);
        }

        $lessons = $this->lessonService->getLessonsByClassId($request->classId);

        return response()->json(['data'=>$lessons], 200);
    }

    public function createLesson(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->first()], 422);
        }

        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        $now = Carbon::now();

        if ($startTime->timestamp > $endTime->timestamp) {
            return response()->json('The end time must be later than start time');
        }

        if ($startTime->subMinutes(15)->timestamp > $now->timestamp
                || $endTime->addMinutes(15)->timestamp < $now->timestamp) {
            return response()->json(['error'=>'Out of range time'], 400);
        }

        if ($this->lessonService->isDuplicateTimeForCreate($request->start_time, $request->class_id)) {
            return response()->json(['error'=>'Duplicate time'], 400);
        }

        $this->lessonService->createLesson($request->all());

        return response()->json(['message'=>'Create lesson successfully'], 201);
    }

    public function updateLesson(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getUpdateRules());

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->first()], 422);
        }

        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        $now = Carbon::now();

        if ($startTime->timestamp > $endTime->timestamp) {
            return response()->json(['error'=>'The end time must be later than start time'], 400);
        }

        if ($startTime->subMinutes(15)->timestamp > $now->timestamp
            || $endTime->addMinutes(15)->timestamp < $now->timestamp) {
            return response()->json(['error'=>'Out of range time'], 400);
        }

        if (!$this->lessonService->isExistedLesson($request->id)) {
            return response()->json(['error'=>'Lesson is not existed'], 404);
        }

        if ($this->lessonService->isDuplicateTimeForUpdate($request->id, $request->start_time)) {
            return response()->json(['error'=>'Duplicate time'], 400);
        }

        $this->lessonService->updateLesson($request->id, $request->only(['start_time', 'end_time']));

        return response()->json(['Update lesson successfully'], 200);
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

    protected function rules()
    {
        return [
            "class_id"      =>      "required",
            "start_time"    =>      "required",
            "end_time"      =>      "required",
        ];
    }

    protected function getUpdateRules()
    {
        return [
            "id"            =>      "required",
            "start_time"    =>      "required",
            "end_time"      =>      "required",
        ];
    }
}
