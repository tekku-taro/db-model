<?php
namespace Taro\DBModel\Schema;


class DbDriver
{
    public $name;
    public $dbName;
    public $owner;
    public $type;
    public const MY_SQL = 'mysql';
    public const SQLITE = 'sqlite';
    public const POSTGRE_SQL = 'pgsql';

    function __construct(array $config)
    {
        $this->name = $config['driver'];
        $this->dbName = $config['dbname'] ?? null;
        $this->owner = $config['owner'] ?? null;
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