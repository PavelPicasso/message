<?php

class Query_builder {

    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $this->mysqli->set_charset("utf8");
        if ($this->mysqli->connect_errno) {
            echo "Не удалось подключиться к mysqli: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error . "</br>";
        }
    }

    public function select($table, $columns = null, $where = null) {
        $sql = 'SELECT ';
        if ($columns != null) {
            $sql .= $columns . ' ';
        } else {
            $sql .= '* ';
        }
        if (isset($table)) {
            $sql .= 'FROM ' . $table . ' ';
        } else {
            return 'err: нет название таблицы';
        }
        if ($where != null) {
            $sql .= 'WHERE ' . $where . ' ';
        } else {
            $sql .= '';
        }
        $query = $this->mysqli->query($sql);
        return $sql = $query ? $query->fetch_all() : false;
    }

    public function insert($table, $array) {
        $sql = "INSERT INTO  $table (";
        $i = 0;
        foreach ($array as $name => $value)
            $sql .= ($i++ ? "," : "") . "$name";
        $sql .= ") VALUES (";
        $i = 0;
        foreach ($array as $name => $value)
            $sql .= ($i++ ? "," : "") . "'{$value}'";
        $sql .= ");";
        $query = $this->mysqli->query($sql);
        return $sql = $query ? true : false;
    }

    public function update($table, $array, $where = null) {
        $querySET = '';
        foreach ($array as $name => $value) {
            if ($querySET == '') {
                $querySET = "`$name` = '" . $value . "'";
            } else {
                $querySET = $querySET . ',' . "`$name` = '" . $value . "'";
            }
        }
        if ($where != null) {
            $sql = "UPDATE $table SET $querySET WHERE $where";
        } else {
            $sql = "UPDATE $table SET $querySET";
        }
        $query = $this->mysqli->query($sql);
        return $sql = $query ? true : false;
    }

    public function delete($table, $where = null) {
        if ($where != null) {
            $sql = "DELETE FROM " . $table . " WHERE $where";
        } else {
            $sql = "DELETE FROM " . $table;
        }
        $query = $this->mysqli->query($sql);
        return $sql = $query ? true : false;
    }
}

