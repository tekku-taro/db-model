<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\BaseBuilder;
use Taro\DBModel\Query\Clauses\Wh;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Query\QueryBuilder;

class ManyToMany extends QueryBuilder
{
    public $pKey;

    public $fKey;    

    public $relKey;    

    public $pivotTable;    

    public $modelName;

    public $relkVal;

    private $canMultiRecords = true;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->pKey = $params->pKey;
        $this->fKey = $params->fKey;
        $this->relKey = $params->relKey;
        $this->pivotTable = $params->pivotTable;
        $this->modelName = $params->modelName;
        $this->fkVal = $params->fkVal;

        $this->join($this->pivotTable)
            ->on($this->pKey, $this->fKey)
            ->where($this->relKey, $this->relkVal)
            ;
    }

    public function insertPivot($id, $data)
    {
        $query = $this->buildPivotQuery();
        return $query->insert([
                $this->fKey => $id,
                $this->relKey => $this->relkVal                
                ] + $data
            );
    }

    public function updatePivot($id, $data, $conditions = [])
    {
        $query = $this->buildPivotQuery($id, $conditions);

        return $query->update([
                $this->relKey => $this->relkVal                
                ] + $data
            );
    }

    public function deletePivot($id, $conditions = [])
    {
        $query = $this->buildPivotQuery($id, $conditions);


        return $query->delete();
    }

    private function buildPivotQuery($id = null, $conditions = []):BaseBuilder
    {
        $query = DirectSql::query()->table($this->pivotTable);

        if($id !== null) {
            $query->where($this->fKey, $id);
        }

        if(!empty($conditions)) {
            $where = new Wh;
            foreach ($conditions as $column => $value) {
                $where->addAnd($column, $value);
            }
            $query->addWhClause($where);
        }

        return $query;
    }
}