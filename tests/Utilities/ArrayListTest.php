<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\Utilities\DataManager\ArrayList;

class ArrayListTest extends TestCase
{

    public function testOrderBy()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->orderBy('price', 'desc')->toArray();

        $expected = [
            ['id'=>2, 'price'=>150],          
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testPluck()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->pluck('id');

        $expected = [3, 1, 2];

        $this->assertEquals($expected, $actual);
    }

    public function testGroupBy()
    {
        $list1 = [
            ['id'=>2, 'category'=>'cat2', 'name'=>'name2'],
            ['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
            ['id'=>1, 'category'=>'cat1', 'name'=>'name1'],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->groupBy('category');

        $expected = [
            'cat1' => new ArrayList([
                ['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
                ['id'=>1, 'category'=>'cat1', 'name'=>'name1'],                
            ]),
            'cat2' => new ArrayList([
                ['id'=>2, 'category'=>'cat2', 'name'=>'name2'],               
            ]),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testGetArrayMap()
    {
        $list1 = [
            ['id'=>2, 'category'=>'cat2', 'name'=>'name2'],
            ['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
            ['id'=>1, 'category'=>'cat1', 'name'=>'name1'],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->getArrayMap('name');

        $expected = [
            'name2' => ['id'=>2, 'category'=>'cat2', 'name'=>'name2'],
            'name3' => ['id'=>3, 'category'=>'cat1', 'name'=>'name3'],
            'name1' => ['id'=>1, 'category'=>'cat1', 'name'=>'name1'],            
        ];

        $this->assertEquals($expected, $actual);
    }


    public function testMerge()
    {
        $list1 = [
            ['id'=>1, 'name'=>'name1'],
            ['id'=>2, 'name'=>'name2'],
            ['id'=>3, 'name'=>'name3'],
        ];

        $list2 = [
            ['id'=>4, 'name'=>'name1'],
            ['id'=>5, 'name'=>'name2'],
            ['id'=>3, 'name'=>'name3-2'],
        ];

        $arrayList1 = new ArrayList($list1);
        $arrayList2 = new ArrayList($list2);

        $arrayList1->merge($arrayList2);

        $expected = array_merge($list1, $list2);

        $this->assertEquals($expected, $arrayList1->toArray());
    }

    public function testOrderByCallBack()
    {
        $list1 = [
            ['id'=>3, 'name'=>'name3'],
            ['id'=>1, 'name'=>'name1'],
            ['id'=>2, 'name'=>'name2'],
        ];

        $arrayList1 = new ArrayList($list1);

        $arrayList1->orderByCallBack(function($a, $b) {
            return $a['id'] <=> $b['id'];
        });

        $expected = [
            ['id'=>1, 'name'=>'name1'],
            ['id'=>2, 'name'=>'name2'],
            ['id'=>3, 'name'=>'name3'],            
        ];

        $this->assertEquals($expected, $arrayList1->toArray());
    }

    public function testFilter()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->filter(function($item) {
            return $item['price'] >= 100;
        })->toArray();

        $expected = [
            ['id'=>3, 'price'=>100],
            ['id'=>2, 'price'=>150],         
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testShift()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->shift();

        $expected = ['id'=>3, 'price'=>100];

        $this->assertEquals($expected, $actual);

        $expected = [            
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],        
        ];

        $this->assertEquals($expected, $arrayList1->toArray());
    }

    public function testPop()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->pop();

        $expected = ['id'=>2, 'price'=>150];

        $this->assertEquals($expected, $actual);

        $expected = [            
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],       
        ];

        $this->assertEquals($expected, $arrayList1->toArray());
    }

    public function testFirst()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->first();

        $expected = ['id'=>3, 'price'=>100];

        $this->assertEquals($expected, $actual);


        $this->assertEquals($list1, $arrayList1->toArray());
    }


    public function testLast()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->last();

        $expected = ['id'=>2, 'price'=>150];

        $this->assertEquals($expected, $actual);

        $this->assertEquals($list1, $arrayList1->toArray());
    }

    public function testItem()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->item(2);

        $expected = ['id'=>2, 'price'=>150];
        $this->assertEquals($expected, $actual);

        $actual = $arrayList1->item(4);
        $this->assertNull($actual);
    }

    public function testSlice()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
            ['id'=>4, 'price'=>90],
        ];

        $arrayList1 = new ArrayList($list1);
        $original = $arrayList1->clone();

        $actual = $arrayList1->slice(2,2)->toArray();

        $expected = [
            ['id'=>2, 'price'=>150],
            ['id'=>4, 'price'=>90],            
        ];

        $this->assertEquals($expected, $actual);

        $this->assertEquals($original, $arrayList1);
    }

    public function testMap()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->map(function($item){
            $item['price'] *= 2;
            return $item;
        })->toArray();

        $expected = [
            ['id'=>3, 'price'=>200],
            ['id'=>1, 'price'=>100],
            ['id'=>2, 'price'=>300],           
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testIfAny()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->ifAny(function($item){
            return $item['price'] === 150;
        });

        $this->assertTrue($actual);

        $actual = $arrayList1->ifAny(function($item){
            return $item['price'] > 150;
        });

        $this->assertFalse($actual);
    }

    public function testIfAll()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->ifAll(function($item){
            return $item['price'] > 30;
        });

        $this->assertTrue($actual);

        $actual = $arrayList1->ifAll(function($item){
            return $item['price'] >= 100;
        });

        $this->assertFalse($actual);
    }

    public function testCountable()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->count();

        $this->assertEquals(3, $actual);
    }

    public function testIterator()
    {
        $list1 = [
            ['id'=>3, 'price'=>100],
            ['id'=>1, 'price'=>50],
            ['id'=>2, 'price'=>150],
        ];

        $arrayList1 = new ArrayList($list1);

        $actual = $arrayList1->getIterator();

        $this->assertInstanceOf(Traversable::class, $actual);

        $actual = [];
        foreach ($arrayList1 as $itemArray) {
            $actual[] = $itemArray['id'];
        }
        $expected = [3,1,2];
        $this->assertEquals($expected, $actual);
    }

}