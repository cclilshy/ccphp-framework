<?php

/**
 * ！！！！！！！！！！！不用这个类
 */

namespace core\Database;

use mysqli;
use mysqli_result;
use core\Ccphp\Launch;


class Mysql
{
    public const OPERS = ['IS', 'LIKE', 'NOT', 'IS NOT', '<', '>', '<>', '='];
    protected array       $config;
    protected mysqli      $mysqli;
    protected string      $table;
    protected array       $where   = [];
    protected array       $whereOr = [];
    protected string|null $order   = null;
    protected string      $field   = '*';
    protected array       $data    = [];
    protected string      $action;
    protected string      $command;
    protected mixed       $execr;
    private DatabaseInfo  $databaseInfo;

    /**
     * @param $config
     */
    /**
     * @param $config
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
        }
        defined('DESC') || define('DESC', 'DESC');
        defined('ASC') || define('ASC', 'ASC');
        $this->databaseInfo = new DatabaseInfo($this);
    }

    /**
     *
     */
    public function __destruct()
    {
        if (is_object($this->mysqli))
            $this->mysqli->close();
    }

    /**
     * @param $connect
     * @return void
     */
    /**
     * @param $connect
     * @return void
     */
    public function setConnect($connect): void
    {
        $this->mysqli = $connect;
    }

