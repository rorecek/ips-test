<?php

namespace App\Actions;

use App\Module;
use App\ReminderTag;
use App\User;

class CalculateReminderTagIdAction
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Module
     */
    private $module;

    public function __construct(User $user, Module $module)
    {
        $this->user = $user;
        $this->module = $module;
    }

    public function execute(string $email, string $courses): int
    {
        $courseKeys = array_intersect(
            explode(',', trim($courses)),
            ['ipa', 'iea', 'iaa']
        );

        $user = $this->user->findByEmail($email);

        foreach ($courseKeys as $courseKey) {
            $courseKey = strtolower($courseKey);

            $lastCompletedModule = $user->lastCompletedCourseModule($courseKey);

            // Are there no completed modules for this course?
            if ($lastCompletedModule === null) {
                return $this->module->firstModule($courseKey)->reminder_tag_id;
            }

            // Is there a next module in this course?
            $nextModule = $lastCompletedModule->nextModule();
            if ($nextModule) {
                return $nextModule->reminder_tag_id;
            }
        }

        return ReminderTag::getRemindersCompletedTagId()->id ?? null;
    }
}
