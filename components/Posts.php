<?php namespace Mavitm\Content\Components;

use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Mavitm\Content\Models\Post as BlogPost;
use Mavitm\Content\Models\Category as BlogCategory;

class Posts extends ComponentBase
{
    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * Parameter to use for the page number
     * @var string
     */
    public $pageParam;

    /**
     * If the post list should be filtered by a category, the model to use.
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages.
     * @var string
     */
    public $noPostsMessage;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage;

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage;

    /**
     * If the post list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder;

    public function componentDetails()
    {
        return [
            'name'        => 'mavitm.content::lang.components.posts.title',
            'description' => 'mavitm.content::lang.components.posts.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => 'mavitm.content::lang.components.posts.posts_pagination',
                'description' => 'mavitm.content::lang.components.posts.posts_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'categoryFilter' => [
                'title'       => 'mavitm.content::lang.components.posts.posts_filter',
                'description' => 'mavitm.content::lang.components.posts.posts_filter_description',
                'type'        => 'string',
                'default'     => ''
            ],
            'postsPerPage' => [
                'title'             => 'mavitm.content::lang.components.posts.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'mavitm.content::lang.components.posts.posts_per_page_validation',
                'default'           => '10',
            ],
            'groupType' => [
                'title'       => 'mavitm.content::lang.post.content_type',
                'type'        => 'dropdown',
                'default'     => '',
            ],
            'noPostsMessage' => [
                'title'        => 'mavitm.content::lang.components.posts.posts_no_posts',
                'description'  => 'mavitm.content::lang.components.posts.posts_no_posts_description',
                'type'         => 'string',
                'default'      => 'No posts found',
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'mavitm.content::lang.components.posts.posts_order',
                'description' => 'mavitm.content::lang.components.posts.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc'
            ],
            'categoryPage' => [
                'title'       => 'mavitm.content::lang.components.posts.posts_category',
                'description' => 'mavitm.content::lang.components.posts.posts_category_description',
                'type'        => 'dropdown',
                'default'     => 'content/category',
                'group'       => 'Links',
            ],
            'postPage' => [
                'title'       => 'mavitm.content::lang.components.posts.posts_post',
                'description' => 'mavitm.content::lang.components.posts.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'content/post',
                'group'       => 'Links',
            ],
            'exceptPost' => [
                'title'             => 'mavitm.content::lang.components.posts.posts_except_post',
                'description'       => 'mavitm.content::lang.components.posts.posts_except_post_description',
                'type'              => 'string',
                'validationPattern' => 'string',
                'validationMessage' => 'mavitm.content::lang.components.posts.posts_except_post_validation',
                'default'           => '',
                'group'             => 'Exceptions',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        return BlogPost::$allowedSortingOptions;
    }

    public function getGroupTypeOptions(){
        return BlogPost::$posType;
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->posts = $this->page['posts'] = $this->listPosts();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1)
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noPostsMessage = $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        /*
         * Page links
         */
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
    }

    protected function listPosts()
    {
        $category = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */
        $posts = BlogPost::with('categories')->listFrontEnd([
            'page'       => $this->property('pageNumber'),
            'sort'       => $this->property('sortOrder'),
            'perPage'    => $this->property('postsPerPage'),
            'search'     => trim(input('search')),
            'category'   => $category,
            'exceptPost' => $this->property('exceptPost'),
            'type'       => $this->property('groupType'),
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $posts->each(function($post) {
            $post->setUrl($this->postPage, $this->controller);

            $post->categories->each(function($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $posts;
    }

    protected function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) {
            return null;
        }

        $category = new BlogCategory;

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }
}
