<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZoomMeetingAttendeeActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_meeting_attendee_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zoom_meeting_attendee_id');
            $table->foreign('zoom_meeting_attendee_id', 'zm_attendee_activity_fk')
                  ->references('id')
                  ->on('zoom_meeting_attendees');
            $table->bigInteger('score')->nullable();
            $table->longText('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoom_meeting_attendee_activities');
    }
}
