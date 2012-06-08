<?php

require_once dirname(__file__)."/SORM.class.php";
require_once "lib/classes/Avatar.class.php";
require_once "lib/classes/InstituteAvatar.class.php";
require_once "lib/classes/searchtypes/SQLSearch.class.php";
require_once dirname(__file__)."/StgProfil.class.php";
require_once dirname(__file__)."/PersonalRechte.php";

class StgAnsprechpartner extends SORM {
    protected $db_table = 'stg_ansprechpartner';
    
    static protected $infos = array();
    static protected $publicTypes = array();
    
    static public function getByStgProfil($profil_id) {
        $db = DBManager::get();
        $ansprechpartner = $db->query(
            "SELECT stg_ansprech_zuord.stg_ansprechpartner_id " .
            "FROM stg_ansprech_zuord " .
                "INNER JOIN stg_ansprechpartner ON (stg_ansprech_zuord.stg_ansprechpartner_id = stg_ansprechpartner.ansprechpartner_id) " .
            "WHERE stg_ansprech_zuord.stg_profil_id = ".$db->quote($profil_id)." " .
            "ORDER BY stg_ansprech_zuord.position DESC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!is_array($ansprechpartner)) {
            return false;
        }
        foreach ($ansprechpartner as $key => $ansprechpartner_id) {
            $ansprechpartner[$key] = new StgAnsprechpartner($ansprechpartner_id);
        }
        return $ansprechpartner;
    }

    static public function getAnsprechpartnerIdentitaetSuche() {
        return new SQLSearch(
            "SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname, ' (', auth_user_md5.perms, ')') " .
            "FROM auth_user_md5 " .
            "WHERE :range_typ = 'auth_user_md5' " .
                "AND CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname, ' (', auth_user_md5.username, ')') LIKE :input " .
            "UNION SELECT Institute.Institut_id, Institute.Name " .
            "FROM Institute " .
            "WHERE :range_typ = 'institute' " .
                "AND Institute.Name LIKE :input " .
        "", _("Ansprechpartner suchen"));
    }

