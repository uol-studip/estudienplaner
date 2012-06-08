<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgFile.class.php';
require_once dirname(__file__).'/../models/StgBereich.class.php';


class ZsbBereicheController extends ZsbController {
    
    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::isRoot()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        PageLayout::addStylesheet('ui.multiselect.css');
        PageLayout::addScript('ui.multiselect.js');
    }
    
    public function bereiche_action() {
        if (Request::get("delete_x") && Request::get("bereichs_id")) {
            $profil = new StgBereich(Request::get("bereichs_id"));
            $profil->delete();
            $this->flash_now("success", _("Bereich gelöscht"));
        } elseif (Request::get("bereichs_id")) {
            $this->details(Request::get("bereichs_id"));
        }
    	$this->bereiche = StgBereich::getBereiche();
    }
    
    protected function details($bereichs_id) {
        
        $this->bereich = new StgBereich($bereichs_id !== "neu" ? $bereichs_id : null);
        if (Request::get('ansprechpartner_typ_neu')) {
            //$this->bereich->addAnsprechpartnerTyp(Request::get('ansprechpartner_typ_neu'));
        }
        if (Request::get("absenden_x") || Request::get("delete_dokument_typ_x") || Request::get("create_x")) {
            $this->bereich['bereich_name'] = Request::get('bereich_name');
            $this->bereich['sichtbar_fsb'] = (int) Request::get('sichtbar_fsb');
            $this->bereich['sichtbar_pamt'] = (int) Request::get('sichtbar_pamt');
            $this->bereich['sichtbar_iamt'] = (int) Request::get('sichtbar_iamt');
            $this->bereich['sichtbar_stuko'] = (int) Request::get('sichtbar_stuko');
            $this->bereich['sichtbar_stab'] = (int) Request::get('sichtbar_stab');
            $this->bereich['oeffentlich'] = (int) Request::get('oeffentlich');
            $this->bereich->setDokumentTypen(Request::getArray('stg_dokument_typ'));
            $this->bereich->store();
            $this->flash_now("success", _("Änderungen wurden übernommen"));
        }
        if (Request::get("create_x")) {
            StgBereich::createDokumentTyp(Request::get("stg_dokument_typ_neu"));
        }
        if (Request::get("delete_dokument_typ_x")) {
            StgBereich::deleteDokumentTyp(Request::get("delete_stg_dokument_typ_id"));
        }
        $this->dokumenttypensuche = new SQLSearch(
            "SELECT doku_typ_id, name " .
            "FROM stg_dokument_typ " .
            "WHERE name LIKE :input " .
        "", _("Dokumenten-Typ suchen"));
        $this->alle_dokument_typen = StgBereich::getAlleDokumentTypen();
        $this->dokument_typen = $this->bereich->getDokumentTypen();
        $this->ansprechpartner_typen = $this->bereich->getAnsprechpartnerTypen();
        $this->render_template('zsb_bereiche/details', $this->layout);
    }
    
    public function add_ansprechpartner_action() {
        $this->bereich = new StgBereich(Request::get("bereichs_id"));
        $this->bereich->addAnsprechpartnerTyp(studip_utf8decode(Request::get('ansprechpartner_typ_neu')));
        $this->render_nothing();
    }
    public function delete_ansprechpartner_typ_action() {
        $this->bereich = new StgBereich(Request::get("bereichs_id"));
        $this->bereich->deleteAnsprechpartnerTyp(Request::get('ansprechpartner_typ_id'));
        $this->render_nothing();
    }
    
    
    
}

