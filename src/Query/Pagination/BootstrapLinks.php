<?php
namespace Taro\DBModel\Query\Pagination;

class BootstrapLinks extends BasicLinks
{
    public $prevLabel = '前';

    public $nextLabel = '次';

    public $firstLabel = '最初';

    public $lastLabel = '最後';

    protected $separator = '';

    protected $wrapper = [
        'prefix' => '<nav aria-label="Page navigation"><ul class="pagination">', 
        'suffix' => '</ul></nav>'
    ];    

    protected $linkAttributes = [
        'class' =>['page-link']
    ];

    public function generateLabelLink($labelName, $href, $isSelected = false, $disabled = false):string
    {
        return $this->generateLink($href, $this->{$labelName}, $isSelected, $disabled);
    }
    
    public function generateNumLink($pageNo, $href, $isSelected = false, $disabled = false):string
    {       
        return $this->generateLink($href, $pageNo, $isSelected, $disabled);
    }

    protected function generateLink($href, $label, $isSelected = false, $disabled = false)
    {
        if($disabled) {
            return '<li class="page-item disabled"><a href="' . $href . '" class="' .  $this->getClassAttributes() . '" tabindex="-1">' . $label . '</a></li>';
        }
        if($isSelected) {
            return '<li class="page-item active"><a href="' . $href . '" class="' .  $this->getClassAttributes() . '">' . $label . '</a></li>';
        }
        return '<li class="page-item"><a href="' . $href . '" class="' .  $this->getClassAttributes() . '">' . $label . '</a></li>';
    }

}