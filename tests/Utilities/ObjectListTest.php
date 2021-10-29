<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\Utilities\DataManager\ObjectList;

class ObjectListTest extends TestCase
{

    public function testOrderBy()
    {
        $list1 = [
            (object)['id'=>3, 'price'=>100],
            (object)['id'=>1, 'price'=>50],
            (object)['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ObjectList($list1);

        $actual = $arrayList1->orderBy('price', 'desc')->toArray();

        $expected = [
            (object)['id'=>2, 'price'=>150],          
            (object)['id'=>3, 'price'=>100],
            (object)['id'=>1, 'price'=>50],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testPluck()
    {
        $list1 = [
            (object)['id'=>3, 'price'=>100],
            (object)['id'=>1, 'price'=>50],
            (object)['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ObjectList($list1);

        $actual = $arrayList1->pluck('id');

        $expected = [3, 1, 2];

        $this->assertEquals($expected, $actual);
    }

    public function testGroupBy()
    {
        $list1 = [
            (object)['id'=>2, 'category'=>'cat2', 'name'=>'name2'],
            (object)['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
            (object)['id'=>1, 'category'=>'cat1', 'name'=>'name1'],
        ];

        $arrayList1 = new ObjectList($list1);

        $actual = $arrayList1->groupBy('category');

        $expected = [
            'cat1' => new ObjectList([
                (object)['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
                (object)['id'=>1, 'category'=>'cat1', 'name'=>'name1'],                
            ]),
            'cat2' => new ObjectList([
                (object)['id'=>2, 'category'=>'cat2', 'name'=>'name2'],               
            ]),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testGetObjectMap()
    {
        $list1 = [
            (object)['id'=>2, 'category'=>'cat2', 'name'=>'name2'],
            (object)['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
            (object)['id'=>1, 'category'=>'cat1', 'name'=>'name1'],
        ];

        $arrayList1 = new ObjectList($list1);

        $actual = $arrayList1->getObjectMap('name');

        $expected = [
            'name2' => (object)['id'=>2, 'category'=>'cat2', 'name'=>'name2'],
            'name3' => (object)['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
            'name1' => (object)['id'=>1, 'category'=>'cat1', 'name'=>'name1'],            
        ];

        $this->assertEquals($expected, $actual);
    }


    public function testOrderByCallBack()
    {
        $list1 = [
            (object)['id'=>3, 'name'=>'name3'],
            (object)['id'=>1, 'name'=>'name1'],
            (object)['id'=>2, 'name'=>'name2'],
        ];

        $arrayList1 = new ObjectList($list1);

        $arrayList1->orderByCallBack(function($a, $b) {
            return $a->id <=> $b->id;
        });

        $expected = [
            (object)['id'=>1, 'name'=>'name1'],
            (object)['id'=>2, 'name'=>'name2'],
            (object)['id'=>3, 'name'=>'name3'],            
        ];

        $this->assertEquals($expected, $arrayList1->toArray());
    }

    public function testFilter()
    {
        $list1 = [
            (object)['id'=>3, 'price'=>100],
            (object)['id'=>1, 'price'=>50],
            (object)['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ObjectList($list1);

        $actual = $arrayList1->filter(function($item) {
            return $item->price >= 100;
        })->toArray();

        $expected = [
            (object)['id'=>3, 'price'=>100],
            (object)['id'=>2, 'price'=>150],         
        ];

        $this->assertEquals($expected, $actual);
    }

}