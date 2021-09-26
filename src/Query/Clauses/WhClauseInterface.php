<?php
namespace Taro\DBModel\Query\Clauses;

interface WhClauseInterface
{
    public function compile(WhParams $params, bool $useBindParam);

    public function getConjunct(): string;

    public function setConjunct(string $value);
}