    static public function getAnsprechpartnerTypName($typ_id) {
        $db = DBManager::get();
        return $db->query(
            "SELECT name FROM stg_ansprechpartner_typ WHERE ansprechpartner_typ_id = ".$db->quote($typ_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
    }

    static public function getAnsprechpartnerTypen($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        //wie ist es mit den Sichtbarkeiten ?
        $db = DBManager::get();
        if (PersonalRechte::isRoot()) {
            return $db->query(
                "SELECT DISTINCT ansprechpartner_typ_id, CONCAT(stg_ansprechpartner_typ.name, ' (', stg_bereiche.bereich_name,  ')') AS name " .
                "FROM stg_ansprechpartner_typ " .
                    "INNER JOIN stg_bereiche ON (stg_ansprechpartner_typ.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                "ORDER BY stg_ansprechpartner_typ.name ASC " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        } elseif(PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
            return $db->query(
                "SELECT DISTINCT ansprechpartner_typ_id, CONCAT(stg_ansprechpartner_typ.name, ' (', stg_bereiche.bereich_name,  ')') AS name " .
                "FROM stg_ansprechpartner_typ " .
                    "INNER JOIN stg_bereiche ON (stg_ansprechpartner_typ.stg_bereichs_id = stg_bereiche.bereichs_id) " .
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
                "ORDER BY name ASC " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $db->query(
                "SELECT DISTINCT ansprechpartner_typ_id, CONCAT(stg_ansprechpartner_typ.name, ' (', stg_bereiche.bereich_name,  ')') AS name " .
                "FROM stg_ansprechpartner_typ " .
                    "INNER JOIN stg_bereiche ON (stg_ansprechpartner_typ.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                    "JOIN stg_fsb_rollen " .
                "WHERE stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
                    "AND (" .
                        "(stg_fsb_rollen.rollen_typ = 'FSB' AND stg_bereiche.sichtbar_fsb = '1') " .
                        "OR (stg_fsb_rollen.rollen_typ = 'P-Amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                        "OR (stg_fsb_rollen.rollen_typ = 'I-Amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                    ") " .
                "ORDER BY name ASC " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    static public function get($ansprechpartner_id) {
        return new StgAnsprechpartner($ansprechpartner_id);
    }
    
    public function __construct($ansprechpartner_id = null) {
        //self::expireTableScheme();
        parent::__construct($ansprechpartner_id);
    }
    
    public function getStgProfile() {
        $db = DBManager::get();
        return $db->query("SELECT stg_profil_id " .
                          "FROM stg_ansprech_zuord " .
                          "WHERE stg_ansprechpartner_id = ".$db->quote($this->getId())." "
        )->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    public function getTyp() {
        $db = DBManager::get();
        return $db->query("SELECT name " .
                          "FROM stg_ansprechpartner_typ " .
                          "WHERE ansprechpartner_typ_id = ".$db->quote($this['ansprechpartner_typ_id'])." "
        )->fetch(PDO::FETCH_COLUMN, 0);
    }
    
    public function isPublic() {
        if (isset(self::$publicTypes[$this['doku_typ_id']])) {
            self::$publicTypes[$this['ansprechpartner_typ_id']];
        }
        $db = DBManager::get();
        return self::$publicTypes[$this['ansprechpartner_typ_id']] = (bool) $db->query(
            "SELECT COUNT(*) " .
            "FROM stg_bereiche " .
                "INNER JOIN stg_ansprechpartner_typ ON (stg_bereiche.bereichs_id = stg_ansprechpartner_typ.stg_bereichs_id) " .
            "WHERE stg_ansprechpartner_typ.ansprechpartner_typ_id = ".$db->quote($this['ansprechpartner_typ_id'])." " .
                "AND stg_bereiche.oeffentlich = '1' ".
            "GROUP BY stg_bereiche.bereichs_id " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
    }
    
    public function getAvatar($size = Avatar::SMALL) {
        if ($this['range_typ'] === "institute") {
            return InstituteAvatar::getAvatar($this['range_id'])->getImageTag($size);
        } elseif ($this['range_typ'] === "auth_user_md5") {
            return Avatar::getAvatar($this['range_id'])->getImageTag($size);
        } else {
            return "";
        }
    }
    
    /**
     * the homepage within Stud.IP either of the user or of the institute or handwritten
     */
    public function getHomepageURL() {
        $db = DBManager::get();
        if ($this['range_typ'] === "institute") {
            return $db->query("SELECT url FROM Institute WHERE Institut_id = ".$db->quote($this['range_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        } elseif ($this['range_typ'] === "auth_user_md5") {
            return $db->query("SELECT Home FROM user_info WHERE user_id = ".$db->quote($this['range_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            return $this['freitext_homepage'];
        }
    }
    
    public function getName() {
        if ($this['range_typ'] === "institute") {
            $useless_array = get_object_name($this['range_id'], "inst");
        	return $useless_array['name'];
        } elseif ($this['range_typ'] === "auth_user_md5") {
            return get_fullname($this['range_id']);
        } else {
            return $this['freitext_name'];
        }
    }
    
    public function getRangeTyp() {
        $map = array(
            'auth_user_md5' => _("Nutzer"),
            'institute' => _("Einrichtung")
        );
        if (isset($map[$this['range_typ']])) {
            return $map[$this['range_typ']];
        } else {
            return _("Externer Ansprechpartner");
        }
    }
    
    public function getEmail() {
        $db = DBManager::get();
        if ($this['range_typ'] === "institute") {
            return $db->query("SELECT email FROM Institute WHERE Institut_id = ".$db->quote($this['range_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        } elseif ($this['range_typ'] === "auth_user_md5") {
            return $db->query("SELECT Email FROM auth_user_md5 WHERE user_id = ".$db->quote($this['range_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            return $this['freitext_mail'];
        }
    }
    
    public function getTelefon() {
        $db = DBManager::get();
        if ($this['range_typ'] === "institute") {
            return $db->query("SELECT telefon FROM Institute WHERE Institut_id = ".$db->quote($this['range_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        } elseif ($this['range_typ'] === "auth_user_md5") {
            return $db->query("SELECT privatnr FROM user_info WHERE user_id = ".$db->quote($this['range_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            return $this['freitext_telefon'];
        }
    }
    
    public function getStudiengaenge() {
        $db = DBManager::get();
        $studiengaenge = $db->query(
            "SELECT stg_profil_id FROM stg_ansprech_zuord WHERE stg_ansprechpartner_id = ".$db->quote($this->getId())." "
        )->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($studiengaenge as $key => $stg_profil_id) {
            $studiengaenge[$key] = new StgProfil($stg_profil_id);
        }
        return $studiengaenge;
    }
    
    public function setStudiengaenge($profile) {
        $db = DBManager::get();
        $studiengaenge = $db->query(
            "DELETE FROM stg_ansprech_zuord WHERE stg_ansprechpartner_id = ".$db->quote($this->getId())." "
        );
        foreach ($profile as $key => $stg_profil_id) {
            $db->query(
                "INSERT INTO stg_ansprech_zuord " .
                "SET stg_ansprechpartner_id = ".$db->quote($this->getId()).", " .
                    "stg_profil_id = ".$db->quote($stg_profil_id)." " .
            "");
        }
    }

    public function addToProfil() {
        
    }

    public function delete() {
        if ($this->is_deletable()) {
            $db = DBManager::get();
            $db->exec("DELETE FROM stg_ansprech_zuord WHERE stg_ansprechpartner_id = ".$db->quote($this->getId()));
            parent::delete();
        }
    }

    public function is_deletable() {
        return true;
    }
}


