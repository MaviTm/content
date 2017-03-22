<?php namespace Mavitm\Content\Classes;
/**
*@Author Mavitm
*@url http://www.mavitm.com
*/
use Mavitm\Compon\Models\Mtmdata;
use Mavitm\Content\Models\Post as BlogPost;
use PhpParser\Node\Expr\Cast\Array_;
use Cms\Classes\Controller as CmsController;
use Rjchauhan\LightGallery\Models\ImageGallery as ImageGalleryModel;

class TextFilter {

    protected $allComponents = [];

    use \October\Rain\Support\Traits\Singleton;

    public function setVariable(Array $var){

        if(count($this->allComponents)){
            $this->allComponents = array_merge($this->allComponents, $var);
        }else{
            $this->allComponents = $var;
        }

    }

    public function parseAccordion($ID){
//        $params = new \stdClass();
//        $params->currentParentId    = $ID;
//        $params->componUnique       = 'accordion'.$params->currentParentId;
//        $params->panelDefaultColor  = 'panel-default';
//        $params->componChildren     = Mtmdata::where([ 'groups' => 'accordion', 'parent_id' => $params->currentParentId ])->get();


        $r = CmsController::getController()->renderComponent("componAccordion", [
            'id' => $ID,
            'panelColor' => 'panel-default'
        ]);

        return $r;
    }


    /*******************************************************************************************************************
    *
    */


    public function textInGallery($string)
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