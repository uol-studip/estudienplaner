<?php

require_once dirname(__file__)."/models/PersonalRechte.php";

function add_safely_security_token() {
    if (class_exists("CSRFProtection")) {
        return CSRFProtection::tokenTag();
    } else {
        return "";
    }
}

//Laden von Extensions wie speziellen lokalen Erweiterungen in Oldenburg:
$extension_folder=opendir(dirname(__file__)."/controllers/extensions/");
while (($file = readdir($extension_folder))!==false) {
    if (!is_dir(dirname(__file__)."/controllers/extensions/".$file)
            && strpos($file, ".") !== 0
            && strpos($file, ".php") !== false) {
        include_once dirname(__file__)."/controllers/extensions/".$file;
    }
}
closedir($extension_folder);

class eStudienplaner extends StudIPPlugin implements SystemPlugin {

    public $config = array();
    
    function __construct() {
        global $perm;

        parent::__construct();

        $this->restoreConfig();
        
        //Sichten für zsb_zentral, fsb, stuko, p_amt und i_amt:
        if (PersonalRechte::hasPermission()) {
            $params = array('abschluss_id' => 0, 'studiengang_id' => 0, 'typ_id' => 0);
            $navigation = new AutoNavigation(_("e-Studienplaner (Backend)"), PluginEngine::getURL($this, $params, 'zsb_studiengang/studiengaenge'));
            Navigation::addItem('/start/zsb', $navigation);
            Navigation::addItem('/zsb', $navigation);
            
            if (PersonalRechte::showStudiengaenge() || PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
                $navigation = new AutoNavigation(_("Studiengangsprofile"), PluginEngine::getURL($this, $params, 'zsb_studiengang/studiengaenge'));
                Navigation::addItem('/start/zsb/studiengaenge', $navigation);
                Navigation::addItem('/zsb/studiengaenge', $navigation);
            }
            
            if (PersonalRechte::isRoot()) {
                $navigation = new AutoNavigation(_("Fächerkombinationen"), PluginEngine::getURL($this, $params, 'zsb_fachkombination/fach_kombinationen'));
                Navigation::addItem('/start/zsb/fach_kombinationen', $navigation);
                Navigation::addItem('/zsb/fach_kombinationen', $navigation);
            }

            if (PersonalRechte::showVerlaufsplaene()) {
                $navigation = new AutoNavigation(_("Verlaufspläne"), PluginEngine::getURL($this, $params, 'zsb_verlaufsplan/verlaufsplan'));
                Navigation::addItem('/start/zsb/verlaufsplan', $navigation);
                Navigation::addItem('/zsb/verlaufsplan', $navigation);
            }

            if (PersonalRechte::showAnsprechpartner()) {
                $navigation = new AutoNavigation(_("Ansprechpartner"), PluginEngine::getURL($this, $params, 'zsb_kontakte/kontakte'));
                Navigation::addItem('/start/zsb/kontakte', $navigation);
                Navigation::addItem('/zsb/kontakte', $navigation);
            }

            if (PersonalRechte::showDokumente()) {
                $navigation = new AutoNavigation(_("Dokumente"), PluginEngine::getURL($this, $params, 'zsb_dateien/dateien'));
                Navigation::addItem('/start/zsb/dokumente', $navigation);
                Navigation::addItem('/zsb/dokumente', $navigation);
            }

            if (PersonalRechte::showStudiengaenge()) {
                $navigation = new AutoNavigation(_("Bewerbungsfristen"), PluginEngine::getURL($this, $params, 'zsb_fristen/index'));
                Navigation::addItem('/start/zsb/fristen', $navigation);
                Navigation::addItem('/zsb/fristen', $navigation);
            }

            if (PersonalRechte::isRoot()) {
                $navigation = new AutoNavigation(_("Verwaltung"), PluginEngine::getURL($this, $params, 'zsb_bereiche/bereiche'));

                Navigation::addItem('/start/zsb/verwaltung', $navigation);
                Navigation::addItem('/zsb/verwaltung', $navigation);
                $navigation = new AutoNavigation(_("Bereiche"), PluginEngine::getURL($this, $params, 'zsb_bereiche/bereiche'));
                Navigation::addItem('/zsb/verwaltung/bereiche', $navigation);
                $navigation = new AutoNavigation(_("Personal"), PluginEngine::getURL($this, $params, 'zsb_personal/personal'));
                Navigation::addItem('/zsb/verwaltung/personal', $navigation);
                $navigation = new AutoNavigation(_("Datenfelder"), PluginEngine::getURL($this, $params, 'zsb_datenfelder/datenfelder'));
                Navigation::addItem('/zsb/verwaltung/datenfelder', $navigation);
            }
            
            if (Navigation::getItem("/zsb")->isActive()) {
                PageLayout::setTitle($this->getDisplayTitle());
            }
        }
        
        
    }

    function getDisplayTitle() {
        return _("eStudienplaner");
    }

    function restoreConfig() {
        $config = DBManager::get()
                ->query("SELECT comment FROM config WHERE field = 'CONFIG_" . $this->getPluginName() . "' AND is_default=1")
                ->fetchColumn();
        $this->config = unserialize($config);
        return $this->config != false;
    }

    function storeConfig() {
        $config = serialize($this->config);
        $field = "CONFIG_" . $this->getPluginName();
        $st = DBManager::get()
        ->prepare("REPLACE INTO config (config_id, field, value, is_default, type, range, chdate, comment)
            VALUES (?,?,'do not edit',1,'string','global',UNIX_TIMESTAMP(),?)");
        return $st->execute(array(md5($field), $field, $config));
    }

    /**
    * This method dispatches and displays all actions. It uses the template
    * method design pattern, so you may want to implement the methods #route
    * and/or #display to adapt to your needs.
    *
    * @param  string  the part of the dispatch path, that were not consumed yet
    *
    * @return void
    */
    function perform($unconsumed_path) {
        if(!$unconsumed_path) {
            header("Location: " . PluginEngine::getUrl($this), 302);
            return false;
        }
        $trails_root = $this->getPluginPath();
        $dispatcher = new Trails_Dispatcher($trails_root, null, 'show');
        $dispatcher->current_plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }
}
