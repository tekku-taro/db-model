<?php
namespace Taro\DBModel\Query;

use Taro\DBModel\Models\Model;
use Taro\DBModel\Utilities\Paginator;

class QueryBuilder
{
    public $query;

    public $modelName;

 public function where():self
 {

 }

 public function whereIn():self    
 {

 }

 public function whereBetween():self    
 {

 }

 public function addWhere():self   
 {

 }

 public function orderBy():self    
 {

 }

 public function limit():self    
 {

 }

 public function groupBy():self    
 {

 }

 public function select():self    
 {

 }

 public function getFirst():Model    
 {

 }

 public function getAll():array
 {

 }

 public function getArrayAll():array    
 {

 }

 public function getPaginator(int $number):Paginator    
 {

 }

 public function findById():Model    
 {

 }

 public function with():self    
 {

 }

 private function checkInput() 
 {

 }

 private function hydrateList():array    
 {

 }

 private function hydrate():Model    
 {

 }

 public function isRelatedModel():bool
 {

 }
}