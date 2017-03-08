<?php namespace Mavitm\Content\Models;

use Db;
use Url;
use App;
use Str;
use Html;
use Lang;
use Model;
use Carbon\Carbon;
use Cms\Classes\Theme;
use Backend\Models\User;
use Cms\Classes\Page as CmsPage;

/**
 * Model
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'mavitm_content_posts';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $rules = [
        'title' => 'required',
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:mavitm_content_posts'],
        'excerpt' => ''
    ];

    public $translatable = [
        'title',
        'content_html',
        'excerpt',
        ['slug', 'index' => true]
    ];

    protected $jsonable = ['config'];

    public static $allowedSortingOptions = array(
        'title asc' => 'Title (ascending)',
        'title desc' => 'Title (descending)',
        'created_at asc' => 'Created (ascending)',
        'created_at desc' => 'Created (descending)',
        'updated_at asc' => 'Updated (ascending)',
        'updated_at desc' => 'Updated (descending)',
        'published_at asc' => 'Published (ascending)',
        'published_at desc' => 'Published (descending)',
        'random' => 'Random'
    );

    public $belongsTo = [
        'user' => ['Backend\Models\User']
    ];

    public $belongsToMany = [
        'categories' => [
            'Mavitm\Content\Models\Category',
            'table' => 'mavitm_content_posts_categories',
            'order' => 'name'
        ]
    ];

    public $attachMany = [
        'gallery_images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $attachOne = [
        'header_image' => ['System\Models\File'],
        'list_image' => ['System\Models\File'],
    ];


    /*******************************************************************************************************************
     * SCOPE
     */

    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<', Carbon::now())
            ;
    }

    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'       => 1,
            'perPage'    => 30,
            'sort'       => 'created_at',
            'categories' => null,
            'category'   => null,
            'search'     => '',
            'published'  => true,
            'exceptPost' => null,
        ], $options));

        $searchableFields = ['title', 'slug', 'excerpt', 'content_html'];

        if ($published) {
            $query->isPublished();
        }

        /*
         * Ignore a post
         */
        if ($exceptPost) {
            if (is_numeric($exceptPost)) {
                $query->where('id', '<>', $exceptPost);
            }
            else {
                $query->where('slug', '<>', $exceptPost);
            }
        }

        /*
         * Sorting
         */
        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {

            if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }
                list($sortField, $sortDirection) = $parts;
                if ($sortField == 'random') {
                    $sortField = Db::raw('RAND()');
                }
                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, $searchableFields);
        }

        /*
         * Categories
         */
        if ($categories !== null) {
            if (!is_array($categories)) $categories = [$categories];
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        /*
         * Category, including children
         */
        if ($category !== null) {
            $category = Category::find($category);

            $categories = $category->getAllChildrenAndSelf()->lists('id');
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        return $query->paginate($perPage, $page);
    }


    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
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

        if (array_key_exists('categories', $this->getRelations())) {
            $params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    /*******************************************************************************************************************
     * GET ATTRIBUTE
     */

    public function getHasSummaryAttribute()
    {
        $more = '<!-- more -->';

        return (
            !!strlen(trim($this->excerpt)) ||
            strpos($this->content_html, $more) !== false ||
            strlen(Html::strip($this->content_html)) > 600
        );
    }

    public function getSummaryAttribute()
    {
        $excerpt = $this->excerpt;
        if (strlen(trim($excerpt))) {
            return $excerpt;
        }

        $more = '<!-- more -->';
        if (strpos($this->content_html, $more) !== false) {
            $parts = explode($more, $this->content_html);
            return array_get($parts, 0);
        }

        return Str::limit(Html::strip($this->content_html), 600);
    }

    protected static function getPostPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $properties = $page->getComponentProperties('blogPost');
        if (!isset($properties['slug'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['slug'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);

        return $url;
    }

}