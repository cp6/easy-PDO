<?php

class easyPDO
{
    protected object $db;

    public function __construct(bool $emulate_prepares = false, bool $persistent = true)
    {
        $this->db = $this->db_connect($emulate_prepares, $persistent);
    }

    protected function db_connect(bool $emulate_prepares = false, bool $persistent = true): object
    {
        $db_host = '127.0.0.1';
        $db_name = 'my_photos';
        $db_user = 'root';
        $db_password = '';
        $options = array(
            PDO::ATTR_PERSISTENT => $persistent,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => $emulate_prepares
        );
        return new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password, $options);
    }

    public function truncateTable(string $table): bool
    {
        $call = $this->db->prepare("TRUNCATE TABLE ?;");
        return $call->execute([$table]);
    }

    protected function deletePrepare(string $table, array $where_cols = [], array $where_conditions = [], array $limit = [], array $between = []): string
    {
        $where_counter = 0;
        $str = "DELETE FROM `$table` ";
        $where_total = count($where_cols);
        if (!empty($between)) {
            $str .= "WHERE `$between[0]` BETWEEN '$between[1]' AND '$between[2]' AND ";
        } else {
            if ($where_total > 0) {
                $str .= "WHERE ";
            }
        }
        foreach ($where_cols as $where) {
            $where_counter++;
            if ($where_counter == $where_total) {
                $str .= "`$where`";
                (empty($where_conditions)) ? $str .= " = ?" : $str .= " {$where_conditions[($where_counter - 1)]} ?";
            } else {
                $str .= "`$where`";
                (empty($where_conditions)) ? $str .= " = ? AND " : $str .= " {$where_conditions[($where_counter - 1)]} ? AND ";
            }
        }
        if (!empty($limit))
            (isset($limit[1])) ? $str .= " LIMIT $limit[0],$limit[1];" : $str .= " LIMIT $limit[0];";
        return $str;
    }

    protected function selectPrepare(string $table, array $select_cols = [], array $where_cols = [], array $where_conditions = [], array $order_by = [], array $limit = [], array $between = []): string
    {
        $cols_counter = $where_counter = 0;
        $str = "SELECT ";
        $cols_total = count($select_cols);
        if ($cols_total == 0 || $select_cols[0] == '*') {
            $str .= "*";
        } else {
            foreach ($select_cols as $col) {
                $cols_counter++;
                ($cols_counter == $cols_total) ? $str .= "`$col`" : $str .= "`$col`,";
            }
        }
        $str .= " FROM `$table` ";
        $where_total = count($where_cols);
        if (!empty($between)) {
            $str .= "WHERE `$between[0]` BETWEEN '$between[1]' AND '$between[2]' AND ";
        } else {
            if ($where_total > 0) {
                $str .= "WHERE ";
            }
        }
        foreach ($where_cols as $where) {
            $where_counter++;
            if ($where_counter == $where_total) {
                $str .= "`$where`";
                (empty($where_conditions)) ? $str .= " = ?" : $str .= " {$where_conditions[($where_counter - 1)]} ?";
            } else {
                $str .= "`$where`";
                (empty($where_conditions)) ? $str .= " = ? AND " : $str .= " {$where_conditions[($where_counter - 1)]} ? AND ";
            }
        }
        if (!empty($order_by))
            $str .= " ORDER BY `$order_by[0]` $order_by[1]";
        if (!empty($limit))
            (isset($limit[1])) ? $str .= " LIMIT $limit[0],$limit[1];" : $str .= " LIMIT $limit[0];";
        return $str;
    }

    protected function updatePrepare(string $table, array $update_cols, array $where_cols, array $where_conditions = [], array $where_values = [], int $limit = 0): string
    {
        $cols_counter = $where_counter = 0;
        $str = "UPDATE `$table` SET ";
        $cols_total = count($update_cols);
        foreach ($update_cols as $col) {
            $cols_counter++;
            ($cols_counter == $cols_total) ? $str .= "`$col` = ?" : $str .= "`$col` = ?, ";
        }
        $str .= " WHERE ";
        $where_total = count($where_cols);
        foreach ($where_cols as $where) {
            $where_counter++;
            if ($where_counter == $where_total) {
                $str .= "`$where`";
                (empty($where_conditions)) ? $str .= " = ?" : $str .= " {$where_conditions[($where_counter - 1)]} ?";
            } else {
                $str .= "`$where`";
                (empty($where_conditions)) ? $str .= " = ? AND " : $str .= " {$where_conditions[($where_counter - 1)]} ? AND ";
            }
        }
        if ($limit > 0)
            $str .= " LIMIT $limit;";
        return $str;
    }

    public function updateDB(string $table, array $update_cols, array $update_values, array $where_cols = [], array $where_conditions = [], array $where_values = [], int $limit = 0)
    {
        $call = $this->db->prepare($this->updatePrepare($table, $update_cols, $where_cols, $where_conditions, $where_values, $limit));
        return $call->execute($this->inputParams($update_values, $where_values));
    }

    protected function insertPrepare(string $table, array $insert_cols, bool $ignore = false): string
    {
        ($ignore) ? $ig = " IGNORE" : $ig = "";
        $cols_counter = $counter = 0;
        $str = "INSERT{$ig} INTO `$table` (";
        $cols_total = count($insert_cols);
        foreach ($insert_cols as $col) {
            $cols_counter++;
            ($cols_counter == $cols_total) ? $str .= "`$col`) " : $str .= "`$col`,";
        }
        $str .= "VALUES (";
        foreach ($insert_cols as $col) {
            $counter++;
            if ($counter == $cols_total) {
                $str .= "?);";
            } else {
                $str .= "?,";
            }
        }
        return $str;
    }

    protected function insertDuplicatePrepare(string $table, array $insert_cols): string
    {
        $cols_counter = $counter = 0;
        $str = "INSERT INTO `$table` (";
        $cols_total = count($insert_cols);
        foreach ($insert_cols as $col) {
            $cols_counter++;
            ($cols_counter == $cols_total) ? $str .= "`$col`) " : $str .= "`$col`,";
        }
        $str .= "VALUES (";
        foreach ($insert_cols as $col) {
            $counter++;
            if ($counter == $cols_total) {
                $str .= ":$col)";
            } else {
                $str .= ":$col, ";
            }
        }
        $str .= " ON DUPLICATE KEY UPDATE ";
        $counter = 0;
        foreach ($insert_cols as $col) {
            $counter++;
            if ($counter == $cols_total) {
                $str .= "`$col` = :{$col}1;";
            } else {
                $str .= "`$col` = :{$col}1, ";
            }
        }
        return $str;
    }

    public function insertDB(string $table, array $insert_cols, array $insert_values, bool $duplicate_update = false, bool $return_id = false, bool $ignore = false)
    {
        if ($duplicate_update) {
            $call = $this->db->prepare($this->insertDuplicatePrepare($table, $insert_cols));
            return $call->execute($this->inputParamsNamed($insert_cols, $insert_values));
        } else {
            $call = $this->db->prepare($this->insertPrepare($table, $insert_cols, $ignore));
            if ($return_id) {
                return $call->lastInsertId();
            } else {
                return $call->execute($this->inputParams($insert_values));
            }
        }
    }

    protected function inputParams(array $values, array $values2 = []): array
    {
        $input_array = array();
        if (!empty($values2)) {
            $values = array_merge($values, $values2);
        }
        for ($i = 0; $i <= count($values) - 1; $i++) {
            $input_array[] = $values[$i];
        }
        return $input_array;//eg: ['value', 'value2', 'value3']
    }

    protected function inputParamsNamed(array $names, array $values): array
    {
        $input_array = array();
        for ($i = 0; $i <= count($values) - 1; $i++) {
            $input_array[":$names[$i]"] = $values[$i];
            $input_array[":$names[$i]1"] = $values[$i];
        }
        return $input_array;//eg: {"id":"102","name":"Tony","score":"458","age":"40"}
    }

    public function deleteDB(string $table, array $where_cols = [], array $where_conditions = [], array $where_values = [], array $limit = [], array $between = []): array
    {
        $call = $this->db->prepare($this->deletePrepare($table, $where_cols, $where_conditions, $limit, $between));
        $call->execute($this->inputParams($where_values));
        return $call->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectFetchAll(string $table, array $select_cols, array $where_cols, array $where_values, array $where_conditions = [], array $order_by = [], array $limit = [], array $between = []): array
    {//All as an array
        $call = $this->db->prepare($this->selectPrepare($table, $select_cols, $where_cols, $where_conditions, $order_by, $limit, $between));
        $call->execute($this->inputParams($where_values));
        return $call->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectFetchCol(string $table, array $select_col, array $where_cols, array $where_values, array $where_conditions = [], array $order_by = [], array $limit = [], array $between = []): string
    {//One column as a string
        $call = $this->db->prepare($this->selectPrepare($table, $select_col, $where_cols, $where_conditions, $order_by, $limit, $between));
        $call->execute($this->inputParams($where_values));
        return $call->fetchColumn();
    }

    public function selectFetch(string $table, array $select_cols, array $where_cols, array $where_values, array $where_conditions = [], array $order_by = [], array $limit = [], array $between = []): array
    {//One row as an array
        $call = $this->db->prepare($this->selectPrepare($table, $select_cols, $where_cols, $where_conditions, $order_by, $limit, $between));
        $call->execute($this->inputParams($where_values));
        return $call->fetch(PDO::FETCH_ASSOC);
    }
}
