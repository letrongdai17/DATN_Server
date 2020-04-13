<?php


namespace App\Services;
use Illuminate\Support\Facades\Log;

use App\Models\Student;
use App\Models\Lesson;

class StudentService
{
    protected $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    public function isStudentExist($id)
    {
        return $this->student->find($id);
    }

    public function getById($id)
    {
        return $this->student->find($id);
    }

    public function rollUp($studentId, $lessonId)
    {
        $currentStudent = $this->student->find($studentId);
        $currentStudent->lessons()->attach($lessonId, ['isRolledUp'=>1]);
    }
}