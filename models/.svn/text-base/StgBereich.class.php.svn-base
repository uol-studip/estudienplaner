<?php

require_once dirname(__file__)."/SORM.class.php";

class StgBereich extends SORM {
    protected $db_table = 'stg_bereiche';
    
    static public function getBereiche() {
        $db = DBManager::get();
        $bereiche = $db->query(
            "SELECT stg_bereiche.bereichs_id " .
            "FROM stg_bereiche " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($bereiche as $key => $bereichs_id) {
            $bereiche[$key] = new StgBereich($bereichs_id);
        }
        return $bereiche;
    }
    
    static public function getAlleDokumentTypen() {
        $db = DBManager::get();
        return $db->query(
            "SELECT stg_dokument_typ.doku_typ_id, stg_dokument_typ.name " .
            "FROM stg_dokument_typ " .
        "")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDokumentTypen() {
        $db = DBManager::get();
        return $db->query(
            "SELECT DISTINCT stg_dokument_typ.doku_typ_id " .
            "FROM stg_dokument_typ " .
                "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_dokument_typ.doku_typ_id = stg_doku_typ_bereich_zuord.stg_doku_typ_id) " .
            "WHERE stg_doku_typ_bereich_zuord.stg_bereichs_id = ".$db->quote($this->getId())." " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    public function setDokumentTypen($dokument_typen) {
        $db = DBManager::get();
        $db->exec(
            "DELETE FROM stg_doku_typ_bereich_zuord " .
            "WHERE stg_bereichs_id = ".$db->quote($this->getId())." " .
        "");
        foreach ($dokument_typen as $stg_dokument_typ_id) {
            $db->exec(
                "INSERT IGNORE INTO stg_doku_typ_bereich_zuord " .
                "SET stg_bereichs_id = ".$db->quote($this->getId()).", " .
                    "stg_doku_typ_id = ".$db->quote($stg_dokument_typ_id)." " .
            "");
        }
    }
    static public function createDokumentTyp($dokument_typ) {
        $db = DBManager::get();
        $db->exec(
            "INSERT IGNORE INTO stg_dokument_typ " .
            "SET name = ".$db->quote($dokument_typ)." " .
        "");
        return $db->query("SELECT LAST_INSERT_ID()")->fetch(PDO::FETCH_COLUMN, 0);
    }
    
    static public function isDokumententypDeletable($id) {
        $db = DBManager::get();
        $count_dependencies = $db->query(
            "SELECT COUNT(*) FROM stg_dokumente WHERE doku_typ_id = ".$db->quote($id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        return ($count_dependencies === '0');
    }

    static public function deleteDokumentTyp($dokument_typ_id) {
        if (self::isDokumententypDeletable($dokument_typ_id)) {
            $db = DBManager::get();
            $db->exec(
                "DELETE FROM stg_dokument_typ " .
                "WHERE doku_typ_id = ".$db->quote($dokument_typ_id)." " .
            "");
            $db->exec(
                "DELETE FROM stg_doku_typ_bereich_zuord " .
                "WHERE stg_doku_typ_id = ".$db->quote($dokument_typ_id)." " .
            "");
            return true;
        }
        return false;
    }
    
    
    public function getAnsprechpartnerTypen() {
        $db = DBManager::get();
        return $db->query(
            "SELECT stg_ansprechpartner_typ.* " .
            "FROM stg_ansprechpartner_typ " .
            "WHERE stg_bereichs_id = ".$db->quote($this->getId())." " .
        "")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addAnsprechpartnerTyp($ansprechpartner_typ_name) {
        $db = DBManager::get();
        $exists = $db->query(
            "SELECT ansprechpartner_typ_id " .
            "FROM stg_ansprechpartner_typ " .
            "WHERE name = ".$db->quote($ansprechpartner_typ_name)." " .
                "AND stg_bereichs_id = ".$db->quote($this->getId())." " .
        "")->fetch();
        if (!$exists) {
            $db->exec(
                "INSERT IGNORE INTO stg_ansprechpartner_typ " .
                "SET name = ".$db->quote($ansprechpartner_typ_name).", " .
                    "stg_bereichs_id = ".$db->quote($this->getId())." " .
            "");
            print $db->query("SELECT LAST_INSERT_ID()")->fetch(PDO::FETCH_COLUMN, 0);
        }
    }

    public function isAnsprechpartnerDeletable($id) {
        $db = DBManager::get();
        $count_dependencies = $db->query(
            "SELECT COUNT(*) FROM stg_ansprechpartner WHERE ansprechpartner_typ_id = ".$db->quote($id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        return ($count_dependencies === '0');
    }

    public function deleteAnsprechpartnerTyp($id) {
        $db = DBManager::get();
        if ($this->isAnsprechpartnerDeletable($id)) {
            $db->exec(
                "DELETE FROM stg_ansprechpartner_typ " .
                "WHERE ansprechpartner_typ_id = ".$db->quote($id)." " .
                    "AND stg_bereichs_id = ".$db->quote($this->getId())." " .
            "");
        }
    }

    public function delete() {
        if ($this->is_deletable()) {
            $db = DBManager::get();
            $db->exec("DELETE FROM stg_ansprechpartner_typ WHERE stg_bereichs_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_doku_typ_bereich_zuord WHERE stg_bereichs_id = ".$db->quote($this->getId()));
            return parent::delete();
        } else {
            return false;
        }
    }

    public function is_deletable() {
        $db = DBManager::get();
        $dependencies = $db->query(
            "SELECT * " .
            "FROM stg_ansprechpartner " .
                "INNER JOIN stg_ansprechpartner_typ ON (stg_ansprechpartner.ansprechpartner_typ_id = stg_ansprechpartner_typ.ansprechpartner_typ_id) " .
            "WHERE stg_ansprechpartner_typ.stg_bereichs_id = ".$db->quote($this->getId())." " .
        "")->fetch();
        return $dependencies ? false : true;
    }
}