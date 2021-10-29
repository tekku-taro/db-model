<?php
namespace Taro\DBModel\Utilities\DataManager;

use Taro\DBModel\Exceptions\InvalidDataException;

/**
 * 配列データが対象の ActiveList
 */
class ArrayList extends ActiveList
{
    // リストの並び替え $order = asc / desc
    public function orderBy($key, $order = 'asc'):self
    {
        usort($this->list, function ($item1, $item2) use($key, $order) {
            if($order === 'asc') {
                return $item1[$key] <=> $item2[$key];
            } elseif ($order === 'desc') {
                return $item2[$key] <=> $item1[$key]; 
            }
        });

        return $this;
    }
    
    
    // リストの各要素の特定キーのみを取得する    
    public function pluck($key):array
    {
        $values = [];
        foreach ($this->list as $itemArray) {
            if(isset($itemArray[$key])) {
                $values[] = $itemArray[$key];
            }
        }
        return $values;
    }

    /**
     * key の値でリストをグループ分けする 
     * 
     * @param mixed $key
     * @return array<ActiveList>
     */        
    public function groupBy($key):array
    {
        $groups = [];
        foreach ($this->list as $itemArray) {
            $groups[$itemArray[$key]][] = $itemArray;
        }
        $returnArray = [];
        foreach ($groups as $keyVal => $subList) {
            $returnArray[$keyVal] = new static($subList);
        }
        
        ksort($returnArray);

        return $returnArray;
    }

    // $key がキーの 配列のマップを作成する 
    public function getArrayMap($key):array
    {
        $keyMap = [];
        foreach ($this->list as $itemArray) {
            if(!isset($itemArray[$key])) {
                throw new InvalidDataException('$list の要素の配列に' . $key . 'をキーとする要素がありません。');
            }

            $keyMap[$itemArray[$key]] = $itemArray;
        }

        return $keyMap;
    }


}