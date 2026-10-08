<?php

namespace Deskuss\Query;

class Builder
{
    private $wpdb;
    private $selectFields = array();
    private $fromTable = '';
    private $fromAlias = '';
    private $joins = array();
    private $whereClauses = array();
    private $whereParameters = array();
    private $orderByClauses = array();
    private $groupByFields = array();
    private $havingClauses = array();
    private $limitValue = '';
    private $offsetValue = '';

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function select($fields)
    {
        if (is_array($fields)) {
            $this->selectFields = array_merge($this->selectFields, $fields);
        } else {
            $this->selectFields[] = $fields;
        }
        return $this;
    }

    public function from($table, $alias = '')
    {
        $this->fromTable = $this->wpdb->prefix . $table;
        $this->fromAlias = $alias;
        return $this;
    }

    public function join($table, $condition, $type = 'LEFT', $alias = '')
    {
        $fullTable = $this->wpdb->prefix . $table;
        if ($alias) {
            $fullTable .= " AS $alias";
        }
        $this->joins[] = strtoupper($type) . " JOIN $fullTable ON $condition";
        return $this;
    }

    public function where($condition, $value = null, $operator = '=')
    {
        if ($value !== null) {
            $this->whereParameters[] = $value;
            $placeholder = is_numeric($value) ? '%d' : '%s';
            $condition = "$condition $operator $placeholder";
        }
        $this->whereClauses[] = count($this->whereClauses) === 0 ? $condition : "AND $condition";
        return $this;
    }

    public function orWhere($condition, $value = null, $operator = '=')
    {
        if ($value !== null) {
            $this->whereParameters[] = $value;
            $placeholder = is_numeric($value) ? '%d' : '%s';
            $condition = "$condition $operator $placeholder";
        }
        $this->whereClauses[] = "OR $condition";
        return $this;
    }

    public function orderBy($field, $direction = 'ASC')
    {
        $this->orderByClauses[] = "$field " . strtoupper($direction);
        return $this;
    }

    public function groupBy($field)
    {
        $this->groupByFields[] = $field;
        return $this;
    }

    public function having($condition)
    {
        $this->havingClauses[] = $condition;
        return $this;
    }

    public function limit($limit)
    {
        $this->limitValue = (int) $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offsetValue = (int) $offset;
        return $this;
    }

    public function getSQL()
    {
        $sql = "SELECT " . ($this->selectFields ? implode(", ", $this->selectFields) : '*');
        $sql .= " FROM " . $this->fromTable;
        if ($this->fromAlias) {
            $sql .= " AS " . $this->fromAlias;
        }

        if ($this->joins) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if ($this->whereClauses) {
            $sql .= " WHERE " . implode(" ", $this->whereClauses);
        }

        if ($this->groupByFields) {
            $sql .= " GROUP BY " . implode(", ", $this->groupByFields);
        }

        if ($this->havingClauses) {
            $sql .= " HAVING " . implode(" AND ", $this->havingClauses);
        }

        if ($this->orderByClauses) {
            $sql .= " ORDER BY " . implode(", ", $this->orderByClauses);
        }

        if ($this->limitValue) {
            $sql .= " LIMIT " . $this->limitValue;
        }

        if ($this->offsetValue) {
            $sql .= " OFFSET " . $this->offsetValue;
        }

        return $this->whereParameters ? $this->wpdb->prepare($sql, $this->whereParameters) : $sql;
    }

    public function get()
    {
        return $this->wpdb->get_results($this->getSQL());
    }

    public function getOne()
    {
        return $this->wpdb->get_row($this->getSQL());
    }

    public function count()
    {
        $countQuery = new self();
        $countQuery->select('COUNT(*) as count')
            ->from($this->fromTable);
        $countQuery->joins = $this->joins;
        $countQuery->whereClauses = $this->whereClauses;
        $countQuery->whereParameters = $this->whereParameters;
        $result = $countQuery->getOne();
        return $result ? $result->count : 0;
    }
}