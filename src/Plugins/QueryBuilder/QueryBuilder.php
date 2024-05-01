<?php

declare(strict_types=1);

namespace AbstractRepo\Plugins\QueryBuilder;

use AbstractRepo\Plugins\Utils\StringUtil;

/**
 * Query builder to abstract sql queries building.
 */
class QueryBuilder
{
    /**
     * Character used to identify a bind param
     */
    public const string BIND_CHAR = ':';

    private string $query = StringUtil::EMPTY;

    /**
     * Appends a select statement to the query
     *
     * @param array|null $columns
     * @return $this
     */
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

    /**
     * Appends a statement to the query
     *
     * @param string $str
     * @return void
     */
    private function append(string $str): void
    {
        $this->query .= "{$str} ";
    }

    /**
     * Appends an insert statement to the query
     *
     * @param string $table
     * @param array $columns
     * @return $this
     */
    public function insert(string $table, array $columns): self
    {
        $this->append("INSERT INTO {$table}");
        $this->append("(" . implode(",", $columns) . ")");
        $this->append("VALUES");
        $this->append("(" . implode(",", array_map(fn($val) => self::BIND_CHAR . "{$val}", $columns)) . ");");
        return $this;
    }

    /**
     * Appends an update statement to the query
     *
     * @param string $table
     * @param array $columns
     * @return $this
     */
    public function update(string $table, array $columns): self
    {
        $this->append("UPDATE {$table} SET");
        $this->append(implode(',', array_map(fn($val) => "{$val} = " . self::BIND_CHAR . $val, $columns)));
        return $this;
    }

    /**
     * Appends a delete statement to the query
     *
     * @param string $tableName
     * @return $this
     */
    public function delete(string $tableName): self
    {
        $this->append("DELETE FROM {$tableName}");
        return $this;
    }

    /**
     * Appends a from statement to the query
     *
     * @param string $from
     * @return $this
     */
    public function from(string $from): self
    {
        $this->append("FROM {$from}");
        return $this;
    }

    /**
     * Appends a where statement to the query
     *
     * @param string $condition
     * @return $this
     */
    public function where(string $condition): self
    {
        $this->append("WHERE {$condition}");
        return $this;
    }

    /**
     * Appends a pagination statement to the query
     *
     * @param int $page
     * @param int $itemsPerPage
     * @return $this
     */
    public function paginate(int $page, int $itemsPerPage): self
    {
        $offset = $page * $itemsPerPage;

        $this->append("LIMIT {$itemsPerPage} OFFSET {$offset}");

        return $this;
    }

    /**
     * Returns the built query
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }
}