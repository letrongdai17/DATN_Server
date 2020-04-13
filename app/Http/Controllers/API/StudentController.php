<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\LessonService;
use App\Services\StudentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    protected $studentService;
    protected $lessonService;

    public function __construct(StudentService $studentService, LessonService $lessonService)
    {
        $this->studentService = $studentService;
        $this->lessonService = $lessonService;
    }

    public function rollUp(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getRules());

         if ($validator->fails()) {
             return response()->json(['error'=>$validator->errors()->first()], 422);
         }

        $student = $this->studentService->getById($request->student_id);
        $lesson = $this->lessonService->getById($request->lesson_id);

        if (!$student) {
            return response()->json(['error'=>'Student is not found!', 404]);
        }

        if (!$lesson) {
            return response()->json(['error'=>'Lesson is not found!', 404]);
        }

         if (!$this->isValidateRangeOfTime($lesson->start_time, $lesson->end_time)) {
             return response()->json(['error'=>'Out of range time'], 400);
         }

        $this->studentService->rollUp($request->student_id, $request->lesson_id);

        return response()->json(['message'=>'Roll up successfully!', 201]);
    }

    protected function isValidateRangeOfTime($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $now = Carbon::now();
        return $start->subMinutes(15)->timestamp < $now->timestamp
            && $now->timestamp < $end->addMinutes(15)->timestamp;
    }

    protected function getRules()
    {
        return [
          'student_id'  =>  'required|exists:student',
          'lesson_id'  =>   'required|exists:lesson',
        ];
    }
}
