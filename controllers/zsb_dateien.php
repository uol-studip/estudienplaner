<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgFile.class.php';
require_once dirname(__file__).'/../models/qqUploader.php';

SimpleORMap::expireTableScheme();

class ZsbDateienController extends ZsbController {

    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::hasPermission()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        PageLayout::addStylesheet('ui.multiselect.css');
        PageLayout::addScript('ui.multiselect.js');
        PageLayout::addHeadElement("script",
            array("src" => $this->assets_url.'javascripts/fileuploader.js'),
            "");
        PageLayout::addHeadElement("link",
            array("href" => $this->assets_url.'stylesheets/fileuploader.css',
                  "rel" => "stylesheet"),
            "");
        URLHelper::bindLinkParam("typ_id", Request::get("typ_id"));
    }

    public function dateien_action() {
        if (Request::get("delete_x") && Request::get("item_id")) {
            $file = new StgFile(Request::get("item_id"));
            $file->delete();
            $this->flash_now("success", _("Dokument gelöscht"));
        } elseif (Request::get("doku_id")
                && (in_array(Request::get("doku_id"), PersonalRechte::meineDateien())
                    || Request::get("doku_id") === "neu")) {
            $this->details(Request::get("doku_id"));
        }
        $db = DBManager::get();
        $this->dateien = array();
        if (Request::get("typ_id")) {
            $dateien = PersonalRechte::meineDateien(null, Request::get('typ_id'), null);
            foreach ($dateien as $key => $doku_id) {
                $this->dateien[$key] = new StgFile($doku_id);
            }
        }

        $this->typen = StgFile::getTypen();
    }

    public function details($doku_id) {
        //letztes als auch nächstes Profil herausfinden:

        if (Request::get('typ_id')) {
            $this->dokumente = PersonalRechte::meineDateien(null, Request::get('typ_id'), null);
            foreach ($this->dokumente as $key => $dokument_id) {
                $this->dokumente[$key] = new StgFile($dokument_id);
            }
        }

        $this->datei = new StgFile($doku_id !== 'neu' ? $doku_id : null);
        if (Request::get("absenden_x")) {
            $this->datei['name'] = Request::get("name");
            $this->datei['quick_link'] = Request::get("quick_link");
            if ($this->datei['quick_link']) {

            }
            $this->datei['sichtbar'] = Request::get("sichtbar") ? "1" : "0";
            if (Request::get("doku_typ_id")) {
                $this->datei['doku_typ_id'] = Request::get("doku_typ_id");
            }
            if ($verknuepfte_studiengaenge = Request::getArray("verknuepfte_studiengaenge")) {
                $this->datei->setStudiengaenge($verknuepfte_studiengaenge);
            }
            $this->datei['language'] = Request::option('language');
            $this->datei['jahr'] = Request::get("jahr");
            $this->datei['version'] = Request::get("version");
            $this->datei['doku_typ_id'] = Request::get("doku_typ_id");
            $this->datei->store();
            $this->datei->setTags(Request::get("tags"));
            $this->flash_now("success", _("Änderungen wurden übernommen"));
        }
        $this->download_action = $this->link_for("zsb_dateien/download_file");
        $this->profilsuche = $this->getProfilSuche();
        $this->typen = StgFile::getTypen();
        $this->render_template('zsb_dateien/details', $this->layout);
    }

    public function change_datei_profil_action() {
        if (Request::get("doku_id") && Request::get("profil_id")) {
            $profil = new StgProfil(Request::get("profil_id"));
            $profil->addDoku(Request::get("doku_id"));
        }
        $this->render_nothing();
    }
    public function delete_datei_profil_action() {
        if (Request::get("doku_id") && Request::get("profil_id")) {
            $profil = new StgProfil(Request::get("profil_id"));
            $profil->deleteDoku(Request::get("doku_id"));
        }
        $this->render_nothing();
    }

    public function upload_action() {
        $datei = new StgFile(Request::get("doku_id") !== 'neu' ? Request::get("doku_id") : null);
        $datei->upload();
        $this->render_nothing();
    }

    public function download_file_action($id = null, $attachment = true) {
        if ($id === null && Request::option('doku_id') !== 'neu') {
            $id = Request::option('doku_id');
        }
        $datei = new StgFile($id);
        $datei->download($attachment);
        $this->render_nothing();
    }
}

