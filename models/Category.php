<?php namespace Mavitm\Content\Models;

use Model;

/**
 * Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'mavitm_content_categories';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $rules = [
        'name' => 'required',
        'slug' => 'required|between:3,64|unique:mavitm_content_categories',
    ];

    public $translatable = [
        'name',
        'description',
        ['slug', 'index' => true]
    ];

    protected $guarded = [];

    public $belongsToMany = [
        'posts' => ['Mavitm\Content\Models\Post',
            'table' => 'mavitm_content_posts_categories',
            'order' => 'created_at desc',
            'scope' => 'isPublished'
        ]
    ];

    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order']
    ];

    public $attachOne = [
        'image' => ['System\Models\File', 'order' => 'sort_order']
    ];

    public function afterDelete()
    {
        $this->posts()->detach();
    }


    /*******************************************************************************************************************
     * GET ATTRIBUTE
     */


    public function getPostCountAttribute()
    {
        return $this->posts()->count();
    }


    /*******************************************************************************************************************
     * SET ATTRIBUTE
     */

    public function setUrl($pageName, $controller)
    {
        $params = [
            'id' => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }


}