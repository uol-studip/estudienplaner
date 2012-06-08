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
                "INNER JOIN stg_profil ON (stg_profil.fach_id = studiengaenge.studiengang_id) " .
                "JOIN stg_fach_kombination ON (stg_fach_kombination.stg_profil_id = stg_profil.profil_id OR stg_fach_kombination.kombi_stg_profil_id = stg_profil.profil_id) " .
                "INNER JOIN stg_verlaufsplan ON (stg_verlaufsplan.stg_profil_id = stg_profil.profil_id OR stg_verlaufsplan.fach_kombi_id = stg_fach_kombination.fach_kombi_id) " .
            "ORDER BY studiengaenge.name ASC " .
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