<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\QueryBuilder;

use AbstractRepo\Plugins\Types\StringUtil;

class QueryBuilder
{
    public const BIND_CHAR = ':';
    private string $query = StringUtil::EMPTY;

    public function select(?array $columns = null): self
    {
        $this->append("SELECT");
        if (!empty($columns)) {
            $this->append(implode(',', $columns));
        } else {
            $this->append("*");
        }
        return $this;
    }

    private function append(string $str): void
    {
        $this->query .= "{$str} ";
    }

    public function insert(string $table, array $columns): self
    {
        $this->append("INSERT INTO {$table}");
        $this->append("(" . implode(",", $columns) . ")");
        $this->append("VALUES");
        $this->append("(" . implode(",", array_map(fn($val) => self::BIND_CHAR . "{$val}", $columns)) . ");");
        return $this;
    }

    public function update(string $table, array $columns): self
    {
        $this->append("UPDATE {$table} SET");
        $this->append(implode(',', array_map(fn($val) => "{$val} = " . self::BIND_CHAR . $val, $columns)));
        return $this;
    }

    public function delete(string $tableName): self
    {
        $this->append("DELETE FROM {$tableName}");
        return $this;
    }

    public function from(string $from): self
    {
        $this->append("FROM {$from}");
        return $this;
    }

    public function where(string $condition): self
    {
        $this->append("WHERE {$condition}");
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}