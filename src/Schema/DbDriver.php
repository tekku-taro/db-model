<?php
namespace Taro\DBModel\Schema;


class DbDriver
{
    public $name;
    public $dbName;
    public $type;
    public const MY_SQL = 'mysql';
    public const SQLITE = 'sqlite';
    public const POSTGRE_SQL = 'pgsql';

    function __construct(string $name, string $dbName)
    {
        $this->name = $name;
        $this->dbName = $dbName;
        $this->setType();
    }

    public function setType()
    {
        switch ($this->name) {
            case 'mysql':
                $this->type = self::MY_SQL;
                break;
            case 'sqlite':
                $this->type =  self::SQLITE;
                break;
            case 'pgsql':
                $this->type =  self::POSTGRE_SQL;
                break;            
            default:
                $this->type =  null;
                break;
        }
    }
}