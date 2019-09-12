<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function completedModules()
    {
        return $this->belongsToMany(Module::class, 'user_completed_modules')->withTimestamps();
    }

    public function findByEmail(string $email): User
    {
        return $this->where('email', $email)->firstOrFail();
    }

    public function lastCompletedCourseModule($courseKey)
    {
        return $this->completedModules()
            ->where('course_key', $courseKey)
            ->orderBy('module_number', 'desc')
            ->first();
    }
}
