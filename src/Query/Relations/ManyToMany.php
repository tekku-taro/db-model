<?php
namespace Taro\DBModel\Query\Relations;

use Taro\DBModel\DB\DbManipulator;
use Taro\DBModel\Query\BaseBuilder;
use Taro\DBModel\Query\Clauses\Wh;
use Taro\DBModel\Query\DirectSql;
use Taro\DBModel\Traits\EagerBinding;

class ManyToMany extends RelationBuilder
{
    public $pKey;

    public $fKey;    

    public $relKey;    

    public $pivotTable;    

    public $modelName;

    public $relkVal;

    protected $canMultiRecords = true;

    public function __construct(RelationParams $params, DbManipulator $dbManipulator, bool $useBindParam = true)
    {
        parent::__construct($dbManipulator, $params->modelName, $useBindParam);

        $this->pKey = $params->pKey;
        $this->fKey = $params->fKey;
        $this->relKey = $params->relKey;
        $this->pivotTable = $params->pivotTable;
        $this->modelName = $params->modelName;
        $this->relkVal = $params->relkVal;
        $this->relatedModelkey = $params->relatedModelkey;

        $this->join($this->pivotTable)
            ->on($this->pKey, $this->fKey)
            ->where($this->pivotTable . '.' . $this->relKey, $this->relkVal)
            ->addColumn($this->pivotTable . '.' . $this->relKey . ' AS '.self::MAP_KEY.' ')
            ;

            
        $this->setBindingParams($useBindParam);
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
            foreach ($conditions as $condition) {
                ['column'=>$column, 'operand'=>$operand, 'value'=>$value] = Wh::reform($condition);
                
                $where->addAnd($column, $operand, $value);
            }
            $query->addWhClause($where);
        }

        return $query;
    }

}