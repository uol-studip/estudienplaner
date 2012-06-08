<?php
require_once dirname(__file__)."/SORM.class.php";

class StgFachkombination extends SORM {
    protected $db_table = 'stg_fach_kombination';
    
    static public function findUsedStudiengaenge() {
        $db = DBManager::get();
        return $db->query(
            "SELECT DISTINCT studiengaenge.studiengang_id " .
            "FROM studiengaenge " .
                "INNER JOIN stg_profil ON (stg_profil.fach_id = studiengaenge.studiengang_id) " .
                "INNER JOIN stg_fach_kombination ON (stg_fach_kombination.stg_profil_id = stg_profil.profil_id OR stg_fach_kombination.kombi_stg_profil_id = stg_profil.profil_id) " .
            "ORDER BY studiengaenge.name ASC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    public function store() {
        $db = DBManager::get();
        $exists = $db->query(
            "SELECT fach_kombi_id FROM stg_fach_kombination " .
            "WHERE ( " .
                "(stg_profil_id = ".$db->quote($this["stg_profil_id"])." AND kombi_stg_profil_id = ".$db->quote($this["kombi_stg_profil_id"]).")".
                "OR (stg_profil_id = ".$db->quote($this["kombi_stg_profil_id"])." AND kombi_stg_profil_id = ".$db->quote($this["stg_profil_id"]).") " .
            ") " .
            ($this->isNew() ? "" : "AND fach_kombi_id != ".$db->quote($this->getId())." ") .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if ($exists === false) {
            return parent::store();
        } else {
            return false;
        }
    }
    
    public function getName() {
        $db = DBManager::get();
        $name1 = StgProfil::getName($this['stg_profil_id']);
        $name2 = StgProfil::getName($this['kombi_stg_profil_id']);
        return $name1." / ".$name2;
    }
}