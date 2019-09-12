<?php

use App\Module;
use App\ReminderTag;
use Illuminate\Database\Seeder;

class iPSDevTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedModules();
        $this->seedReminderTags();
        $this->associateReminderTagsToModules();
    }

    protected function seedModules()
    {
        $courses = ['ipa', 'iea', 'iaa'];
        $modules = range(1, 7);

        foreach ($courses as $courseKey) {
            foreach ($modules as $moduleNumber) {
                Module::create([
                    'course_key' => $courseKey,
                    'module_number' => $moduleNumber,
                    'name' => strtoupper($courseKey) . ' Module ' . $moduleNumber,
                ]);
            }
        }
    }

    protected function seedReminderTags()
    {
        foreach ($this->getInfusionTags() as $tagData) {
            $tagData['name'] = $tagData['name']
                ?? $this->makeReminderName($tagData['course_key'], $tagData['module_number']);
            ReminderTag::create($tagData);
        }
    }

    protected function getInfusionTags()
    {
        return [
            ['id' => 110, 'course_key' => 'ipa', 'module_number' => 1],
            ['id' => 112, 'course_key' => 'ipa', 'module_number' => 2],
            ['id' => 114, 'course_key' => 'ipa', 'module_number' => 3],
            ['id' => 116, 'course_key' => 'ipa', 'module_number' => 4],
            ['id' => 118, 'course_key' => 'ipa', 'module_number' => 5],
            ['id' => 120, 'course_key' => 'ipa', 'module_number' => 6],
            ['id' => 122, 'course_key' => 'ipa', 'module_number' => 7],

            ['id' => 124, 'course_key' => 'iea', 'module_number' => 1],
            ['id' => 126, 'course_key' => 'iea', 'module_number' => 2],
            ['id' => 128, 'course_key' => 'iea', 'module_number' => 3],
            ['id' => 130, 'course_key' => 'iea', 'module_number' => 4],
            ['id' => 132, 'course_key' => 'iea', 'module_number' => 5],
            ['id' => 134, 'course_key' => 'iea', 'module_number' => 6],
            ['id' => 136, 'course_key' => 'iea', 'module_number' => 7],

            ['id' => 138, 'course_key' => 'iaa', 'module_number' => 1],
            ['id' => 140, 'course_key' => 'iaa', 'module_number' => 2],
            ['id' => 142, 'course_key' => 'iaa', 'module_number' => 3],
            ['id' => 144, 'course_key' => 'iaa', 'module_number' => 4],
            ['id' => 146, 'course_key' => 'iaa', 'module_number' => 5],
            ['id' => 148, 'course_key' => 'iaa', 'module_number' => 6],
            ['id' => 150, 'course_key' => 'iaa', 'module_number' => 7],

            [
                'id' => 154,
                'course_key' => null,
                'module_number' => null,
                'name' => 'Module reminders completed',
            ],
        ];
    }

    protected function makeReminderName($courseKey, $moduleNumber)
    {
        $courseKey = strtoupper($courseKey);

        return "Start $courseKey Module $moduleNumber Reminders";
    }

    protected function associateReminderTagsToModules()
    {
        Module::each(function ($module) {
            $reminderTagId = ReminderTag::where('name', 'like', '%' . $module->name . '%')
                    ->first()->id ?? null;
            
            $module->update([
                'reminder_tag_id' => $reminderTagId,
            ]);
        });
    }
}
