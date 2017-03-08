<?php namespace Mavitm\Content\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateMavitmContentPostsCategories extends Migration
{
    public function up()
    {
        Schema::create('mavitm_content_posts_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('post_id')->unsigned();
            $table->integer('category_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('mavitm_content_posts_categories');
    }
}
