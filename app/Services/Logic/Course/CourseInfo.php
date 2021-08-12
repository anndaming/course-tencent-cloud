<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Services\Logic\Course;

use App\Models\Course as CourseModel;
use App\Models\User as UserModel;
use App\Repos\CourseFavorite as CourseFavoriteRepo;
use App\Services\Logic\CourseTrait;
use App\Services\Logic\Service as LogicService;

class CourseInfo extends LogicService
{

    use CourseTrait;

    public function handle($id)
    {
        $course = $this->checkCourse($id);

        $user = $this->getCurrentUser();

        $this->setCourseUser($course, $user);

        return $this->handleCourse($course, $user);
    }

    protected function handleCourse(CourseModel $course, UserModel $user)
    {
        $service = new BasicInfo();

        $result = $service->handleBasicInfo($course);

        $me = [
            'plan_id' => 0,
            'allow_order' => 0,
            'allow_reward' => 0,
            'joined' => 0,
            'owned' => 0,
            'reviewed' => 0,
            'favorited' => 0,
            'progress' => 0,
        ];

        $me['joined'] = $this->joinedCourse ? 1 : 0;
        $me['owned'] = $this->ownedCourse ? 1 : 0;

        $caseOwned = $this->ownedCourse == false;
        $casePrice = $course->market_price > 0;

        /**
         * 过期直播不允许购买
         */
        if ($course->model == CourseModel::MODEL_LIVE) {
            $caseModel = $course->attrs['end_date'] < date('Y-m-d');
        } else {
            $caseModel = true;
        }

        $me['allow_order'] = $caseOwned && $casePrice && $caseModel ? 1 : 0;
        $me['allow_reward'] = $course->market_price == 0 ? 1 : 0;

        if ($user->id > 0) {

            $favoriteRepo = new CourseFavoriteRepo();

            $favorite = $favoriteRepo->findCourseFavorite($course->id, $user->id);

            if ($favorite && $favorite->deleted == 0) {
                $me['favorited'] = 1;
            }

            if ($this->courseUser) {
                $me['reviewed'] = $this->courseUser->reviewed ? 1 : 0;
                $me['progress'] = $this->courseUser->progress ? 1 : 0;
                $me['plan_id'] = $this->courseUser->plan_id;
            }
        }

        $result['me'] = $me;

        return $result;
    }

}
