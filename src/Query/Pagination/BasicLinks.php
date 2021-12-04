<?php
namespace Taro\DBModel\Query\Pagination;

class BasicLinks implements LinksTemplate
{
    public $prevLabel = '<';

    public $nextLabel = '>';

    public $firstLabel = '<<';

    public $lastLabel = '>>';

    protected $separator = '|';

    protected $wrapper = [
        'prefix' => '<div class="pagination">', 
        'suffix' => '</div>'
    ];

    protected $linkAttributes = [
        'class' =>['pagination-link']
    ];
    
    public function wrapLinks($links):string
    {
        return $this->wrapper['prefix'] . $links . $this->wrapper['suffix'];
    }

    public function generateLabelLink($labelName, $href, $isSelected = false, $disabled = false):string
    {
        if($disabled) {
            return $this->{$labelName};
        }
        return $this->generateLink($href, $this->{$labelName});

    }
    
    public function generateNumLink($pageNo, $href, $isSelected = false, $disabled = false):string
    {
        if($disabled) {
            return $pageNo;
        }        
        return $this->generateLink($href, $pageNo);

    }

    protected function generateLink($href, $label, $disabled = false)
    {
        return '<a href="' . $href . '" class="' .  $this->getClassAttributes() . '">' . $label . '</a>';
    }    
    
    
    public function getSeparator():string
    {
        return $this->separator;
    }
    
    public function getLabel($labelName):string
    {
        return $this->{$labelName};
    }

    protected function getClassAttributes()
    {
        return implode(' ', $this->linkAttributes['class']);
    }
}