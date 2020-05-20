<?php
class DB
{
    public $instance = null;
    public $dbh;
    public $lastQuery;
    public $error;

    public function __construct()
    {
        // private  to stop new
        $this->dbh = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE) or die("Could not connect : " . mysql_error());

    }

    public function __clone()
    { // private to stop clone
    }

    public function dbh()
    {return $this->dbh;}

    public function quote($data, $type = 'varchar')
    {
        return in_array($type, array
            (
                'varchar',
                'char',
                'date',
                'datetime',
                'blob',
                'mediumblob',
                'text',
                'mediumtext',
            )) ? "'" . mysqli_real_escape_string($this->dbh, $data) . "'" : $data;}

    public function execute($sql)
    {
        $this->error = false;
        $this->lastQuery = $sql;
        flashDebug($sql);
        $result = mysqli_query($this->dbh, $sql);

        if (!$result) {
            $this->error = 'Database error: ' . mysqli_error($this->dbh);
        }
        return $result;
    }
    /**
     * @desc make a database query
     *  returns insert_id if INSERT , affected_rows if UPDATE , result ID if SELECT
     *
     */
    public function query($sql)
    {
        $this->error = false;
        $this->lastQuery = $sql;
        flashDebug($sql);
        $sql = trim($sql);

        $result = mysqli_query($this->dbh, $sql);
        if ($result) {
            // no query errors
            if (preg_match("/^\binsert\b\s+/i", $sql)) {
                return mysqli_insert_id($this->dbh);
            } elseif (preg_match("/^\b(update|delete|replace)\b\s+/i", $sql)) {
                return mysqli_affected_rows($this->dbh);
            } else {
                // if query returns data
                if (mysqli_num_rows($result)) {
                    return $result;
                } else {
                    return false;
                }
            }
        } else {
            // query failed

            $this->error = 'Database error: ' . mysqli_error($this->dbh);
            //pr($this->error);exit;
            return -1; //cant use false as this is the same result as no rows returned from query
        }
    }

    public function ajaxQuery($sql)
    {
        if (!empty($sql)) {
            if ($query = $this->query($sql)) {
                while ($row = mysqli_fetch_assoc($query)) {
                    $result[] = $row;
                }
                return array
                    (
                    'replyCode' => '200',
                    'replyText' => 'Ok',
                    'data' => $result,
                );
            } else {
                return array
                    (
                    'replyCode' => '500',
                    'replyText' => $this->error . " SQL=" . $sql,
                    'data' => array(),
                );
            }
        }
    }

    /**
     * @desc return a multirow result set as assoc array
     */
    public function fetchRows($sql, $key = '')
    {
        if (!empty($sql)) {
            $result = array();
            $query = $this->query($sql);

            if (!$this->error) {
                if ($query) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        if (empty($key) || !isset($row[$key])) {
                            $result[] = $row;
                        } else {

                            $result[$row[$key]] = $row;
                        }
                    }
                }

                return $result;
            } else {
                flashError($this->error);
                flashError($this->lastQuery);
                return false;
            }
        }
    }
    /**
     * @desc return a single result set as assoc array
     */
    public function fetchRow($sql)
    {
        if (!empty($sql)) {
            $result = array();
            $query = $this->query($sql);

            if ($query > -1) {
                $result = mysqli_fetch_assoc($query);
            }
            return $result;
        }

        return -1;
    }

    public function fetchRecordById($table, $id)
    {
        $sql = "select * from " . $table . " where id =" . $id;
        return $this->fetchRow($sql);
    }

    public function updateRecord($table, $data, $where = '')
    {$this->query(update_string($table, $data, $where));}

    public function insertRecord($table, $data, $key = 'id')
    {
        unset($data[$key]);
        $sql = insert_string($table, $data);
        $insertId = $this->query($sql);
        return $insertId;
    }

    public function deleteRecord($table, $id)
    {
        if ($id) {
            $sql = "delete from " . $table . " where id=" . $id . " limit 1";
            $this->query($sql);
        }
    }
    /**
     * @desc used to execute a srting with multiple sql statements
     *  useful for modules that read their setup sql from a file
     */
    public function multiQuery($multipleQuerys)
    {
        $result = true;
        $querys = split(";", $multipleQuerys);

        if (is_array($querys) && count($querys)) {
            $sql = '';

            foreach ($querys as $query) {
                $sql .= rtrim($query);

                if (!empty($sql)) {
                    if (!$this->execute($sql)) {
                        flashError($this->error . '<br />QUERY=' . $this->lastQuery);
                        $result = false;
                    }
                    $sql = "";
                }
            }
        }
        return $result;
    }

    // end of class
}
