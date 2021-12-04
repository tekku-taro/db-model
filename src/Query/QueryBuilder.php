<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\DB\DB;
use Taro\DBModel\Exceptions\WrongSqlException;
use Taro\DBModel\Models\Model;
use Taro\DBModel\Query\Pagination\Paginator;
use Taro\DBModel\Query\Relations\RelationBuilder;
use Taro\DBModel\Utilities\DataManager\ArrayList;
use Taro\DBModel\Utilities\DataManager\ObjectList;

class QueryBuilder extends BaseBuilder
{
    public static function query($modelName, bool $useBindParam = true):QueryBuilder
    {
        $dbManipulator = DB::getGlobal()->getManipulator();
        $builder = new self($dbManipulator, $modelName, $useBindParam);
        return $builder;        
    }


    public function getFirst():Model    
    {
        $result = $this->executeAndFetch();

        return $this->hydrate($result, $this->modelName);
    }

    public function getAll():ObjectList
    {
        $results = $this->executeAndFetchAll();
        $objectList = $this->hydrateList($results, $this->modelName);

        return $this->fetchRelatedModels($objectList);
    }

    public function eagerLoad(array $relations)
    {
        $this->query->relations->setList($relations);
        return $this;
    }

    public function setRelations(RelationList $relations)
    {
        $this->query->relations = $relations;
        return $this;
    }

    protected function fetchRelatedModels(ObjectList $objectList):ObjectList
    {
        if(count($this->query->relations) === 0 || count($objectList) === 0) {
            return $objectList;
        }

        $relations = $this->pickExistingRelations();
        
        foreach ($relations as $relation) {
            // モデルのリレーションメソッドのクエリビルダを作成
            /** @var RelationBuilder $relationBuilder */
            $relationBuilder = $objectList->first()->{$relation}();
            // クエリビルダの getRelatedModelKey から、呼び出し側モデルの ローカルキーを取得する。
            $localKey = $relationBuilder->getRelatedModelKey();
            // モデルのローカルキーのIDのリストを取得
            $idList = $objectList->pluck($localKey);
            // クエリビルダの updateWhereWithIdList() によって、リレーションの条件をリレーションキーの IDリストで上書きする
            $relationBuilder->updateWhereWithIdList($idList);
            // 余ったリレーション先リストをリレーション先ビルダーに渡す
            $relationBuilder->setRelations($this->query->relations);
            // 実行後に結果のリストから、リレーションキー をキーとした mapを取得
            $modelMap = $relationBuilder->getAsMap();
            // 各モデルの localKey を使って、mapから関連モデルを取り出して、
            // 対応するモデルインスタンスの 動的プロパティに保存。
            foreach ($objectList as $model) {
                /** @var Model $model */
                $this->mappingRelatedModels($relation, $modelMap, $model->{$localKey}, $model);
            }

        }

        if(count($this->query->relations) !== 0) {
            throw new WrongSqlException('存在しないリレーション先が指定されました：' . implode(',', $this->query->relations->toArray()));
        }

        return $objectList;
    }

    protected function pickExistingRelations():array
    {
        $existing = [];
        foreach ($this->query->relations as $idx => $relation) {
            if (method_exists($this->modelName, $relation)) {
                $existing[] = $relation;
                $this->query->relations->deleteAt($idx);
            }
        }

        return $existing;
    }

    protected function mappingRelatedModels($relation, array $modelMap, $key, Model $model):void
    {
        if (isset($modelMap[$key])) {
            $model->setDynamicProperty($relation, $modelMap[$key]);
        }
    }

    public function getArrayAll():ArrayList
    {
        $records = $this->executeAndFetchAll();

        return $this->arrayList($records);
    }

    public function paginate(int $pageSize):Paginator    
    {
        return $this->getPaginator($pageSize);
    }

    public function paginateArray(int $pageSize):Paginator    
    {
        return $this->getPaginator($pageSize, true);
    }

    protected function getPaginator(int $pageSize, $isArrayList = false)
    {
        // $_GET から pageNoを取得 又は 0
        $pageNo = $this->getPageNoFromGET();

        $offset = $pageNo * $pageSize;
        $paginator = new Paginator();
        
        // OFFSET に 4 を指定した場合、最初から 4 番目までのデータを飛ばして 5 番目のデータから取得
        $this->offset($offset)->limit($pageSize);
        
        if($isArrayList) {
            $list = $this->getArrayAll();
        } else {
            $list = $this->getAll();
        }
        
        $totalRecordNum = $this->countTotal(); // 全てのレコード数を取得(limit無効)
        
        
        $paginator->setLinkSettings([
          'pageSize' => $pageSize,
          'pageNo' => $pageNo,
          'totalRecordNum' =>$totalRecordNum,
          'list' =>$list,
        ]);

        return $paginator;        
    }

    protected function getPageNoFromGET()
    {
        $request = request();

        return $request->get(Paginator::PAGE_NO_PARAM);
    }

    public function findById($id):Model    
    {
        $this->query->where = [];
        
        $this->where('id', $id);
        $result = $this->executeAndFetch();

        return $this->hydrate($result, $this->modelName);
    }

    public function addColumn($column)
    {
        $this->query->otherColumns[] = $column;
    }

    protected function checkInput() 
    {

    }


    public function isRelatedModel():bool
    {

    }
}