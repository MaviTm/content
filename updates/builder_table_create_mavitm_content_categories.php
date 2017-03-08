<?php namespace Mavitm\Content\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateMavitmContentCategories extends Migration
{
    public function up()
    {
        Schema::create('mavitm_content_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->string('slug', 255)->index();
            $table->text('description')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->text('conf')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('mavitm_content_categories');
    }
}
