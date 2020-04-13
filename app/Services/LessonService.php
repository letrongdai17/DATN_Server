<?php


namespace App\Services;


use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LessonService
{
    protected $lesson;

    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    public function getById($id)
    {
        return $this->lesson->find($id);
    }

    public function getLessonsByClassId($classId)
    {
        return $this->lesson->where('class_id', '=', $classId)->get();
    }

    public function isDuplicateTimeForCreate($startTime, $classId)
    {
        $duplicateTime = $this->lesson
            ->where('class_id', '=', $classId)
            ->where(
                'end_time',
                '>',
                Carbon::parse($startTime)->toDateTimeString()
            )->get();

        return count($duplicateTime->toArray()) != 0;
    }

    public function isDuplicateTimeForUpdate($id, $start_time)
    {
        $duplicateTime = $this->lesson
            ->where('id', '!=', $id)
            ->where('end_time', '>=', Carbon::parse($start_time)->toDateTimeString())
            ->get();

        return count($duplicateTime->toArray()) != 0;
    }

    public function isExistedLesson($lessonId)
    {
        return $this->lesson->find($lessonId);
    }

    public function createLesson($data)
    {
        $newLesson = new Lesson();
        $newLesson->start_time = $data['start_time'];
        $newLesson->end_time = $data['end_time'];
        $newLesson->class_id = $data['class_id'];

        $newLesson->save();
    }

    public function updateLesson($id, $data)
    {
        $updatedLesson = $this->lesson->find($id);

        $updatedLesson->start_time = $data['start_time'];
        $updatedLesson->end_time   = $data['end_time'];

        $updatedLesson->save();
    }
}
