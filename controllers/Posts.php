<?php namespace Mavitm\Content\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Response;
use Rjchauhan\LightGallery\Models\ImageGallery as ImageGalleryModel;

class Posts extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Mavitm.Content', 'content', 'content_list');
    }

    public function create()
    {
        $this->bodyClass = 'compact-container';
        $this->vars['parentlist'] = ImageGalleryModel::select('id', 'name')->orderBy('name')->get()->lists('name', 'id');
        return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->bodyClass = 'compact-container';
        $this->vars['parentlist'] = ImageGalleryModel::select('id', 'name')->orderBy('name')->get()->lists('name', 'id');
        return $this->asExtension('FormController')->update($recordId);
    }

    public function onGalleries(){
        return Response::json(ImageGalleryModel::select('id', 'name')->orderBy('name')->get()->lists('name', 'id'), 200);
    }

}