    /**
     * @param $table
     * @return $this
     */
    /**
     * @param $table
     * @return $this
     */
    public function table($table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param $key
     * @param $compare
     * @param $value
     * @return $this
     */
    /**
     * @param $key
     * @param $compare
     * @param $value
     * @return $this
     */
    public function where($key, $compare = null, $value = null): static
    {
        if ($value === null) {
            $value   = in_array($compare, self::OPERS) ? $value : $compare;
            $compare = in_array($compare, self::OPERS) ? $compare : '=';
        }
        if (!empty($key))
            $this->where[] = is_array($key) ? self::paserWhereArray($key) : [[$key, $compare, $value]];
        return $this;
    }

    /**
     * @param $wheres
     * @return array
     */
    /**
     * @param $wheres
     * @return array
     */
    protected static function paserWhereArray($wheres): array
    {
        $array = [];
        foreach ($wheres as $k => $v) {
            $item = is_array($v) && is_int($k) ? $v : $wheres;
            foreach ($item as $jk => $jv) {
                if (is_int($jk)) {
                    $tempKey     = $item[0];
                    $tempCompare = count($item) === 3 ? $item[1] : '=';
                    $tempValue   = count($item) === 3 ? $item[2] : $item[1];
                } else {
                    $tempKey     = $jk;
                    $tempCompare = '=';
                    $tempValue   = $jv;
                }
                $array[] = [$tempKey, $tempCompare, $tempValue];
                break;
            }
            if (!is_array($v)) {
                break;
            }
        }
        return $array;
    }

    /**
     * @param $key
     * @param $compare
     * @param $value
     * @return $this
     */
    /**
     * @param $key
     * @param $compare
     * @param $value
     * @return $this
     */
    public function whereOr($key, $compare = null, $value = null): static
    {
        if ($value === null) {
            $value   = in_array($compare, self::OPERS) ? $value : $compare;
            $compare = in_array($compare, self::OPERS) ? $compare : '=';
        }
        $this->whereOr[] = is_array($key) ? self::paserWhereArray($key) : [[$key, $compare, $value]];
        return $this;
    }

    /**
     * @param $data
     * @return int
     */
    /**
     * @param $data
     * @return int
     */
    public function insert($data = null): int
    {
        $this->action = 'INSERT';
        $this->data($data);
        $this->paserAndExec();
        return mysqli_affected_rows($this->mysqli);
    }

    /**
     * @param $data
     * @return $this
     */
    /**
     * @param $data
     * @return $this
     */
    public function data($data): static
    {
        if ($data !== null) {
            $this->data = $data;
            foreach ($this->data as $key => $value) {
                unset($this->data[$key]);
                $key              = str_replace('`', '\`', $key);
                $value            = str_replace("\\", "\\\\", $value);
                $value            = str_replace("'", "\'", $value);
                $this->data[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @return \mysqli_result|bool|$this
     */
    protected function paserAndExec(): mysqli_result|bool|static
    {

        if ($this->command && $this->action === '') {
            return $this->execr = $this->mysqli->query($this->command);
        }
        $i = 0;
        switch ($this->action) {
            case 'SELECT':
                $this->command = "SELECT {$this->field} FROM  `{$this->table}` ";
                break;
            case 'DELETE':
                $this->command = "DELETE FROM `{$this->table}` ";
                foreach ($this->data as $key => $value) {
                    $this->command .= "`{$key}` = '{$value}' ";
                    if ($i !== count($this->data) - 1) {
                        $this->command .= ',';
                    }
                    $i++;
                }
                break;
            case 'INSERT':
                $this->command = "INSERT INTO `{$this->table}` ";
                $this->command .= self::parseInsertArray($this->data);
                break;
            case 'UPDATE':
                $this->command = "UPDATE `{$this->table}` SET ";
                foreach ($this->data as $key => $value) {
                    $this->command .= "`{$key}` = '{$value}' ";
                    if ($i !== count($this->data) - 1) {
                        $this->command .= ',';
                    }
                    $i++;
                }
                break;
        }
        for ($i = 0; $i < count($this->where); $i++) {
            for ($j = 0; $j < count($this->where[$i]); $j++) {
                if ($j === 0 && $i === 0) {
                    $this->command .= 'WHERE (';
                } elseif ($j === 0) {
                    $this->command .= 'AND (';
                } else {
                    $this->command .= 'AND ';
                }

                $key           = "`{$this->where[$i][$j][0]}`";
                $compare       = $this->where[$i][$j][1];
                $value         = $this->where[$i][$j][2] ? "'{$this->where[$i][$j][2]}'" : 'NULL';
                $this->command .= "{$key} {$compare} {$value} ";
                if ($j === count($this->where[$i]) - 1) {
                    $this->command .= ') ';
                }
            }
        }

        for ($i = 0; $i < count($this->whereOr); $i++) {
            for ($j = 0; $j < count($this->whereOr[$i]); $j++) {
                if ($j === 0 && $i === 0) {
                    $this->command .= 'OR (';
                } elseif ($j === 0) {
                    $this->command .= 'OR (';
                } else {
                    $this->command .= 'AND ';
                }
                $key           = "`{$this->whereOr[$i][$j][0]}`";
                $compare       = $this->whereOr[$i][$j][1];
                $value         = $this->whereOr[$i][$j][2] ? "'{$this->whereOr[$i][$j][2]}'" : 'NULL';
                $this->command .= "{$key} {$compare} {$value} ";
                if ($j === count($this->whereOr[$i]) - 1) {
                    $this->command .= ') ';
                }
            }
        }

        if ($this->databaseInfo->limit) {
            $this->command .= "LIMIT {$this->databaseInfo->limit}";
        }

        if ($this->order) {
            $this->command .= "ORDER BY {$this->order}";
        }

        Launch::record('sql', $this->command);
        $this->execr = $this->mysqli->query($this->command);
        return $this;
    }

    /**
     * @param $command
     * @return mixed
     */
    public function query($command): mixed
    {
        $this->command = $command;
        $this->paserAndExec();
        return $this->execr;
    }


    /**
     * @param $data
     * @return string
     */
    protected static function parseInsertArray($data): string
    {
        $i      = 0;
        $j      = 0;
        $field  = '';
        $values = '';
        foreach ($data as $key => $value) {
            $item = is_int($key) ? $value : $data;
            foreach ($item as $ik => $iv) {
                $values .= $i === 0 ? '(' : '';
                $values .= "'{$iv}'";
                $values .= $i === count($item) - 1 ? ') ' : ',';
                if ($i !== 0 || $j !== 0) {
                    continue;
                }
                $field .= '(';
                $field .= "`{$ik}`";
                $field .= $i === count($item) - 1 ? ') ' : ',';
                $i++;
            }
            if (!is_int($key)) {
                break;
            }
            $values .= $j === count($data) - 1 ? ';' : ',';
            $j++;
            $i = 0;
        }
        return "{$field} VALUES {$values}";
    }

    /**
     * @param $data
     * @return int
     */
    /**
     * @param $data
     * @return int
     */
    public function update($data = null): int
    {
        $this->action = 'UPDATE';
        $this->data($data);
        $this->paserAndExec();
        return mysqli_affected_rows($this->mysqli);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        $this->action = 'DELETE';
        $this->paserAndExec();
        return mysqli_affected_rows($this->mysqli);
    }

    /**
     * @return array|null
     */
    public function select(): ?array
    {
        $this->action = 'SELECT';
        $this->paserAndExec();
        if ($this->execr) {
            return mysqli_fetch_all($this->execr, MYSQLI_ASSOC);
        }
        return null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $this->action = 'SELECT';
        $this->field  = 'count(*)';
        $this->paserAndExec();
        if ($this->execr) {
            $result = mysqli_fetch_row($this->execr);
            return (int)$result[0];
        }
        return 0;
    }

    /**
     * @return $this
     */
    public function field(): static
    {
        $this->field = '';
        $arguments   = func_get_args();
        while ($item = array_shift($arguments)) {
            $this->field .= "`{$item}`";
            $this->field .= count($arguments) > 0 ? ',' : '';
        }
        return $this;
    }

    /**
     * @param $field
     * @param $sort
     * @return $this
     */
    /**
     * @param $field
     * @param $sort
     * @return $this
     */
    public function order($field, $sort): static
    {
        $this->order = "`{$field}` {$sort}";
        return $this;
    }

    /**
     * @return array|null
     */
    public function first(): ?array
    {
        $this->action = 'SELECT';
        $this->paserAndExec();
        if ($this->execr) {
            $result = mysqli_fetch_assoc($this->execr);
            mysqli_free_result($this->execr);
            return $result;
        }
        return null;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return Mysql
     */
    public function page(int $page, int $limit): static
    {
        return $this->databaseInfo->page($page, $limit);
    }

    /**
     * @param int $start
     * @param int $end
     * @return static
     */
    public function limit(int $start, int $end): static
    {
        return $this->databaseInfo->limit($start, $end);
    }

    /**
     * @param string $command
     * @return array
     */
    public function queryArray(string $command): array
    {
        $this->command = $command;
        $this->paserAndExec();
        if ($this->execr !== false) {
            $result = mysqli_fetch_all($this->execr);
            mysqli_free_result($this->execr);
            return $result;
        }
        return [];
    }
}
