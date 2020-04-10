<?php


namespace App\Services;


use App\Models\Lesson;

class LessonService
{
    protected $lesson;

    public function __construct(Lesson $lesson)
    {
        $this->lesson = $lesson;
    }

    public function getLessonsByClassId($classId)
    {
        return $this->lesson->where('class_id', '=', $classId)->get();
    }
}
