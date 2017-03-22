<?php namespace Mavitm\Content\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateMavitmContentPosts extends Migration
{
    public function up()
    {
        Schema::create('mavitm_content_posts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('content_type', 200)->index();
            $table->string('excerpt')->nullable();
            $table->text('content_html')->nullable();
            $table->text('config')->nullable();
            $table->integer('sort_order')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

        });
    }
    
    public function down()
    {
        Schema::dropIfExists('mavitm_content_posts');
    }
}
