<?php
namespace Taro\DBModel\Utilities\DataManager;

use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;

/**
 * AcitveList は ArrayList, ObjectList の親抽象クラス
 * 配列として扱え、格納するリストデータを操作する便利なメソッドを提供する
 * 
 * クエリビルダでクエリ実行後の結果は、ActiveList を実装したクラスのオブジェクトとして返される。
 */
abstract class ActiveList implements IteratorAggregate, Countable
{

    /**
     * 扱うリストを格納する
     *
     * @var array<mixed>
     */    
    protected $list = [];

    public function __construct($list = [])
    {
        $this->setList($list);
    }

    // list の格納
    public function setList(array $list):self
    {
        $this->list = $list;
        return $this;
    }


    // ActiveList 同士の結合 同じキーは上書きされる
    public function merge(ActiveList $activeList):self
    {
        $mergingList = $activeList->toArray();
        $this->list = array_merge($this->list, $mergingList);

        return $this;
    }

    // リストの並び替え $order = asc / desc
    abstract public function orderBy($key, $order = 'asc');
    
    
    // callback によるリストの並び替え 
    public function orderByCallBack(Closure $callback)
    {
        usort($this->list, $callback);

        return $this;
    } 
    
    // リストの各要素の特定キーのみを取得する    
    abstract public function pluck($key):array;

    // リストを条件で絞り込む 非破壊的
    public function filter(Closure $callback):self
    {
        $list = array_values(array_filter($this->list, $callback));


        return $this->clone()->setList($list);
    } 

    //  先頭を取得してリストから取り除く 
    public function shift()
    {
        return array_shift($this->list);
    }   

    //末尾を取得してリストから取り除く   
    public function pop()
    {
        return array_pop($this->list);
    } 

    // リストの先頭を取得
    public function first()
    {
        if(empty($this->list)) {
            return null;
        }
        return $this->list[array_key_first($this->list)];
    }    

    // リストの末尾を取得    
    public function last()
    {
        if(empty($this->list)) {
            return null;
        }
        return $this->list[array_key_last($this->list)];
    }

    // リストの末尾に要素を追加  
    public function push($item):self
    {
        $this->list[] = $item;
        return $this;
    }

    // リストの index 番目の要素を取り出す (先頭は 0)
    public function item($index)
    {
        if(!isset($this->list[$index])) {
            return null;
        }
        return $this->list[$index];
    }

    /**
     * 先頭(0) から offset 番目から length 個取り出す  非破壊的
     *
     * @param int $offset
     * @param int $length
     * @return self
     */ 
    public function slice($offset, $length):self
    {
        $list = array_slice($this->list, $offset, $length);
        return $this->clone()->setList($list);
    }

    // 各要素に処理を実行する    
    public function map(Closure $callback):self
    {
        $this->list = array_map($callback, $this->list);

        return $this;
    }

    // 条件が true のものがひとつでもあるか    
    public function ifAny(Closure $callback):bool
    {
        foreach ($this->list as $item) {
            if($callback($item)) {
                return true;
            }
        }

        return false;
    }

    // すべての要素で条件が true になるか    
    public function ifAll(Closure $callback):bool
    {
        foreach ($this->list as $item) {
            if($callback($item) === false) {
                return false;
            }
        }

        return true;
    }


    // keyの値 === value の要素を削除する
    abstract public function removeIf($key, $value);    

    /**
     * key の値でリストをグループ分けする 
     * 
     * @param mixed $key
     * @return array<ActiveList>
     */        
    abstract public function groupBy($key):array;


    public function clone()
    {
        return new static($this->list);
    }


    // 格納したデータを配列として返す 
    public function toArray():array
    {
        return $this->list;
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