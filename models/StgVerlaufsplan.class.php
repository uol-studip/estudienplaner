<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__file__)."/SORM.class.php";

class StgVerlaufsplan extends SORM {
    protected $db_table = "stg_verlaufsplan";
    protected static $profile = array();

    static public function findUsedStudiengaenge() {
        $db = DBManager::get();
        return $db->query(
            "SELECT DISTINCT studiengaenge.studiengang_id " .
            "FROM studiengaenge " .
                "INNER JOIN stg_profil AS p ON (p.fach_id = studiengaenge.studiengang_id) " .
                "LEFT JOIN stg_fach_kombination AS k ON (k.stg_profil_id = p.profil_id " .
                    "OR k.kombi_stg_profil_id = p.profil_id " .
                ") " .
                "INNER JOIN stg_verlaufsplan AS v ON (v.stg_profil_id = p.profil_id " .
                    "OR v.fach_kombi_id = k.fach_kombi_id " .
                ") " .
            "ORDER BY studiengaenge.name ASC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    static public function findUsedAbschluesse() {
        $db = DBManager::get();
        return $db->query(
            "SELECT DISTINCT abschluss.abschluss_id " .
            "FROM abschluss " .
                "INNER JOIN stg_profil AS p ON (p.abschluss_id = abschluss.abschluss_id) " .
                "LEFT JOIN stg_fach_kombination AS k ON (k.stg_profil_id = p.profil_id " .
                    "OR k.kombi_stg_profil_id = p.profil_id " .
                ") " .
                "INNER JOIN stg_verlaufsplan AS v ON (v.stg_profil_id = p.profil_id " .
                    "OR v.fach_kombi_id = k.fach_kombi_id " .
                ") " .
            "ORDER BY abschluss.name ASC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    public function isVisible() {
        return $this['sichtbar_fach1'] && ($this['fach_kombi_id'] ? $this['sichtbar_fach2'] : true);
    }

    public function delete() {
        $id = $this->getId();
        if (parent::delete()) {
            $db = DBManager::get();
            $db->exec("DELETE FROM stg_verlaufsplan_eintraege WHERE stg_verlaufsplan_id = ".$db->quote($id)." ");
        }
    }

    public function is_deletable() {
        return true;
    }

    public function getProfile() {
        if (isset(self::$profile[$this->getId()])) {
            return self::$profile[$this->getId()];
        }
        if ($this['fach_kombi_id']) {
            $db = DBManager::get();
            $kombo = $db->query("SELECT * FROM stg_fach_kombination WHERE fach_kombi_id = ".$db->quote($this['fach_kombi_id'])." ")->fetch(PDO::FETCH_ASSOC);
            $profil1 = new StgProfil($kombo['stg_profil_id']);
            $profil2 = new StgProfil($kombo['kombi_stg_profil_id']);
            return self::$profile[$this->getId()] = array($profil1, $profil2);
        } elseif($this['stg_profil_id']) {
            return self::$profile[$this->getId()] = array(new StgProfil($this['stg_profil_id']));
        } else {
            return array();
        }
    }

}