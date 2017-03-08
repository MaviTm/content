<?php namespace Mavitm\Content\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Mavitm\Content\Models\Post as BlogPost;
use Rjchauhan\LightGallery\Models\ImageGallery as ImageGalleryModel;

class Post extends ComponentBase
{
    public $post;
    public $categoryPage;

    protected $allComponents = [];

    public function componentDetails()
    {
        return [
            'name'        => 'mavitm.content::lang.components.post.title',
            'description' => 'mavitm.content::lang.components.post.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'mavitm.content::lang.post.slug',
                'description' => 'mavitm.content::lang.components.post.post_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'categoryPage' => [
                'title'       => 'mavitm.content::lang.content.categories_components.category_page',
                'description' => 'mavitm.content::lang.content.categories_components.category_page_description',
                'type'        => 'dropdown',
                'default'     => 'content/category',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->addCss('assets/css/frontend.css');

        foreach($this->page->attributes as $ar){
            if(is_array($ar)){
                $this->allComponents = array_merge($this->allComponents, $ar);
            }
        }
        $this->page["allComponents"] = $this->allComponents;
        $this->page["gallery"] = 0;
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->post = $this->page['post'] = $this->loadPost();

    }

    protected function loadPost()
    {
        $slug = $this->property('slug');

        $post = new BlogPost;

        $post = $post->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $post->transWhere('slug', $slug)
            : $post->where('slug', $slug);

        $post = $post->isPublished()->first();

        if(strpos($post->content_html,"[/gallery]")){
            $this->page["gallery"] = 1;

            $post->content_html = $this->textInGallery($post->content_html);
        }

        /*
         * Add a "url" helper attribute for linking to each category
         */
        if ($post && $post->categories->count()) {
            $post->categories->each(function($category){
                $category->setUrl($this->categoryPage, $this->controller);
            });
        }

        return $post;
    }

    protected function textInGallery($string)
    {
        $string = preg_replace_callback('#\[gallery\](.*?)\[\/gallery\]#is', array($this,"textInGalleryInsert"), $string);
        return $string;
    }

    protected function textInGalleryInsert($param)
    {
        $id = explode('-',$param[1])[0];
        if(!is_numeric($id)){
            return '';
        }

        $mainThumbWidth     = (!empty($this->allComponents['mainThumbWidth']) ? $this->allComponents['mainThumbWidth'] : 80);
        $mainThumbHeight    = (!empty($this->allComponents['mainThumbHeight']) ? $this->allComponents['mainThumbHeight'] : 60);
        $resizer            = (!empty($this->allComponents['resizer']) ? $this->allComponents['resizer'] : 'crop');

        $gallery = ImageGalleryModel::find($id);

        $return = '<div style="width: 100%" class="lightGallery">';
            foreach($gallery->images as $im){
                $return .= '<div data-src="'.$im->path.'" data-sub-html="<h4>'.$im->title.'</h4><p>'.$im->description.'</p>">
            <a href=""><img class="img-responsive" src="'.$im->getThumb($mainThumbWidth, $mainThumbHeight, $resizer).'" alt="'.$im->title.'"></a>
        </div>';
            }
        $return .= '</div><div class="clearfix"></div> ';
        return $return;
    }

}
