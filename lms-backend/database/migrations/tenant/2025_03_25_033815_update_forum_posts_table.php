<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForumPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('forum_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_posts', 'topic_category')) {
                $table->string('topic_category')->nullable()->index();
            }
            if (!Schema::hasColumn('forum_posts', 'status')) {
                $table->string('status')->comment('APPROVED, DECLINED, PENDING, REMOVED')->default('PENDING');
            }
            if (!Schema::hasColumn('forum_posts', 'is_pinned')) {
                $table->boolean('is_pinned')->default(0)->index();
            }
            if (!Schema::hasColumn('forum_posts', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('forum_posts', function (Blueprint $table) {
            if (Schema::hasColumn('forum_posts', 'reviewer_id')) {
                $table->dropForeign(['reviewer_id']); // Drop foreign key constraint
                $table->dropColumn('reviewer_id');
            }
            if (Schema::hasColumn('forum_posts', 'topic_category')) {
                $table->dropColumn('topic_category');
            }
            if (Schema::hasColumn('forum_posts', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('forum_posts', 'is_pinned')) {
                $table->dropColumn('is_pinned');
            }
        });
    }
}
