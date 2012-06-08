<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgVerlaufsplan.class.php';
require_once dirname(__file__).'/../models/Verlaufsplan.php';
require_once dirname(__file__).'/../models/modules/ModuleHandler.php';
require_once dirname(__file__).'/../models/Abschluss.class.php';
require_once dirname(__file__).'/../models/Studiengang.class.php';


class ZsbVerlaufsplanController extends ZsbController {

    public $type_of_modules = 5;

    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        PageLayout::addHeadElement("script",
            array("src" => $this->assets_url.'javascripts/verlaufsplaner.js'),
            "");
        if (!PersonalRechte::showVerlaufsplaene()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        URLHelper::bindLinkParam("abschluss_id", Request::get("abschluss_id"));
        URLHelper::bindLinkParam("studiengang_id", Request::get("studiengang_id"));
    }

    public function verlaufsplan_action() {
        if (Request::get("delete_x") && Request::get("item_id") && in_array(Request::get("item_id"), PersonalRechte::meineVerlaufsplaene())) {
            $verlaufsplan = new StgVerlaufsplan(Request::get("item_id"));
            $verlaufsplan->delete();
            PersonalRechte::restore();
            $this->flash_now("success", _("Verlaufsplan gelöscht"));
        } elseif (Request::get("item_id")) {
            $this->verlaufsplan_details(Request::get("item_id"));
        }
        $db = DBManager::get();
        $this->verlaufsplaene = array();
        if (Request::get("studiengang_id") or Request::get("abschluss_id")) {
            $verlaufsplaene = PersonalRechte::meineVerlaufsplaene(null, Request::get("studiengang_id"), Request::get("abschluss_id"));
            foreach($verlaufsplaene as $key => $verlaufsplan_id) {
                $this->verlaufsplaene[$key] = new StgVerlaufsplan($verlaufsplan_id);
            }
        }
        
        //$this->studiengaenge = PersonalRechte::meineStudiengaenge(null, true);
        $this->studiengaenge = StgVerlaufsplan::findUsedStudiengaenge();
        $this->abschluesse = Abschluss::findAllUsed();
    }

