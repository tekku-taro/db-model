<?php
namespace Taro\DBModel\Query\Pagination;

interface LinksTemplate
{
    
    public function wrapLinks($links):string;

    public function generateLabelLink($labelName, $href, $isSelected = false, $disabled = false):string;

    
    public function generateNumLink($pageNo, $href, $isSelected = false, $disabled = false):string;

        
    public function getSeparator():string;

    public function getLabel($labelName):string;


}