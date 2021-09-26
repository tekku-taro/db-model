<?php

use PHPUnit\Framework\TestCase;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Query\Clauses\Wh;

class WhTest extends TestCase
{

    public function setUp():void
    {
    }

    public function tearDown():void
    {
    }

    public function testAddAndAddOr()
    {        
        $where = new Wh();
        $where->add('column1', 'value1');
        $where->addAnd('column2', 'value2');
        $where->addOr('column3', 'value3');
        $where->addAnd('column4', 'value4');
        $actual = $where->compile();
        $expected = 
        '( column1 = :param1 AND column2 = :param2 OR column3 = :param3 AND column4 = :param4 )';
        $this->assertEquals($expected, $actual);
        
        $actual = $where->getParams();
        $expected = [   
            ':param1' => 'value1',
            ':param2' => 'value2',
            ':param3' => 'value3',
            ':param4' => 'value4',                
        ];
        // var_export($actual);
        $this->assertEquals($expected, $actual);

    }

    public function testAddAndBlockAddOrBlock()
    {
        
        $where = new Wh();
        $where->addBlock(
            Wh::and(
                Wh::block('weight', 500),
                Wh::or(
                    Wh::block('size', 'L'),
                    Wh::between('length', 5, 10)
                )
            )
        );
        $where->addOrBlock(
            Wh::block('color', 'IN', ['yellow', 'blue']),
        );
        $actual = $where->compile();

        // (  weight AND  (  size OR  length  )  ) OR  color
        $expected = 
        '( ( weight = :param5 AND ( size = :param6 OR length BETWEEN :param7 AND :param8 ) ) OR color IN  ( :param9, :param10 )  )';
        $this->assertEquals($expected, $actual);

        $actual = $where->getParams();
        $expected = [   
            ':param5' => 500,
            ':param6' => 'L',
            ':param7' => 5,
            ':param8' => 10,
            ':param9' => 'yellow',
            ':param10' => 'blue',
        ];
        // var_export($actual);
        $this->assertEquals($expected, $actual);
    }


    public function testDeepNestedBlocks()
    {        
        $where = new Wh();
        $where->addBlock(
            Wh::and(
                Wh::block('level2', 1),
                Wh::and(
                    Wh::block('level3', 1),
                    Wh::and(
                        Wh::block('level4', 1),
                        Wh::and(
                            Wh::block('level5', 1),
                            Wh::and(
                                Wh::block('level6', 1),
                                Wh::block('size', 'L'),
                            )
                        )
                    )
                )
            )
        );
        $where->addAnd('level1', 1);
        $actual = $where->compile();
        var_export($actual);
        // '( level2 AND  (  level3 AND  (  level4 AND  (  level5 AND  (  level6 AND  size  )  )  )  )  )  AND  level1';
        $expected = 
        '( ( level2 = :param11 AND ( level3 = :param12 AND ( level4 = :param13 AND ( level5 = :param14 AND ( level6 = :param15 AND size = :param16 ) ) ) ) ) AND level1 = :param17 )';
        
        $this->assertEquals($expected, $actual);
        
        $actual = $where->getParams();
        $expected = [
            ':param11' => 1,
            ':param12' => 1,
            ':param13' => 1,
            ':param14' => 1,
            ':param15' => 1,
            ':param16' => 'L',
            ':param17' => 1,
        ];
        $this->assertEquals($expected, $actual);

    }

    public function testTooDeepNestedBlocks()
    {        
        $this->expectException(WrongSqlException::class);

        $where = new Wh();
        $where->addBlock(
            Wh::and(
                Wh::block('level2', 1),
                Wh::and(
                    Wh::block('level3', 1),
                    Wh::and(
                        Wh::block('level4', 1),
                        Wh::and(
                            Wh::block('level5', 1),
                            Wh::and(
                                Wh::block('level6', 1),
                                Wh::and(
                                    Wh::block('level7', 1),
                                    Wh::block('size', 'L'),
                                )
                            )
                        )
                    )
                )
            )
        );
        $actual = $where->compile();
    }

}