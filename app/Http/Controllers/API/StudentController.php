<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\LessonService;
use App\Services\StudentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    protected $studentService;
    protected $lessonService;

    public function __construct(StudentService $studentService, LessonService $lessonService)
    {
        $this->studentService = $studentService;
        $this->lessonService = $lessonService;
    }

    public function index(Request $request)
    {
        $params = $this->getParams($request);
        $students = $this->studentService->getStudentByClassId($params);
        return response()->json($students, 200);
    }

    public function getStudentsRolledUpByLessonId(Request $request)
    {
        $data = $this->studentService->getStudentsRolledUpByLessonId($request->id);
        return response()->json($data, 200);
    }

    public function getStudentsRolledUpByClassId(Request $request)
    {
        $studentsData = $this->studentService->getStudentRolledUpByClassId($request->id);
        return response()->json([
            'students'      =>      $studentsData['students'],
            'lessons_date'  =>      $studentsData['lessons_date'],
        ], 200);
    }

    public function getAllStudentsByLessonId(Request $request)
    {
        $allStudents = $this->studentService->getClassStudentsByLessonId($request->lessonId);
        return response()->json($allStudents, 200);
    }

    public function rollUp(Request $request)
    {
        Log::info($request->server());
        $validator = Validator::make($request->all(), $this->getRules());

         if ($validator->fails()) {
             return response()->json(['error'=>$validator->errors()->first()], 422);
         }


        $data = $this->studentService->rollUp($request->student_code, $request->lesson_id);

        return response()->json($data, 200);
    }

    protected function getRules()
    {
        return [
          'student_code'    =>  'required',
          'lesson_id'       =>   'required',
        ];
    }

    protected function getParams($request)
    {
        return [
            'class_id'       =>      $request->id,
            'per_page'      =>      $request->per_page,
            'current_page'  =>      $request->current_page,
        ];
    }
}
