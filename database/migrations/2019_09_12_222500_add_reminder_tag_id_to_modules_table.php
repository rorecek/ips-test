<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReminderTagIdToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedInteger('reminder_tag_id')->nullable()->after('name');
            $table->foreign('reminder_tag_id')->references('id')->on('reminder_tags')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['reminder_tag_id']);
            $table->dropColumn('reminder_tag_id');
        });
    }
}