    public function verlaufsplan_details($item_id) {
        $db = DBManager::get();
        $meineProfile = PersonalRechte::meineProfile();
        $meineVerlaufsplaene = PersonalRechte::meineVerlaufsplaene();
        $this->verlaufsplan = new StgVerlaufsplan($item_id !== "neu" ? $item_id : null);
        $profile = $this->verlaufsplan->getProfile();
        if ($item_id !== "neu"
                && !in_array($item_id, $meineVerlaufsplaene) ) {
            throw new AccessDeniedException(_("Diesen Verlaufsplan dürfen Sie nicht bearbeiten."));
        }
        if (Request::submitted("absenden")) {
            if (!Request::get("is_kombo")) {
                if (Request::get('stg_profil_id')) {
                    $this->verlaufsplan['stg_profil_id'] = Request::option('stg_profil_id');
                    $this->verlaufsplan['fach_kombi_id'] = null; //in der Datenbank steht trotzdem "0" wegen SimpleORMap
                    $this->verlaufsplan['sichtbar_fach2'] = 0;
                }
            } else {
                if (Request::get('fach_kombi_id')) {
                    $this->verlaufsplan['fach_kombi_id'] = Request::option('fach_kombi_id');
                    $this->verlaufsplan['stg_profil_id'] = null; //Datenbank "0"
                    $kombo = $db->query("SELECT * FROM stg_fach_kombination WHERE fach_kombi_id = ".$db->quote($this->verlaufsplan['fach_kombi_id'])." ")->fetch(PDO::FETCH_ASSOC);
                }
            }

            if ($profile[0] && in_array($profile[0]->getId(), $meineProfile)) {
                $this->verlaufsplan['sichtbar_fach1'] = Request::int('sichtbar_fach1');
            }
            if ($profile[1] && in_array($profile[1]->getId(), $meineProfile)) {
                $this->verlaufsplan['sichtbar_fach2'] = Request::int('sichtbar_fach2');
            }
            
            $this->verlaufsplan['titel'] = Request::get("verlaufsplan_titel");
            $this->verlaufsplan['untertitel'] = Request::get("untertitel");
            $this->verlaufsplan['notiz'] = Request::get("notiz");
            $this->verlaufsplan['version'] = Request::int("version");
            $this->verlaufsplan['user_id'] = $GLOBALS['user']->id;
            $this->verlaufsplan->store();
            $this->flash_now("success", _("Änderungen wurden übernommen"));
        }

        if (!$profil1) {
            if ($this->verlaufsplan['fach_kombi_id']) {
                $kombo = $db->query("SELECT * FROM stg_fach_kombination WHERE fach_kombi_id = ".$db->quote($this->verlaufsplan['fach_kombi_id'])." ")->fetch(PDO::FETCH_ASSOC);
                $profil1 = $kombo['stg_profil_id'];
                $profil2 = $kombo['kombi_stg_profil_id'];
            } else {
                $profil1 = $this->verlaufsplan['stg_profil_id'];
            }
        }
        $this->edit_fach1 = in_array($profil1, $meineProfile);
        $this->edit_fach2 = $profil2 && in_array($profil2, $meineProfile);

        //letztes als auch nächstes Profil herausfinden:
        $this->setHorizontalNavigation($item_id, PersonalRechte::meineVerlaufsplaene(), "StgVerlaufsplan");

        $this->profilsuche = $this->getProfilSuche();
        $this->fachkombisuche = $this->getFachkombiSuche();
        if ($this->verlaufsplan['fach_kombi_id']) {
            $this->fachkombinationsname = DBManager::get()->query(
                "SELECT CONCAT(studiengang1.name, ' (', abschluss1.name , ') - ', studiengang2.name, ' (', abschluss2.name , ')') " .
                "FROM stg_fach_kombination " .
                        "INNER JOIN stg_profil AS profil1 ON (profil1.profil_id = stg_fach_kombination.stg_profil_id) " .
                        "LEFT JOIN studiengaenge AS studiengang1 ON (studiengang1.studiengang_id = profil1.fach_id) " .
                        "LEFT JOIN abschluss AS abschluss1 ON (abschluss1.abschluss_id = profil1.abschluss_id) " .
                        "INNER JOIN stg_profil AS profil2 ON (profil2.profil_id = stg_fach_kombination.kombi_stg_profil_id) " .
                        "LEFT JOIN studiengaenge AS studiengang2 ON (studiengang2.studiengang_id = profil2.fach_id) " .
                        "LEFT JOIN abschluss AS abschluss2 ON (abschluss2.abschluss_id = profil2.abschluss_id) " .
                "WHERE stg_fach_kombination.fach_kombi_id = ".DBManager::get()->quote($this->verlaufsplan['fach_kombi_id'])." " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
        }

        //Von Till:
        $this->plugin_url = $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . '/'. $this->dispatcher->trails_root;
        $this->controller = $this;

        // fetch the chosen Verlaufsplan
        if (!$this->verlaufsplan->isNew()) {
            $this->gettersetter_verlaufsplan = new Verlaufsplan($this->verlaufsplan->getId());

            // fetch available modules
            $this->modules = PersonalRechte::meineModule();
            $this->module_type = $this->type_of_modules;
            $this->dimensions = $db->query(
                "SELECT MAX(fachsem) as height, MAX(position) AS width " .
                "FROM stg_verlaufsplan_eintraege " .
                "WHERE stg_verlaufsplan_id = ".$db->quote($this->verlaufsplan->getId())." " .
                "GROUP BY stg_verlaufsplan_id " .
            "")->fetch(PDO::FETCH_ASSOC, 0);
        }

        $this->render_template('zsb_verlaufsplan/details.php', $this->layout);
    }

    public function get_module_children_action() {
        $this->modul_id = Request::option("modul_id");
        $this->modules = PersonalRechte::meineModule();
        $this->module_type = $this->type_of_modules;
        if (!isset($this->modules[$this->modul_id])) {
            throw new AccessDeniedException("Keinen Zugriff auf dieses Modul");
        }
        $this->layout = null;
    }

    

    ////////////////////////////////////////////////////////////////////////////
    //                               von Till                                 //
    ////////////////////////////////////////////////////////////////////////////

    public function remove_mod_action() {
        $this->render_text(createQuestion( _("Sind sie sicher, dass sie eine Modulspalte löschen wollen?") . ' '
            . _("Sämtliche darin enthaltenen Modulzuordnungen gehen dadurch verloren."),
            array(), array(), 'javascript:'
        ));
    }

    public function remove_sem_action() {
        $this->render_text(createQuestion( _("Sind sie sicher, dass sie ein Semester löschen wollen?") . ' '
            . _("Sämtliche darin enthaltenen Modulzuordnungen gehen dadurch verloren."),
            array(), array(), 'javascript:'
        ));
    }

    public function expand_sem_action() {
        $this->render_text(createQuestion( _("Dies wird alle Moduleinträge im darunter liegenden Block löschen.") . ' '
            . _("Sind Sie sicher?"),
            array(), array(), 'javascript:'
        ));
    }

    public function store_verlaufsplan_action() {
        $verlaufsplan_id = Request::option('verlaufsplan_id');
        if (!in_array($verlaufsplan_id, PersonalRechte::meineVerlaufsplaene())) {
            throw new AccessDeniedException(_("Diesen Verlaufsplan dürfen Sie nicht bearbeiten."));
        }
        $verlaufsplan = new Verlaufsplan($verlaufsplan_id);
        $verlaufsplan->clearModules();

        foreach (Request::getArray('verlaufsplan') as $element) {
            if ($element['modul_id'][0] === "z") { //Freitextmodul
                $element['modul_id'] = "";
            }
            $verlaufsplan->addModule(
                $element['semester'],
                $element['position'],
                $element['duration'],
                $element['modul_id'],
                $element['kp'],
                $element['type'],
                $element['notiz']
            );
        }
        $verlaufsplan->store();
        $this->render_nothing();
    }

}

