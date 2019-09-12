<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $guarded = [];

    public function firstModule($courseKey)
    {
        return static::where('course_key', $courseKey)
            ->orderBy('module_number')
            ->first();
    }

    public function nextModule()
    {
        return static::where('course_key', $this->course_key)
            ->where('module_number', $this->module_number + 1)
            ->first();
    }
}
