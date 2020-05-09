<?php


namespace App\Services;
use App\Models\_Class;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Models\Student;
use App\Models\Lesson;

class StudentService
{
    protected $student;
    protected $_class;
    protected $lesson;

    public function __construct(Student $student, _Class $_class, Lesson $lesson)
    {
        $this->student = $student;
        $this->_class = $_class;
        $this->lesson = $lesson;
    }

    public function isStudentExist($id)
    {
        return $this->student->find($id);
    }

    public function getById($id)
    {
        return $this->student->find($id);
    }

    public function getByStudentCode($studentCode)
    {
        return $this->student->where('student_code', '=', $studentCode);
    }

    public function rollUp($studentCode, $lessonId)
    {
        $currentLesson = Lesson::find($lessonId);
        $currentStudent = $this->student->where('student_code', '=', $studentCode)->first();

        if (!$currentLesson) {
            return [
                'error'     =>      'Lớp học không tồn tại',
                'code'      =>      404,
            ];
        }

        if (!$currentStudent) {
            return [
                'error'     =>      'Sinh viên không tồn tại',
                'code'      =>      404,
            ];
        }

        if (!$this->isStudentBelongToClass($currentLesson->class->id, $currentStudent->id)) {
            return [
                'error'     =>      'Sinh viên không có trong danh sách lớp này',
                'code'      =>      404,
            ];
        }

        if (!$this->isValidateRangeOfTime($currentLesson->start_time, $currentLesson->end_time)) {
            return [
                'error'     =>      'Hiện tại không phải là khoảng thời gian để điểm danh cho lớp học này',
                'code'      =>      400,
            ];
        }

        if ($currentLesson->students()->where('students.id', $currentStudent->id)->exists()) {
            return [
                'error'     =>      'Sinh viên này đã điểm danh trước đó',
                'code'      =>      400,
            ];
        }

        $currentLesson->students()->attach($currentStudent->id);

        return [
            'message'       =>      'Sinh viên điểm danh thành công',
            'code'          =>      201,
        ];
    }

    public function getStudentByClassId($params)
    {
        $data = Student::where('class_id', '=', $params['class_id'])
            ->paginate($params['per_page'], ['*'], 'current_page', $params['current_page']);
        $students = json_decode(json_encode($data), true);
        return [
            'total'         =>      $students['total'],
            'current_page'  =>      $students['current_page'],
            'data'          =>      $students['data'],
            'per_page'      =>      $students['per_page'],
        ];
    }

    public function getStudentsRolledUpByLessonId($lessonId)
    {
        $lesson = Lesson::find($lessonId);
        $currentClass = $this->_class->find($lesson->class_id);
        $allClassStudents = $currentClass->students;
        $studentRolledUpInLesson = $lesson->students;

        $lessonDetail = [
            'id'            =>      $lesson->id,
            'start_time'    =>      $lesson->start_time,
            'end_time'      =>      $lesson->end_time,
            'subject_name'  =>      $currentClass->subject_name,
        ];

        if (count($allClassStudents) == 0) {
            return [];
        }

        foreach ($allClassStudents as $key=>$student) {
            if (in_array($student->id, $studentRolledUpInLesson->pluck('id')->toArray())) {
                $allClassStudents[$key]['is_rolled_up'] = 1;
            } else {
                $allClassStudents[$key]['is_rolled_up'] = 0;
            }
        }

        return [
            'students'      =>      $allClassStudents,
            'detail'        =>      $lessonDetail,
        ];
    }

    public function getStudentRolledUpByClassId($classId)
    {
        $currentClass = _Class::find($classId);
        $allClassStudents = $currentClass->students;

        foreach ($allClassStudents as $key=>$student) {
            $allClassStudents[$key]->rollUps = [];
        }

        $lessonDate = [];

        foreach ($currentClass->lessons as $lesson) {
            $studentIdsRolledUp = $lesson->students->pluck('id')->toArray();
            array_push($lessonDate, $lesson->start_time);
            foreach ($allClassStudents as $key=>$student) {
                $isRolledUp = in_array($student->id, $studentIdsRolledUp) ? 1 : 0;
                $studentRollUps = $student->rollUps;
                array_push($studentRollUps, $isRolledUp);
                $student->rollUps = $studentRollUps;
                $allClassStudents[$key] = $student;
            }
        }

        return [
            'students'       =>      $allClassStudents,
            'lessons_date'   =>      $lessonDate,
        ];
    }

    public function getClassStudentsByLessonId($lessonId)
    {
        $currentLesson = $this->lesson->find($lessonId);

        if (!$currentLesson) {
            return [
                'error'     =>      'Không tìm thấy lớp học',
                'code'      =>      '404',
            ];
        }

        $currentClass = $currentLesson->class;

        return [
            'data'  =>      $this->getStudentRolledUpByClassId($currentClass->id),
            'code'  =>      200,
        ];
    }

    protected function isValidateRangeOfTime($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $now = Carbon::now();
        return $start->subMinutes(15)->timestamp < $now->timestamp
            && $now->timestamp < $end->addMinutes(15)->timestamp;
    }

    protected function isStudentBelongToClass($classId, $studentId)
    {
        $currentClass = $this->_class->find($classId);
        return $currentClass->students()->where('students.id', $studentId)->exists();
    }
}