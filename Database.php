<?php

declare(strict_types=1);

class Database extends mysqli
{
    private $host;
    private $db_user;
    private $password;
    private $db_name;
    /** @var Database */
    private $connection;
    /** @var mysqli_result */
    private $result;

    public function __construct($host, $db_user, $password, $db_name) {
        $this->host     = $host;
        $this->db_user  = $db_user;
        $this->password = $password;
        $this->db_name  = $db_name;
        $this->open_connection();
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function getResult(){
        return $this->result;
    }

    public function setResult($result){
        $this->result = $result;
    }

    public function open_connection() {
        $this->connection = new mysqli($this->host, $this->db_user, $this->password, $this->db_name);
        if($this->connection->connect_error) {
            die('Połączenie z bazą danych zakończone niepowodzeniem.' . $this->connection->connect_error);
        }
    }

    public function custom_query($query) {
        if (!$this->result = $this->connection->query($query)) {
            die('Nie można wykonać zapytania do bazy danych.');
        }
        return $this->result;
    }

    public function get_num_rows() {
            return $this->result->num_rows ?? 0;
    }

    public function fetch_result() {
            return $this->getResult()->fetch_assoc() ?? FALSE;
    }

    public function fetch_results() {
            return $this->getResult()->fetch_all() ?? FALSE;
    }

    public function prepare_query($query, array $parameters, $return_affected_rows = 0) {
        $parameters_to_bind = array();
        $types = NULL;

        foreach ($parameters as $attribute => $value) {
            $position = strpos($query, (string) $value);

            if ($position !== FALSE) {
                $query = substr_replace($query, '?', $position, strlen((string) $value));
            }

            $parameters_to_bind[] = &$parameters[$attribute];
            $types .= (is_int($value)) ? 'i' : 's';
            $parameters[$attribute] = $value ?? '';
        }

        $parameters_to_bind = array_merge(array(&$types), $parameters_to_bind);
        $statement = $this->connection->prepare($query);

        if ($statement === FALSE) {
            trigger_error(htmlentities('Błędne zapytanie do bazy: ' . $query . ' Błąd: ' . $this->errno . ' ' . $this->error));
        }

        call_user_func_array(array($statement, 'bind_param'), $parameters_to_bind);
        $statement->execute();

        if ($return_affected_rows) {
            $this->setResult($statement->affected_rows);
        } else {
            $this->setResult($statement->get_result());
        }
    }

    public function insert($table, array $data) {
        $query = "INSERT INTO $table";
        $query_attributes = '(';
        $query_values = 'VALUES (';
        $query_parameters = array();

        foreach ($data as $attribute => $value) {
            $query_attributes .= !isset($i) ? "`$attribute`" : ", `$attribute`";
            $query_values .= !isset($i) ? $value : ", $value";
            $query_parameters["$attribute"] = $value ?? '';
            $i = '';
        }

        $query_attributes .= ')';
        $query_values .= ')';
        $query .= ' ' . $query_attributes . ' ' . $query_values;
        $this->prepare_query($query, $query_parameters, 1);

        return TRUE;
    }

    public function update($table, $condition_attribute, $condition_value, array $data) {
        $query = "UPDATE $table SET";
        $query_condition = "WHERE $condition_attribute = $condition_value";
        $query_attributes = '';
        $query_parameters = array();

        $i = 0;
        foreach ($data as $attribute => $value) {
            $query_attributes .= $i == 0 ? "$attribute = $value" : ", $attribute = $value";
            $query_parameters[$i] = $value ?? '';
            $i++;
        }

        $query_parameters["$condition_attribute"] = $condition_value;
        $query .= ' ' . $query_attributes . ' ' . $query_condition;
        $this->prepare_query($query, $query_parameters, 1);

        return TRUE;
    }

    public function delete($table, $where_attribute, $where_value) {
        $query = "DELETE FROM $table";
        $query_condition = "WHERE $where_attribute = $where_value";
        $query_parameters["$where_attribute"] = $where_value;
        $query .= ' ' . $query_condition;
        $this->prepare_query($query, $query_parameters, 1);

        return TRUE;
    }

    public function select($table, array $attributes, $where_attribute = NULL, $where_value = NULL, $order = NULL, $limit = NULL) {
        $glued_attributes = implode(", ", $attributes);
        $query = "SELECT $glued_attributes FROM $table";
        $query_parameters = array();

        if ($where_attribute != NULL && $where_value != NULL) {
            $query .= ' WHERE ' . $where_attribute . ' = ' . $where_value;
            $query_parameters["$where_attribute"] = $where_value;
        }
        if ($order != NULL) {
            $query .= ' ORDER BY ' . $order;
        }
        if ($limit != NULL) {
            $query .= ' LIMIT ' . $limit;
        }

        $this->prepare_query($query, $query_parameters);

        return TRUE;
    }
}