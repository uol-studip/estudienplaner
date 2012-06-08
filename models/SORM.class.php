<?php

require_once "lib/classes/SimpleORMap.class.php";

/**
 * Extension of SimpleORMap for tables that don't have md5-hashes as IDs, but
 * INT-IDs with auto_increment attribute.
 * Also allows chaining for most methods.
 * @author Rasmus Fuhse <fuhse@data-quest.de>
 *
 */

class SORM extends SimpleORMap {

    static protected $trigger_function = array();

    public function setValue($field, $value) {
        parent::setValue($field, $value);
        return $this;
    }
    public function setNew($is_new) {
        parent::setNew($is_new);
        return $this;
    }
    public function restore() {
        parent::restore();
        return $this;
    }
    public function triggerChdate() {
        parent::triggerChdate();
        return $this;
    }
    
    public function getNewId() {
        return null;
    }
    
    /**
     * had to be overwritten for storing new items correctly
     */
    public function store()
    {
    	$db = DBManager::get();
        $where_query = $this->getWhereQuery();
        foreach ($this->content as $key => $value) {
            if (is_float($value)) $value = str_replace(',','.',$value);
            if (isset($this->db_fields[$key]) && $key != 'chdate' && $key != 'mkdate') {
                $query_part[] = "`$key` = " . DBManager::get()->quote($value) . " ";
            }
        }
        if ($where_query || $this->isNew()) {
            if (!$this->isNew()){
                $query = "UPDATE `{$this->db_table}` SET "
                    . implode(',', $query_part);
                if ($this->db_fields['chdate']) {
                    $query .= " , chdate=UNIX_TIMESTAMP()";
                }
                $query .= " WHERE ". join(" AND ", $where_query);
                $ret = $db->exec($query);
            } else {
                $query = "INSERT INTO `{$this->db_table}` SET "
                    . implode(',', $query_part);
                if ($this->db_fields['mkdate']) {
                    $query .= " ,mkdate=UNIX_TIMESTAMP()";
                }
                if ($this->db_fields['chdate']) {
                    $query .= " , chdate=UNIX_TIMESTAMP()";
                }
                $ret = $db->exec($query);
                $this->setId($db->query("SELECT LAST_INSERT_ID()")->fetch(PDO::FETCH_COLUMN, 0));
            }
            $this->restore();
            return $ret;
        } else {
            return false;
        }
    }

    public function is_deletable() {
        return false;
    }
    
    

    protected function trigger($field = null) {
        //Spezialfunktionen für Uni-Oldenburg oder ähnliches:
        foreach (self::$trigger_function as $trigger) {
            if ($trigger[0] === $field) {
                call_user_func($trigger[1], $this);
            }
        }
    }

    static function addTrigger($fieldname, $functionname) {
        self::$trigger_function[] = array($fieldname, $functionname);
    }

    static function clearTrigger() {
        self::$trigger_function = array();
    }

}