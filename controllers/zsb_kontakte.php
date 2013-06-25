<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgAnsprechpartner.class.php';
if (!class_exists("DBHelper")) {
    include_once dirname(__file__).'/../models/DBHelper.class.php';
}

class ZsbKontakteController extends ZsbController {

    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::hasPermission()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        PageLayout::addStylesheet('ui.multiselect.css');
        PageLayout::addScript('ui.multiselect.js');
        URLHelper::bindLinkParam("typ_id", Request::get("typ_id"));
    }

    public function kontakte_action() {
        if (Request::get("delete_x") && Request::get("item_id")) {
            $kontakt = new StgAnsprechpartner(Request::get("item_id"));
            $kontakt->delete();
            $this->flash_now("success", _("Ansprechpartner gelöscht"));
        } elseif (Request::get("kontakt_id")
                && (in_array(Request::get("kontakt_id"), PersonalRechte::meineAnsprechpartner())
                    || Request::get("kontakt_id") === "neu")) {
            $this->kontakte_details(Request::get("kontakt_id"));
        }
        $this->ansprechpartner = array();
        if (Request::get("typ_id")) {
            $ansprechpartner = PersonalRechte::meineAnsprechpartner(null, Request::get("typ_id"));
            foreach ($ansprechpartner as $key => $ansprechpartner_id) {
                $this->ansprechpartner[$key] = new StgAnsprechpartner($ansprechpartner_id);
            }
            usort($this->ansprechpartner, function ($a, $b) {
                return strcmp($a->getName('no_title_rev'), $b->getName('no_title_rev'));
            });
        }
        $this->typen = StgAnsprechpartner::getAnsprechpartnerTypen();
    }

    public function kontakte_details($kontakt_id) {
        $this->kontakt = new StgAnsprechpartner($kontakt_id !== "neu" ? $kontakt_id : null);
        if (Request::get("absenden_x")) {
            $this->kontakt['range_typ'] = Request::get("range_typ");
            $this->kontakt['range_id'] = Request::get("range_typ") && Request::get("range_id") ? Request::get("range_id") : "";
            if (Request::get("ansprechpartner_typ_id")) {
                $typen = StgAnsprechpartner::getAnsprechpartnerTypen();
                foreach ($typen as $typ) {
                    if ($typ['ansprechpartner_typ_id'] === Request::get("ansprechpartner_typ_id")) {
                        $this->kontakt['ansprechpartner_typ_id'] = Request::get("ansprechpartner_typ_id");
                    }
                }
            }
            if ($verknuepfte_studiengaenge = Request::getArray("verknuepfte_studiengaenge")) {
                $this->kontakt->setStudiengaenge($verknuepfte_studiengaenge);
            }
            $this->kontakt['freitext_name'] = Request::get("freitext_name");
            $this->kontakt['freitext_mail'] = Request::get("freitext_mail");
            $this->kontakt['freitext_telefon'] = Request::get("freitext_telefon");
            $this->kontakt['freitext_homepage'] = Request::get("freitext_homepage");
            $this->kontakt->store();
            $this->flash_now("success", _("Änderungen wurden übernommen"));
        }

        //letztes als auch nächstes Profil herausfinden:
        if (Request::get("typ_id")) {
            $this->ansprechpartner = PersonalRechte::meineAnsprechpartner(null, Request::get("typ_id"));
            foreach ($this->ansprechpartner as $key => $ansprechpartner_id) {
                $this->ansprechpartner[$key] = new StgAnsprechpartner($ansprechpartner_id);
            }
        }

        $this->typen = StgAnsprechpartner::getAnsprechpartnerTypen();

        $this->kontaktsuche = StgAnsprechpartner::getAnsprechpartnerIdentitaetSuche();
        $this->profilsuche = $this->getProfilSuche();
        $this->render_template('zsb_kontakte/details_kontakte', $this->layout);
    }

    public function change_kontakt_profil_action() {
        if (Request::get("kontakt_id") && Request::get("profil_id")) {
            $profil = new StgProfil(Request::get("profil_id"));
            $profil->addAnsprechpartner(Request::get("kontakt_id"));
        }
        $this->render_nothing();
    }
    public function delete_kontakt_profil_action() {
        if (Request::get("kontakt_id") && Request::get("profil_id")) {
            $profil = new StgProfil(Request::get("profil_id"));
            $profil->deleteAnsprechpartner(Request::get("kontakt_id"));
        }
        $this->render_nothing();
    }


}

