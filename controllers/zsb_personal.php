<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/Studiengang.class.php';
if (!class_exists("DBHelper")) {
    include_once dirname(__file__).'/../models/DBHelper.class.php';
}


class ZsbPersonalController extends ZsbController {
    
    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::isRoot()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        URLHelper::bindLinkParam("abschluss_id", Request::get("abschluss_id"));
        URLHelper::bindLinkParam("studiengang_id", Request::get("studiengang_id"));
    }
    
    public function personal_action() {
        if (Request::get("studiengang_id")) {
            $this->details(Request::get("studiengang_id"));
        }
        
        $this->studiengaenge = PersonalRechte::meineStudiengaenge();
    }
    
    public function details($studiengang_id) {
        $this->studiengaenge = PersonalRechte::meineStudiengaenge();
        
    	$this->studiengang = new Studiengang($studiengang_id);

        $db = DBManager::get();
        $this->rollen = array();
        foreach (DBHelper::getEnumOptions('stg_fsb_rollen', 'rollen_typ') as $rollen_typ) {
            $this->rollen[$rollen_typ] = $db->query(
                "SELECT user_id " .
                "FROM stg_fsb_rollen " .
                "WHERE studiengang_id = ".$db->quote($studiengang_id)." " .
                    "AND rollen_typ = ".$db->quote($rollen_typ)." " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        //$this->profilsuche = $this->getProfilSuche();
        $this->render_template('zsb_personal/details', $this->layout);
    }
    
    public function add_personal_action() {
        $db = DBManager::get();
        $query = "INSERT IGNORE INTO stg_fsb_rollen " .
            "SET user_id = ".$db->quote(Request::get("user_id")).", " .
                "studiengang_id = ".$db->quote(Request::get("studiengang_id")).", " .
                "rollen_typ = ".$db->quote(Request::get("rollen_typ"))." ";
        print $query;
        $db->query(
            "INSERT IGNORE INTO stg_fsb_rollen " .
            "SET user_id = ".$db->quote(Request::get("user_id")).", " .
                "studiengang_id = ".$db->quote(Request::get("studiengang_id")).", " .
                "rollen_typ = ".$db->quote(Request::get("rollen_typ"))." " .
        "");
        $this->render_nothing();
    }
    
    public function delete_personal_action() {
        $db = DBManager::get();
        $db->query(
            "DELETE FROM stg_fsb_rollen " .
            "WHERE user_id = ".$db->quote(Request::get("user_id"))." " .
                "AND studiengang_id = ".$db->quote(Request::get("studiengang_id"))." " .
                "AND rollen_typ = ".$db->quote(Request::get("rollen_typ"))." " .
        "");
    	$this->render_nothing();
    }
}

