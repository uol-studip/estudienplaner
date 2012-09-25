<?php

require_once dirname(__file__)."/DBFile.class.php";

class StgFile extends DBFile {
    
    static protected $publicTypes = array();
    
    static public function getByStgProfil($profil_id) {
        $db = DBManager::get();
        $dateien = $db->query(
            "SELECT stg_doku_zuord.doku_id " .
            "FROM stg_doku_zuord " .
            "WHERE stg_doku_zuord.stg_profil_id = ".$db->quote($profil_id)." " .
            "ORDER BY stg_doku_zuord.position DESC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!is_array($dateien)) {
            return false;
        }
        foreach ($dateien as $key => $doku_id) {
            $dateien[$key] = new StgFile($doku_id);
        }
        return $dateien;
    }

    static public function getDokuTypName($doku_typ_id) {
        $db = DBManager::get();
        return $db->query(
            "SELECT name FROM stg_dokument_typ WHERE doku_typ_id = ".$db->quote($doku_typ_id)." ".
        "")->fetch(PDO::FETCH_COLUMN, 0);
    }

    static public function getTypen($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        //wie ist es mit den Sichtbarkeiten ?
        $db = DBManager::get();
        if (PersonalRechte::isRoot()) {
            return $db->query(
                "SELECT stg_dokument_typ.doku_typ_id, IF(stg_bereiche.bereichs_id IS NULL, stg_dokument_typ.name, CONCAT(stg_dokument_typ.name, ' (', GROUP_CONCAT(stg_bereiche.bereich_name SEPARATOR ', '), ')')) AS name " .
                "FROM stg_dokument_typ " .
                    "LEFT JOIN stg_doku_typ_bereich_zuord ON (stg_dokument_typ.doku_typ_id = stg_doku_typ_bereich_zuord.stg_doku_typ_id) " .
                    "LEFT JOIN stg_bereiche ON (stg_doku_typ_bereich_zuord.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                "GROUP BY stg_dokument_typ.doku_typ_id " .
                "ORDER BY stg_dokument_typ.name ASC " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        } elseif(PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
            return $db->query(
                "SELECT stg_dokument_typ.doku_typ_id, IF(stg_bereiche.bereichs_id IS NULL, stg_dokument_typ.name, CONCAT(stg_dokument_typ.name, ' (', GROUP_CONCAT(stg_bereiche.bereich_name SEPARATOR ', '), ')')) AS name " .
                "FROM stg_dokument_typ " .
                    "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_dokument_typ.doku_typ_id = stg_doku_typ_bereich_zuord.stg_doku_typ_id) " .
                    "INNER JOIN stg_bereiche ON (stg_doku_typ_bereich_zuord.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                    "JOIN roles_user " .
                    "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                    "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                    "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
                "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                    "AND plugins.pluginclassname = 'eStudienplaner' " .
                    "AND ( " .
                        "(roles.rolename = 'stg_p-amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                        "OR (roles.rolename = 'stg_i-amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                    ") " .
                "GROUP BY stg_dokument_typ.doku_typ_id " .
                "ORDER BY stg_dokument_typ.name ASC " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $db->query(
                "SELECT stg_dokument_typ.doku_typ_id, IF(stg_bereiche.bereichs_id IS NULL, stg_dokument_typ.name, CONCAT(stg_dokument_typ.name, ' (', GROUP_CONCAT(stg_bereiche.bereich_name SEPARATOR ', '), ')')) AS name " .
                "FROM stg_dokument_typ " .
                    "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_dokument_typ.doku_typ_id = stg_doku_typ_bereich_zuord.stg_doku_typ_id) " .
                    "INNER JOIN stg_bereiche ON (stg_doku_typ_bereich_zuord.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                    "JOIN stg_fsb_rollen " .
                "WHERE stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
                    "AND (" .
                        "(stg_fsb_rollen.rollen_typ = 'FSB' AND stg_bereiche.sichtbar_fsb = '1') " .
                        "OR (stg_fsb_rollen.rollen_typ = 'P-Amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                        "OR (stg_fsb_rollen.rollen_typ = 'I-Amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                    ") " .
                "GROUP BY stg_dokument_typ.doku_typ_id " .
                "ORDER BY stg_dokument_typ.name ASC " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    protected $db_table = 'stg_dokumente';
    
    public function __construct($id = null) {
        parent::__construct($id);
    }

    public function getTyp() {
        $db = DBManager::get();
        return $db->query(
            "SELECT * FROM stg_dokument_typ " .
        "")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function isPublic() {
        if (isset(self::$publicTypes[$this['doku_typ_id']])) {
            return self::$publicTypes[$this['doku_typ_id']];
        }
        $db = DBManager::get();
        return self::$publicTypes[$this['doku_typ_id']] = (bool) $db->query(
            "SELECT COUNT(*) " .
            "FROM stg_bereiche " .
                "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_bereiche.bereichs_id = stg_doku_typ_bereich_zuord.stg_bereichs_id) " .
            "WHERE stg_doku_typ_bereich_zuord.stg_doku_typ_id = ".$db->quote($this['doku_typ_id'])." " .
                "AND ( stg_bereiche.oeffentlich = '1' ".
                    "OR stg_bereiche.oeffentlich IS NULL ) " .
            "GROUP BY stg_bereiche.bereichs_id " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
    }
    
    public function getStudiengaenge() {
        $db = DBManager::get();
        $studiengaenge = $db->query(
            "SELECT stg_profil_id FROM stg_doku_zuord WHERE doku_id = ".$db->quote($this->getId())." "
        )->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($studiengaenge as $key => $stg_profil_id) {
            $studiengaenge[$key] = new StgProfil($stg_profil_id);
        }
        return $studiengaenge;
    }

    public function setStudiengaenge($profile) {
        $db = DBManager::get();
        $studiengaenge = $db->query(
            "DELETE FROM stg_doku_zuord WHERE doku_id = ".$db->quote($this->getId())." "
        );
        foreach ($profile as $key => $stg_profil_id) {
            $db->query(
                "INSERT INTO stg_doku_zuord " .
                "SET doku_id = ".$db->quote($this->getId()).", " .
                    "stg_profil_id = ".$db->quote($stg_profil_id)." " .
            "");
        }
    }

    public function beforeUpload($file_properties) {
        $this['filename'] = $file_properties['filename'];
        $this['filesize'] = $file_properties['filesize'];
    }
    
    protected function getUploadPath() {
        $directory = $GLOBALS['UPLOAD_PATH'].'/stg_dokumente';
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        return $directory.'/'.$this->getId();
    }
    
    /**
     * returns unix-timestamp of last time, this file was edited
     */
    public function getLastDate() {
        return $this->mysqlTimestamp2UnixTimestamp($this['chdate']);
    }
    
    protected function mysqlTimestamp2UnixTimestamp($mtimestamp) {
        return mktime(substr($mtimestamp,8,2),substr($mtimestamp,10,2),substr($mtimestamp,12,2),substr($mtimestamp,4,2),substr($mtimestamp,6,2),substr($mtimestamp,0,4));
    }
    
    public function store()
    {
    	$db = DBManager::get();
        $where_query = $this->getWhereQuery();
        foreach ($this->content as $key => $value) {
            if (is_float($value)) $value = str_replace(',','.',$value);
            if (isset($this->db_fields[$key]) && $key != 'chdate' && $key != 'mkdate'){
                $query_part[] = "`$key` = " . DBManager::get()->quote($value) . " ";
            }
        }
        if ($where_query || $this->isNew()){
            if (!$this->isNew()){
                $query = "UPDATE `{$this->db_table}` SET "
                    . implode(',', $query_part);
                if ($this->db_fields['chdate']) {
                    $query .= " , chdate=CURRENT_TIMESTAMP()";
                }
                $query .= " WHERE ". join(" AND ", $where_query);
                $db->query($query);
            } else {
                $query = "INSERT INTO `{$this->db_table}` SET "
                    . implode(',', $query_part);
                if ($this->db_fields['mkdate']) {
                    $query .= " ,mkdate=CURRENT_TIMESTAMP()";
                }
                if ($this->db_fields['chdate']) {
                    $query .= " , chdate=CURRENT_TIMESTAMP()";
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
    
    
    public function download($attachment) {
        if (!trim($this['quick_link'])) {
            parent::download($attachment);
        } else {
            header("Location: ".$this['quick_link']);
        }
    }
    
    public function getSize($precision = 2) {
        $base = log($this['filesize']) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)]."B";
    }
    
    public function getTags() {
        $db = DBManager::get();
        return $db->query("SELECT tag FROM stg_dokument_tags WHERE doku_id = ".$db->quote($this->getId()))->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    public function setTags($text) {
        $db = DBManager::get();
        $db->query("DELETE FROM stg_dokument_tags WHERE doku_id = ".$db->quote($this->getId()));
        $tags = preg_split("/[\s,!\.;\?\*\+<>]+/", $text, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($tags as $tag) {
            $db->query("INSERT IGNORE INTO stg_dokument_tags SET doku_id = ".$db->quote($this->getId()).", tag = ".$db->quote($tag)."");
        }
    }

    public function delete() {
        if ($this->is_deletable()) {
            $db = DBManager::get();
            $db->exec("DELETE FROM stg_doku_zuord WHERE doku_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_dokument_tags WHERE doku_id = ".$db->quote($this->getId()));
            return parent::delete();
        } else {
            return false;
        }
    }

    public function is_deletable() {
        return true;
    }
}

