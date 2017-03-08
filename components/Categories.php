<?php namespace Mavitm\Content\Components;

use Db;
use App;
use Request;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Mavitm\Content\Models\Category as BlogCategory;

class Categories extends ComponentBase
{
    /**
     * @var Collection A collection of categories to display
     */
    public $categories;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    /**
     * @var string Reference to the current category slug.
     */
    public $currentCategorySlug;

    public function componentDetails()
    {
        return [
            'name'        => 'mavitm.content::lang.content.categories_components.title',
            'description' => 'mavitm.content::lang.content.categories_components.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'mavitm.content::lang.content.categories_components.category_slug',
                'description' => 'mavitm.content::lang.content.categories_components.category_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'displayEmpty' => [
                'title'       => 'mavitm.content::lang.content.categories_components.category_display_empty',
                'description' => 'mavitm.content::lang.content.categories_components.category_display_empty_description',
                'type'        => 'checkbox',
                'default'     => 0
            ],
            'categoryPage' => [
                'title'       => 'mavitm.content::lang.content.categories_components.category_page',
                'description' => 'mavitm.content::lang.content.categories_components.category_page_description',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'Links',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->currentCategorySlug = $this->page['currentCategorySlug'] = $this->property('slug');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }

    protected function loadCategories()
    {
        $categories = BlogCategory::orderBy('name');
        if (!$this->property('displayEmpty')) {
            $categories->whereExists(function($query) {
                $prefix = Db::getTablePrefix();

                $query
                    ->select(Db::raw(1))
                    ->from('mavitm_content_posts_categories')
                    ->join('mavitm_content_posts', 'mavitm_content_posts.id', '=', 'mavitm_content_posts_categories.post_id')
                    ->whereNotNull('mavitm_content_posts.published')
                    ->where('mavitm_content_posts.published', '=', 1)
                    ->whereRaw($prefix.'mavitm_content_categories.id = '.$prefix.'mavitm_content_posts_categories.category_id')
                ;
            });
        }

        $categories = $categories->getNested();

        /*
         * Add a "url" helper attribute for linking to each category
         */
        return $this->linkCategories($categories);
    }

    protected function linkCategories($categories)
    {
        return $categories->each(function($category) {
            $category->setUrl($this->categoryPage, $this->controller);

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });
    }
}
