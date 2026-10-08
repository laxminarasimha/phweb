<?php
// WordPress Dynamic Query Builder for Deskuss
class WPDynamicQueryBuilder {
	private $wpdb;
	private $select = [];
	private $from = '';
	private $joins = [];
	private $where = [];
	private $orderBy = [];
	private $groupBy = [];
	private $having = [];
	private $limit = '';
	private $offset = '';
	private $parameters = [];

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function select($fields){
		if(is_array($fields)){
			$this->select = array_merge($this->select, $fields);
		}
		else{
			$this->select[] = $fields;
		}
		return $this;
	}
	public function from($table, $alias = ''){
		$this->from = $this->wpdb->prefix .$table;
		if ($alias) {
			$this->from .= " AS $alias";
		}
		return $this;
	}

	public function join($table, $condition, $type = 'LEFT', $alias = '') {
		$table = $this->wpdb->prefix . $table;
		if ($alias) {
			$table .= " AS $alias";
		}
		$this->joins[] = strtoupper($type) . " JOIN $table ON $condition";
		return $this;
	}

	public function where($condition, $value = null, $operator = '=') {
		if ($value !== null) {
			$this->parameters[] = $value;
			$placeholder = is_numeric($value) ? '%d' : '%s';
			$condition = "$condition $operator $placeholder";
		}
		$this->where[] = count($this->where) === 0 ? $condition : "AND $condition";
		return $this;
	}

	public function orWhere($condition, $value = null, $operator = '=') {
		if ($value !== null) {
			$this->parameters[] = $value;
			$placeholder = is_numeric($value) ? '%d' : '%s';
			$condition = "$condition $operator $placeholder";
		}
		$this->where[] = "OR $condition";
		return $this;
	}

	public function orderBy($field, $direction = 'ASC') {
		$this->orderBy[] = "$field " . strtoupper($direction);
		return $this;
	}

	public function groupBy($field) {
		$this->groupBy[] = $field;
		return $this;
	}

	public function having($condition) {
		$this->having[] = $condition;
		return $this;
	}

	public function limit($limit) {
		$this->limit = (int)$limit;
		return $this;
	}

	public function offset($offset) {
		$this->offset = (int)$offset;
		return $this;
	}

	public function getSQL() {
		$sql = "SELECT " . ($this->select ? implode(", ", $this->select) : '*');
		$sql .= " FROM " . $this->from;

		if ($this->joins) {
			$sql .= " " . implode(" ", $this->joins);
		}

		if ($this->where) {
			$sql .= " WHERE " . implode(" ", $this->where);
		}

		if ($this->groupBy) {
			$sql .= " GROUP BY " . implode(", ", $this->groupBy);
		}

		if ($this->having) {
			$sql .= " HAVING " . implode(" AND ", $this->having);
		}

		if ($this->orderBy) {
			$sql .= " ORDER BY " . implode(", ", $this->orderBy);
		}

		if ($this->limit) {
			$sql .= " LIMIT " . $this->limit;
		}

		if ($this->offset) {
			$sql .= " OFFSET " . $this->offset;
		}

		return $this->parameters ? $this->wpdb->prepare($sql, $this->parameters) : $sql;
	}

	public function get() {
		return $this->wpdb->get_results($this->getSQL());
	}

	public function getOne() {
		return $this->wpdb->get_row($this->getSQL());
	}   

	public function count() {
		$countQuery = new self();
		$countQuery->select('COUNT(*) as count')
		->from($this->from)
		->joins = $this->joins;
		$countQuery->where = $this->where;
		$countQuery->parameters = $this->parameters;
		$result = $countQuery->getOne();
		return $result ? $result->count : 0;
	}

}

?>