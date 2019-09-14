<?php

namespace Tests\Feature;

use App\Http\Helpers\CRMHelperInterface;
use App\Http\Helpers\InfusionsoftHelper;
use App\Module;
use App\ReminderTag;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function missing_email_address()
    {
        $response = $this->post('/api/module_reminder_assigner');

        $response->assertStatus(422);
    }

    /** @test */
    public function email_address_is_not_present_in_the_database()
    {
        $response = $this->post('/api/module_reminder_assigner', [
            'contact_email' => str_random(20),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function course_list_is_empty()
    {
        $user = factory(User::class)->create();
        $tagId = $this->reminderTagIdByName('Module reminders completed');
        $this->mockInfusionHelper($user->email, '', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function no_module_is_completed()
    {
        $user = factory(User::class)->create();
        $tagId = $this->reminderTagIdByName('Start IPA Module 1 Reminders');
        $this->mockInfusionHelper($user->email, 'ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function one_module_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());
        $tagId = $this->reminderTagIdByName('Start IPA Module 2 Reminders');
        $this->mockInfusionHelper($user->email, 'ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function gap_between_completed_modules()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());
        $user->completedModules()->attach(Module::where('name', 'IPA Module 5')->first());
        $tagId = $this->reminderTagIdByName('Start IPA Module 6 Reminders');
        $this->mockInfusionHelper($user->email, 'ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function last_module_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(7)->get());
        $tagId = $this->reminderTagIdByName('Module reminders completed');
        $this->mockInfusionHelper($user->email, 'ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);

    }

    /** @test */
    public function modules_from_different_courses_are_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->limit(1)->get());
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());
        $tagId = $this->reminderTagIdByName('Start IEA Module 2 Reminders');
        $this->mockInfusionHelper($user->email, 'iea', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function one_course_out_of_two_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->get());
        $tagId = $this->reminderTagIdByName('Start IPA Module 1 Reminders');
        $this->mockInfusionHelper($user->email, 'iea,ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function one_course_is_completed_and_one_module_from_the_second_course_is_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->get());
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->limit(1)->get());

        $tagId = $this->reminderTagIdByName('Start IPA Module 2 Reminders');
        $this->mockInfusionHelper($user->email, 'iea,ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    /** @test */
    public function two_courses_are_completed()
    {
        $user = factory(User::class)->create();
        $user->completedModules()->attach(Module::where('course_key', 'iea')->get());
        $user->completedModules()->attach(Module::where('course_key', 'ipa')->get());

        $tagId = $this->reminderTagIdByName('Module reminders completed');
        $this->mockInfusionHelper($user->email, 'iea,ipa', $tagId);

        $this->getResponseAndAssertSuccessWithTagId($user->email, $tagId);
    }

    protected function getResponseAndAssertSuccessWithTagId($email, $tagId)
    {
        $response = $this->post('/api/module_reminder_assigner', [
            'contact_email' => $email,
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'success' => true,
            'message' => "Tag $tagId added successfully",
        ]);
    }

    protected function reminderTagIdByName($name)
    {
        return ReminderTag::whereName($name)->first()->id;
    }

    protected function mockInfusionHelper($email, $products, $tagId)
    {
        $id = random_int(1, 1000);
        $mock = \Mockery::mock(InfusionsoftHelper::class);
        $mock->shouldReceive('getContact')
            ->once()
            ->with($email)
            ->andReturn([
                'Id' => $id,
                '_Products' => $products,
            ]);
        $mock->shouldReceive('addTag')
            ->once()
            ->with($id, $tagId)
            ->andReturn(true);

        $this->app->instance(CRMHelperInterface::class, $mock);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->seed('iPSDevTestSeeder');
    }

}
