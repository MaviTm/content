<?php namespace Mavitm\Content;

use Backend;
use System\Classes\PluginBase;
use Backend\Classes\BackendController;
use Mavitm\Content\Classes\TextFilter;
use Mavitm\Content\Classes\TagProcessor;
use Mavitm\Content\Controllers\Posts as PostsController;

class Plugin extends PluginBase
{

    public $require = ['Rjchauhan.Lightgallery'];

    public function pluginDetails()
    {
        return [
            'name'          => 'mavitm.content::lang.plugin.name',
            'description'   => 'mavitm.content::lang.plugin.description',
            'author'        => 'Mavitm',
            'icon'          => 'oc-icon-archive',
            'homepage'      => 'https://github.com/MaviTm/content'
        ];
    }

    public function registerComponents()
    {
        return [
            'Mavitm\Content\Components\Post'       => 'contentPost',
            'Mavitm\Content\Components\Posts'      => 'contentPosts',
            'Mavitm\Content\Components\Categories' => 'contentCategories',
            'Mavitm\Content\Components\Catmenu' => 'contentCatMenu',
            //'Mavitm\Content\Components\RssFeed'    => 'contentRssFeed'
        ];
    }

    public function registerSettings()
    {
    }

    public function registerNavigation()
    {
        return [
            'content' => [
                'label'         => 'mavitm.content::lang.plugin.name',
                'url'           =>  Backend::url('mavitm/content/posts'),
                'icon'          => 'icon-archive',
                'order'         => 50,

                'sideMenu' => [
                    'content_add' => [
                        'label'         => 'mavitm.content::lang.post.create_content',
                        'url'           =>  Backend::url('mavitm/content/posts/create'),
                        'icon'          => 'icon-plus',
                    ],
                    'content_list' => [
                        'label'         => 'mavitm.content::lang.plugin.name',
                        'url'           =>  Backend::url('mavitm/content/posts'),
                        'icon'          => 'icon-copy',
                    ],
                    'content_category' => [
                        'label'         => 'mavitm.content::lang.content.categories.categories',
                        'url'           => Backend::url('mavitm/content/categories'),
                        'icon'          => 'icon-folder',
                    ]
                ]
            ]
        ];
    }

    public function register()
    {
        PostsController::extend(function($controller) {
            if (!in_array(BackendController::$action, ['create', 'update'])) {
                return;
            }

            $controller->addCss('/plugins/mavitm/content/assets/css/form.css');
            $controller->addJs('/plugins/mavitm/content/assets/js/galleryToolbar.js');
        });

    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'accordion' => [TextFilter::instance(), 'parseAccordion']
            ]
        ];
    }

}
