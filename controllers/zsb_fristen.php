<?php
require 'application.php';
require_once dirname(__file__)."/zsb_controller.php";
require_once dirname(__file__).'/../models/Fristen.class.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/Studiengang.class.php';
require_once dirname(__file__).'/../models/Abschluss.class.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgFile.class.php';
require_once dirname(__file__).'/../models/StgAnsprechpartner.class.php';
require_once dirname(__file__).'/../models/StgVerlaufsplan.class.php';

class ZsbFristenController extends ZSBController {
    
    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::hasPermission()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        URLHelper::bindLinkParam("abschluss_id", Request::get("abschluss_id"));
        URLHelper::bindLinkParam("studiengang_id", Request::get("studiengang_id"));
    }
    
    public function index_action() {
        if (Request::get("studiengang_id") or Request::get("abschluss_id")) {
            $this->fristen_action();
            return;
        }
        $this->studiengaenge = PersonalRechte::meineStudiengaenge(null, true);
        $this->abschluesse = Abschluss::findAllUsed();
    }

    public function fristen_action() {
        Navigation::activateItem('/start/zsb/fristen');
        $studiengaenge = PersonalRechte::meineStudiengaenge();
        if (($profile = Request::getArray("profile")) && count($_POST)) {
            foreach ($profile as $profil_id => $profil_data) {
                if (in_array(StgProfil::get($profil_id)->getValue("fach_id"), $studiengaenge)) {
                    Fristen::StoreBewerbungByProfil($profil_id, $profil_data);
                }
            }
            $this->flash_now("success", _("Änderungen wurden übernommen"));
        }
        $this->profile = array();
        if (Request::get("studiengang_id")) {
            $this->profile = Fristen::GetBewerbungsdatenByStudiengang(Request::get("studiengang_id"));
        }
        if (Request::get("abschluss_id")) {
            $this->profile = Fristen::GetBewerbungsdatenByAbschluss(Request::get("abschluss_id"));
        }
        
        $this->zielgruppen = Fristen::GetZielgruppen();
        if (!$this->zielgruppen) {
            throw new Exception(_("Konnte einige Zielgruppen nicht finden. Datenbank ist scheinbar unvollständig."));
        }
        $this->render_template('zsb_fristen/fristen', $this->layout);
    }

}

