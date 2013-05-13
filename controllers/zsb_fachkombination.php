<?php
require 'application.php';
require_once dirname(__file__)."/zsb_controller.php";
require_once dirname(__file__).'/../models/Abschluss.class.php';
require_once dirname(__file__).'/../models/Studiengang.class.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgFachkombination.class.php';
if (!class_exists("DBHelper")) {
    include_once dirname(__file__).'/../models/DBHelper.class.php';
}

//Laden von Extensions wie speziellen lokalen Erweiterungen in Oldenburg:
$extension_folder=opendir(dirname(__file__)."/extensions/");
while (($file = readdir($extension_folder))!==false) {
    if (!is_dir(dirname(__file__)."/extensions/".$file)
            && strpos($file, ".") !== 0
            && strpos($file, ".php") !== false) {
        include_once dirname(__file__)."/extensions/".$file;
    }
}
closedir($extension_folder);


class ZsbFachkombinationController extends ZSBController {
    
    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::hasPermission()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        PageLayout::addStylesheet('ui.multiselect.css');
        PageLayout::addScript('ui.multiselect.js');
        URLHelper::bindLinkParam("abschluss_id", Request::get("abschluss_id"));
        URLHelper::bindLinkParam("studiengang_id", Request::get("studiengang_id"));
    }

    public function fach_kombinationen_action() {
        $db = DBManager::get();
        if (!PersonalRechte::isRoot()) {
            throw new AccessDeniedException(_("Sie sind weder Root noch Angestellter der ZSB."));
            return;
        }
        
        $this->url = $this->url_for("zsb_fachkombination/fach_kombinationen");
        
        if (Request::get("absenden_x")) {
            $new = !Request::submitted("fach_kombi_id") or !Request::get("fach_kombi_id") !== "";
            $kombi = new StgFachkombination(!$new ? Request::get("fach_kombi_id") : null);
            $kombi['stg_profil_id'] = Request::get("stg_profil_id");
            $kombi['kombi_stg_profil_id'] = Request::get("kombi_stg_profil_id");
            $kombi['beschreibung'] = Request::get("beschreibung");
            if ($kombi->store() !== false) {
                if ($new) {
                    $this->flash_now("success", _("Fächerkombination wurde angelegt."));
                } else {
                    $this->flash_now("success", _("Änderungen wurden übernommen"));
                }
            } else {
                if ($new) {
                    $this->flash_now("error", _("So eine Kombination ist schon vergeben. Es wurde nichts angelegt."));
                } else {
                    $this->flash_now("error", _("So eine Kombination ist schon vergeben. Es wurde nichts geändert."));
                }
            }
        }
        if (Request::get("delete_x") && (Request::submitted("fach_kombi_id") || Request::submitted("item_id"))) {
            $kombi = new StgFachkombination(Request::get("fach_kombi_id") ? Request::get("fach_kombi_id") : Request::get("item_id"));
            $kombi->delete();
            $this->flash_now("success", _("Fächerkombination gelöscht"));
        } elseif (Request::submitted("fach_kombi_id")) {
            $this->fach_kombination_details(Request::get("fach_kombi_id"));
            return;
        }
        //alle Kombinationsfächer finden:
        $this->kombinationen = array();
        if (Request::get("studiengang_id") or Request::get("abschluss_id")) {
            $this->kombinationen = PersonalRechte::meineFachkombinationen(null, Request::get("studiengang_id"), Request::get("abschluss_id"));
            foreach ($this->kombinationen as $key => $kombination_id) {
                $this->kombinationen[$key] = new StgFachkombination($kombination_id);
            }
        }
        //$this->studiengaenge = PersonalRechte::meineStudiengaenge();
        $this->studiengaenge = StgFachkombination::findUsedStudiengaenge();
        $this->abschluesse = Abschluss::findAllUsed();
    }
    
    public function fach_kombination_details($fach_kombi_id) {
        if (!PersonalRechte::isRoot()) {
            throw new AccessDeniedException(_("Sie sind weder Root noch Angestellter der ZSB."));
            return;
        }
        $db = DBManager::get();
        $this->kombination = new StgFachkombination(Request::get("fach_kombi_id"));
        if (Request::get("studiengang_id") or Request::get("abschluss_id")) {
            $this->kombinationen = PersonalRechte::meineFachkombinationen(null, Request::get("studiengang_id"), Request::get("abschluss_id"));
            foreach ($this->kombinationen as $key => $kombination_id) {
                $this->kombinationen[$key] = new StgFachkombination($kombination_id);
            }
        }
        
        $this->profilsuche = $this->getProfilSuche();
        
        $this->studiengaenge = PersonalRechte::meineStudiengaenge();
        $this->abschluesse = Abschluss::findAllUsed();
        
        $this->render_template('zsb_fachkombination/details_fachkombi', $this->layout);
        
    }
    
}

