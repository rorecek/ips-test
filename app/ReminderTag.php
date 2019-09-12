<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReminderTag extends Model
{
    protected $guarded = [];

    public static function getRemindersCompletedTagId()
    {
        return self::whereNull('course_key')->whereNull('module_number')->firstOrFail();
    }
}
