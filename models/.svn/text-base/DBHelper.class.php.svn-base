<?php

class DBHelper {
    static $enumOptions = array();
    public static function getEnumOptions($table, $field) {
        if (isset(self::$enumOptions[$table][$field])) {
            return self::$enumOptions[$table][$field];
        } else {
            $db = DBManager::get();
            $fieldType = $db->query("SHOW COLUMNS " .
                                    "FROM `".addslashes($table)."` " .
                                    "WHERE Field = ".$db->quote($field)." ")->fetch();
            preg_match_all("/'([\w\s\-]+)'/", $fieldType['Type'], $options);
            self::$enumOptions[$table][$field] = array();
            foreach ($options[1] as $option) {
                self::$enumOptions[$table][$field][] = $option;
            }
            return self::$enumOptions[$table][$field];
        }
    }
}