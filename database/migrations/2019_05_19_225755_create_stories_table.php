<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title');
            $table->string('description');
            $table->enum('status', ['live', 'published']);

            $table->text('cover_image');
            $table->text('published_link');
            $table->boolean('published_fb')->default(false);

            $table->text('friends')->comment('List of Userid that are invited to this story by stashtag, [1,2,4 ...]');

            $table->text('posts')->comment('List of Post id that are added to this Story, [1,2,4 ...]');

            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stories');
    }
}
