<?php

namespace Tests\Unit;

use App\Actions\CalculateReminderTagIdAction;
use App\Module;
use App\ReminderTag;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculateReminderTagIdActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        $this->seed('iPSDevTestSeeder');
    }

    protected function reminderTagIdByName($name)
    {
        return ReminderTag::whereName($name)->first()->id;
    }

    /** @test */
    public function email_address_is_not_present_in_the_database()
    {
        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->expectException(ModelNotFoundException::class);

        $calculateTagId->execute(str_random(10), '');
    }

    /** @test */
    public function course_list_is_empty()
    {
        $user = factory(User::class)->create();

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Module reminders completed'),
            $calculateTagId->execute($user->email, '')
        );
    }

    /** @test */
    public function no_module_is_completed()
    {
        $user = factory(User::class)->create();

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Start IPA Module 1 Reminders'),
            $calculateTagId->execute($user->email, 'ipa')
        );
    }

    /** @test */
    public function one_module_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Start IPA Module 2 Reminders'),
            $calculateTagId->execute($user->email, 'ipa')
        );
    }

    /** @test */
    public function gap_between_completed_modules()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completedModules()->attach(Module::where('name', 'IPA Module 5')->first());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Start IPA Module 6 Reminders'),
            $calculateTagId->execute($user->email, 'ipa')
        );
    }

    /** @test */
    public function last_module_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(7)->get());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Module reminders completed'),
            $calculateTagId->execute($user->email, 'ipa')
        );
    }

    /** @test */
    public function modules_from_different_courses_are_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->limit(1)->get());
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Start IEA Module 2 Reminders'),
            $calculateTagId->execute($user->email, 'iea,ipa')
        );
    }

    /** @test */
    public function one_course_out_of_two_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->get());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Start IPA Module 1 Reminders'),
            $calculateTagId->execute($user->email, 'iea,ipa')
        );
    }

    /** @test */
    public function one_course_is_completed_and_one_module_from_the_second_course_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->get());
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Start IPA Module 2 Reminders'),
            $calculateTagId->execute($user->email, 'iea,ipa')
        );
    }

    /** @test */
    public function two_courses_are_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->get());
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->get());

        $calculateTagId = app()->make(CalculateReminderTagIdAction::class);

        $this->assertEquals(
            $this->reminderTagIdByName('Module reminders completed'),
            $calculateTagId->execute($user->email, 'iea,ipa')
        );
    }
}
