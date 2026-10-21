<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForumPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index(); 
            $table->longText('title');
            $table->foreignId('course_id')->nullable()->constrained('courses', 'id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
            $table->string('topic_category')->nullable()->index();
            $table->longText('content');
            $table->string('status')->comment('APPROVED, DECLINED, PENDING, REMOVED')->default('PENDING');
            $table->boolean('is_pinned')->default(0)->index();
            $table->foreignId('reviewer_id')->nullable()->constrained('users'); 
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
        Schema::dropIfExists('forum_posts');
    }
}
