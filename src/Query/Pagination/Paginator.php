<?php
namespace Taro\DBModel\Query\Pagination;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Taro\DBModel\Exceptions\NotFoundException;
use Taro\DBModel\Utilities\DataManager\ActiveList;
use Taro\DBModel\Utilities\Url;

class Paginator implements IteratorAggregate, Countable
{
    public const PAGE_NO_PARAM = 'pageNo';

    public $currentPageNo;

    public $nextNo;

    public $prevNo;

    public $pageSize = 0;

    public $maxPage = 0;

    public $recordNum = 0;

    public $totalRecordNum = 0;

    /** @var ActiveList|null $list */
    public $list;

    /** @var LinksTemplate */
    public $template;

    public $routeUrl;

    public $queryStrings = [];


    function __construct()
    {
        $this->template = new BootstrapLinks;
        $this->routeUrl = Url::getPageUrlWithoutQueryStrings();
    }

    public function setLinkSettings($settings = [])
    {
        if(isset($settings['pageSize'])) {
            $this->pageSize = $settings['pageSize'];
        }

        if(isset($settings['totalRecordNum'])) {
            $this->totalRecordNum = $settings['totalRecordNum'];
        }

        if($this->pageSize !== null && $this->pageSize > 0) {
            $this->maxPage = (int)floor($this->totalRecordNum / $this->pageSize);
        }

        if(isset($settings['list'])) {
            $this->list = $settings['list'];
            $this->recordNum = count($settings['list']);
        }

        if(isset($settings['pageNo']) && is_int($settings['pageNo'])) {
            $this->currentPageNo = (int) $settings['pageNo'];
            $this->prevNo = ($this->currentPageNo - 1 >= 0)? $this->currentPageNo - 1: 0;
            $this->nextNo = ($this->currentPageNo + 1 <= $this->maxPage)? $this->currentPageNo + 1: $this->maxPage;
        }
        
        return $this;
    }

    public function dispLinks(): string
    {
        // ページネーションリンクを作成して返す
        $links = '';

        $links .= $this->generateLink('firstLabel', 0);
        $links .= $this->template->getSeparator();
        $links .= $this->generateLink('prevLabel', $this->prevNo);
        $links .= $this->template->getSeparator();
        
        for($idx = 0; $idx <= $this->maxPage; $idx++) {
            if($idx === $this->currentPageNo) {
                $links .= $this->generateLink($idx + 1, $idx, true);
            } else {
                $links .= $this->generateLink($idx + 1, $idx);
            }
            $links .= $this->template->getSeparator();
        }
        $links .= $this->generateLink('nextLabel', $this->nextNo); 
        $links .= $this->template->getSeparator();   
        $links .= $this->generateLink('lastLabel', $this->maxPage);    

        return $this->template->wrapLinks($links);
    }


    private function generateLink($label, $pageNo, $isSelected = false)
    {
        $disabled = false;
        
        if($pageNo === $this->currentPageNo && !$isSelected) {
            $disabled = true;
        }


        if(is_int($label)) {
            return $this->template->generateNumLink($label, $this->getHref($pageNo), $isSelected, $disabled);
        } else {
            return $this->template->generateLabelLink($label, $this->getHref($pageNo), $isSelected, $disabled);
        }
    }

    public function setTemplate(LinksTemplate $template)
    {
        $this->template = $template;

        return $this;
    }

    public function getLinkData(): array
    {
        $data = [];

        $data[] =  $this->createLinkItem('firstLabel', 0);
        $data[] =  $this->createLinkItem('prevLabel', $this->prevNo);

        for($idx = 0; $idx <= $this->maxPage; $idx++) {
            if($idx === $this->currentPageNo) {
                $data[] = $this->createLinkItem($idx + 1, $idx, true);
            } else {
                $data[] = $this->createLinkItem($idx + 1, $idx);
            }            
        }
        $data[] =  $this->createLinkItem('nextLabel', $this->nextNo);
        $data[] =  $this->createLinkItem('lastLabel', $this->maxPage);


        return [
            'routeUrl' => $this->routeUrl,
            'links' => $data
        ];
    }

    private function createLinkItem($label, $pageNo, $isSelected = false)
    {
        if(!is_int($label)) {
            $label = $this->template->getLabel($label);
        }
        $item = ['label'=> $label, 'href' => $this->getHref($pageNo)];
        if($isSelected) {
            $item['selected'] = true;
        }
        if($pageNo === $this->currentPageNo) {
            $item['disabled'] = true;
        }

        return $item;
    }

    public function appendParams($params)
    {
        $this->queryStrings += $params;
        return $this;
    }

    public function setUrl(string $url)
    {
        $this->routeUrl = $url;

        return $this;
    } 

    private function getHref($pageNo):string
    {
        $params = [self::PAGE_NO_PARAM => $pageNo] + $this->queryStrings;
        return $this->routeUrl . '?' . http_build_query($params);
    }

    public function __call($name, $arguments)
    {
        if($this->list instanceof ActiveList && method_exists($this->list, $name)) {
            return $this->list->{$name}(...$arguments);
        } else {
            throw new NotFoundException($name . 'というメソッドは存在しません。');
        }
    }

    public function getIterator() 
    {
      return new ArrayIterator($this->list);
    }

    public function count()
    {
      return count($this->list); 
    }    
}