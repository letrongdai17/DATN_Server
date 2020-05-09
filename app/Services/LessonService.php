<?php


namespace App\Services;


use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function getLessonsByClassId($params)
    {
        $lessonsObject = DB::table('lessons')
            ->where('class_id', '=', $params['classId'])
            ->orderBy('created_at', 'DESC')
            ->paginate($params['per_page'], ['*'], 'current_page', $params['current_page']);
        $lessonsData = json_decode(json_encode($lessonsObject), true);

        return [
            'data'              =>      $lessonsData['data'],
            'current_page'      =>      $lessonsData['current_page'],
            'per_page'          =>      $lessonsData['per_page'],
            'total'             =>      $lessonsData['total'],
        ];
    }

    public function getClassByLessonId($lessonId)
    {
        if (!$lessonId) {
            return [
                'error'   =>  'Id tiết học là bắt buộc.',
                'code'    =>      422,
            ];
        }
        $lesson = $this->lesson->find($lessonId);

        if (!$lesson) {
            return [
                'error'     =>      'Không tìm thấy tiết học.',
                'code'      =>      404,
            ];
        }

        if (!$lesson->class) {
            return [
                'error'     =>      'Không tìm thấy lớp học tương ứng.',
                'code'      =>      404,
            ];
        }

        return [
            'data'      =>      $lesson->class,
            'code'      =>      200,
        ];
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

    public function deleteLesson($id)
    {
        $currentLesson = $this->lesson->find($id);

        if (!$currentLesson) {
            return [
              'error'   =>      'Không tìm thấy lớp học này',
              'code'    =>      404,
            ];
        }

        if ($currentLesson->is_confirmed) {
            return [
                'error'   =>      'Lớp học này đã kết thúc',
                'code'    =>      403,
            ];
        }

        $currentLesson->delete();
        return [
            'message'   =>      'Xóa lớp học thành công',
            'code'      =>      200,
        ];
    }

    public function confirmLesson($id)
    {
        $currentLesson = $this->lesson->find($id);

        if (!$currentLesson) {
            return [
                'error'   =>      'Không tìm thấy lớp học này',
                'code'    =>      404,
            ];
        }

        if ($currentLesson->is_confirmed) {
            return [
                'error'   =>      'Lớp học này đã đã được xác nhận trước đó',
                'code'    =>      403,
            ];
        }

        $currentLesson->is_confirmed   = 1;
        $currentLesson->save();

        return [
            'message'   =>      'Cập nhật lớp học thành công',
            'code'      =>      200,
        ];
    }
}
