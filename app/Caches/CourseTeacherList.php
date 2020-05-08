<?php

namespace App\Caches;

use App\Models\User as UserModel;
use App\Repos\Course as CourseRepo;

class CourseTeacherList extends Cache
{

    protected $lifetime = 1 * 86400;

    public function getLifetime()
    {
        return $this->lifetime;
    }

    public function getKey($id = null)
    {
        return "course_teacher_list:{$id}";
    }

    public function getContent($id = null)
    {
        $courseRepo = new CourseRepo();

        $users = $courseRepo->findTeachers($id);

        if ($users->count() == 0) {
            return [];
        }

        return $this->handleContent($users);
    }

    /**
     * @param UserModel[] $users
     * @return array
     */
    public function handleContent($users)
    {
        $result = [];

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'title' => $user->title,
            ];
        }

        return $result;
    }

}
