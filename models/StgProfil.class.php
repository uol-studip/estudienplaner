<?php

require_once dirname(__file__)."/SORM.class.php";
require_once dirname(__file__)."/PersonalRechte.php";

class StgProfil extends SORM {
    protected $db_table = 'stg_profil';

    static protected $infos = array();

    static public function get($profil_id) {
        return new StgProfil($profil_id);
    }

    static public function getMeineProfile($user_id = null, $studiengang_id = null, $abschluss_id = null) {
        global $user_id;
        $user_id || $user_id = $user->id;
        $profile = array();
        foreach (PersonalRechte::meineStudiengaenge($user_id) as $studiengang) {
            if ($studiengang_id === null or $studiengang === $studiengang_id) {
                $profile = array_merge($profile, self::getByStudiengang($studiengang, $abschluss_id));
            }
        }
        return $profile;
    }

    static public function getByStudiengang($studiengang_id, $abschluss_id = null) {
        $db = DBManager::get();
        $profile = $db->query(
            "SELECT stg_profil.profil_id " .
            "FROM stg_profil " .
            "WHERE stg_profil.fach_id = ".$db->quote($studiengang_id)." ".
                ($abschluss_id ? "AND stg_profil.abschluss_id = ".$db->quote($abschluss_id)." " : "") .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        $ret = array();
        foreach ($profile as $profil_id) {
            $ret[] = new StgProfil($profil_id);
        }
        return $ret;
    }

    static public function getMoeglicheTypen() {
        $db = DBManager::get();
        return $db->query(
            "SELECT * FROM stg_typ " .
        "")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function __construct($profil_id = null) {
        parent::__construct($profil_id);
    }

    protected function getSettings() {
        $db = DBManager::get();
        $infos = $db->query("SELECT abschluss.name as abschluss_name, studiengaenge.name as studiengang " .
                            "FROM stg_profil " .
                                "LEFT JOIN abschluss ON (stg_profil.abschluss_id = abschluss.abschluss_id) " .
                                "LEFT JOIN studiengaenge ON (studiengaenge.studiengang_id = stg_profil.fach_id) " .
                            "WHERE stg_profil.profil_id = " . $db->quote($this->getId()) . " "
                     )->fetch();
        self::$infos[$this->getId()]["abschluss"] = $infos['abschluss_name'];
        self::$infos[$this->getId()]["studiengang"] = $infos['studiengang'];
        self::$infos[$this->getId()]["typen"] = $db->query(
            "SELECT stg_typ_id FROM stg_typ_zuordnung WHERE stg_profil_id = ".$db->quote($this->getId())." " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        return $this;
    }

    public function getAbschluss() {
        if (!self::$infos[$this->getId()]["abschluss"]) {
            $this->getSettings();
        }
        return self::$infos[$this->getId()]["abschluss"];
    }

    public function getTypen() {
        if (!self::$infos[$this->getId()]["typen"]) {
            $this->getSettings();
        }
        return self::$infos[$this->getId()]["typen"];
    }

    public function setTypen($typen) {
        self::$infos[$this->getId()]["typen"] = $typen;
        $db = DBManager::get();
        $db->exec("DELETE FROM stg_typ_zuordnung WHERE stg_profil_id = ".$db->quote($this->getId()));
        if (count($typen)) {
            $values = "";
            foreach ($typen as $key => $typ_id) {
                if ($key != 0) {
                    $values .= ", ";
                }
                $values .= "(".$db->quote($typ_id).", ".$db->quote($this->getId()).") ";
            }
            $db->exec("INSERT IGNORE INTO stg_typ_zuordnung (stg_typ_id, stg_profil_id) VALUES ".$values);
        }
    }

    public function getStudiengang() {
        if (!self::$infos[$this->getId()]["studiengang"]) {
            $this->getSettings();
        }
        return self::$infos[$this->getId()]["studiengang"];
    }

    /*public/* function getName() {
        return $this->getStudiengang()." (".$this->getAbschluss().")";
    }
     *
     */

    public function hasPermission($user_id = null) {
        global $user;
        if ($this->isNew()) {
            return true;
        }
        $user_id || $user_id = $user->id;
        return in_array($this['fach_id'], PersonalRechte::meineStudiengaenge($user_id))
                ? true
                : false;
    }

    public function getInformation($language = null) {
        $db = DBManager::get();
        $informationen = array();
        $plugin_info = PluginManager::getInstance()->getPluginInfo('eStudienplaner');

        $informationen['datenfelder'] = array_filter(DataFieldEntry::getDataFieldEntries($this->profil_id, 'plugin', $plugin_info['id']), function ($item) {
            return preg_match('/\.profile$/', $item->getName());
        });

        foreach (self::getPossibleLanguages() as $lang) {
            if ($language !== null && $language !== $lang) {
                continue;
            }
            $info = array();
            $info['kurz'] = $db->query("SELECT * " .
                               "FROM stg_profil_information " .
                               "WHERE stg_profil_id = ".$db->quote($this->getId())." " .
                                   "AND sprache = ".$db->quote($lang)." " .
                                   "AND info_form = 'kurz' "
            )->fetch();
            $info['lang'] = $db->query("SELECT * " .
                               "FROM stg_profil_information " .
                               "WHERE stg_profil_id = ".$db->quote($this->getId())." " .
                                   "AND sprache = ".$db->quote($lang)." " .
                                   "AND info_form = 'lang' "
            )->fetch();
            //Datenfelder auslesen:
            // Noch keine Informationen gespeichert? Dann temporäre ID verwenden...
            if (!$info['kurz']['information_id']) {
                $info['kurz']['information_id'] = 'eStudienplaner_info_kurz';
            }
            if (!$info['lang']['information_id']) {
                $info['lang']['information_id'] = 'eStudienplaner_info_lang';
            }

            $info['kurz']['datenfelder'] = array_filter(DataFieldEntry::getDataFieldEntries($info['kurz']['information_id'], 'plugin', $plugin_info['id']), function ($item) {
                return !preg_match('/\.profile$/', $item->getName());
            });
            $info['lang']['datenfelder'] = array_filter(DataFieldEntry::getDataFieldEntries($info['lang']['information_id'], 'plugin', $plugin_info['id']), function ($item) {
                return !preg_match('/\.profile$/', $item->getName());
            });

            if ($language !== null && $language === $lang) {
                return $info;
            } else {
                $informationen[$lang] = $info;
            }
        }
        return $informationen;
    }

    public function setInformation($informationen) {
        $db = DBManager::get();
        $plugin_info = PluginManager::getInstance()->getPluginInfo('eStudienplaner');

        //Datenfelder aktualisieren:
        $datafield_entries = DataFieldEntry::getDataFieldEntries($this->profil_id, 'plugin', $plugin_info['id']);
        foreach ($datafield_entries as $datafield_id => $datafield_entry) {
            if (is_array($informationen['datenfelder'][$datafield_id])) {
                //Kombo-Box
                $value = $informationen['datenfelder'][$datafield_id][$informationen['datenfelder'][$datafield_id]['combo']];
            } else {
                $value = $informationen['datenfelder'][$datafield_id];
            }
            $datafield_entry->setValue($value);
            $datafield_entry->store();
        }


        foreach ($informationen as $language => $sprach_infos) {
            foreach (array('kurz', 'lang') as $info_form) {
                if ($info_form === "lang" || PersonalRechte::isRoot()) {
                    $exists = $db->query(
                        "SELECT * " .
                        "FROM stg_profil_information " .
                        "WHERE stg_profil_id = ".$db->quote($this->getId())." " .
                            "AND info_form = ".$db->quote($info_form)." " .
                            "AND sprache = ".$db->quote($language)." " .
                    "")->fetch();
                    $setcascade = "einleitung = ".$db->quote($sprach_infos[$info_form]['einleitung']).", " .
                                "profil = ".$db->quote($sprach_infos[$info_form]['profil']).", " .
                                "inhalte = ".$db->quote($sprach_infos[$info_form]['inhalte']).", " .
                                "lernformen = ".$db->quote($sprach_infos[$info_form]['lernformen']).", " .
                                "gruende = ".$db->quote($sprach_infos[$info_form]['gruende']).", " .
                                "berufsfelder = ".$db->quote($sprach_infos[$info_form]['berufsfelder']).", " .
                                "weitere_infos = ".$db->quote($sprach_infos[$info_form]['weitere_infos']).", " .
                                "aktuelles = ".$db->quote($sprach_infos[$info_form]['aktuelles']).", " .
                                "besonderezugangsvoraussetzungen = ".$db->quote($sprach_infos[$info_form]['besonderezugangsvoraussetzungen']).", " .
                                "schwerpunkte = ".$db->quote($sprach_infos[$info_form]['schwerpunkte']).", " .
                                "sprachkenntnisse = ".$db->quote($sprach_infos[$info_form]['sprachkenntnisse']).", " .
                                "einschreibungsverfahren = ".$db->quote($sprach_infos[$info_form]['einschreibungsverfahren']).", " .
                                "bewerbungsverfahren = ".$db->quote($sprach_infos[$info_form]['bewerbungsverfahren']).", " .
                                "sichtbar = ".$db->quote($sprach_infos[$info_form]['sichtbar'] ? 1 : 0).", " .
                                "vollstaendig = ".$db->quote($sprach_infos[$info_form]['vollstaendig'] ? 1 : 0)." ";
                    if (!$exists) {
                        $db->query("INSERT INTO stg_profil_information " .
                            "SET " .$setcascade .", " .
                                "sprache = ".$db->quote($language).", " .
                                "stg_profil_id = ".$db->quote($this->getId()).", " .
                                "info_form = ".$db->quote($info_form)." " .
                        "");
                        $last_id = $db->query(
                            "SELECT information_id " .
                            "FROM stg_profil_information " .
                            "WHERE stg_profil_id = ".$db->quote($this->getId())." " .
                                "AND info_form = ".$db->quote($info_form)." " .
                                "AND sprache = ".$db->quote($language)." " .
                        "")->fetch();
                        $profil_information_id = $last_id['information_id'];
                    } else {
                        $db->query("UPDATE stg_profil_information " .
                            "SET " .$setcascade ." ".
                            "WHERE sprache = ".$db->quote($language)." " .
                                "AND stg_profil_id = ".$db->quote($this->getId())." " .
                                "AND info_form = ".$db->quote($info_form)." " .
                        "");
                        $profil_information_id = $exists['information_id'];
                    }
                    //Datenfelder aktualisieren:
                    $datafield_entries = DataFieldEntry::getDataFieldEntries($profil_information_id, 'plugin', $plugin_info['id']);
                    if (is_array($datafield_entries)) {
                        foreach ($datafield_entries as $datafield_id => $datafield_entry) {
                            if (is_array($sprach_infos[$info_form]['datenfelder'][$datafield_id])) {
                                //Kombo-Box
                                $value = $sprach_infos[$info_form]['datenfelder'][$datafield_id][$sprach_infos[$info_form]['datenfelder'][$datafield_id]['combo']];
                            } else {
                                $value = $sprach_infos[$info_form]['datenfelder'][$datafield_id];
                            }
                            $datafield_entry->setValue($value);
                            $datafield_entry->store();
                        }
                    }
                }
            }
        }
    }

    static public function getPossibleLanguages() {
        $db = DBManager::get();
        $sprachen = $db->query("SHOW COLUMNS FROM stg_profil_information WHERE Field = 'sprache'")->fetch();
        preg_match_all("/'(\w+)'/", $sprachen['Type'], $sprachen); //Sprachen sollten keine Leerzeichen enthalten
        return $sprachen[1];
    }

    public function getAufbauendeStudiengaenge() {
        $db = DBManager::get();
        $mutterstudiengaenge = $db->query(
            "SELECT aufbau_stg_profil_id " .
            "FROM stg_aufbaustudiengang " .
            "WHERE stg_range_id = ".$db->quote($this->getId())." " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($mutterstudiengaenge as $key => $profil_id) {
            $mutterstudiengaenge[$key] = new StgProfil($profil_id);
        }
        return $mutterstudiengaenge ? $mutterstudiengaenge : array();
    }
    public function addAufbauendenStudiengang($stg_profil_id) {
        $db = DBManager::get();
        $db->query(
            "INSERT IGNORE INTO stg_aufbaustudiengang " .
            "SET stg_range_id = ".$db->quote($this->getId()).", " .
                "aufbau_stg_profil_id = ".$db->quote($stg_profil_id)." " .
        "");
    }
    public function deleteAufbauendenStudiengang($stg_profil_id) {
        $db = DBManager::get();
        $db->query(
            "DELETE FROM stg_aufbaustudiengang " .
            "WHERE stg_range_id = ".$db->quote($this->getId())." " .
                "AND aufbau_stg_profil_id = ".$db->quote($stg_profil_id)." " .
        "");
    }

    public function getKombinationen() {
        $db = DBManager::get();
        return $db->query("SELECT * FROM stg_fach_kombination WHERE stg_profil_id = ".$db->quote($this->getId())." OR kombi_stg_profil_id = ".$db->quote($this->getId()))->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function getName($stg_profil_id) {
        if (isset($infos[$stg_profil_id]['name'])) {
            return $infos[$stg_profil_id]['name'];
        }
        $db = DBManager::get();
        return $infos[$stg_profil_id]['name'] = $db->query("SELECT CONCAT(studiengaenge.name, ' (', abschluss.name , ')') FROM stg_profil " .
                        "LEFT JOIN studiengaenge ON (studiengaenge.studiengang_id = stg_profil.fach_id) " .
                        "LEFT JOIN abschluss ON (abschluss.abschluss_id = stg_profil.abschluss_id) " .
                    "WHERE stg_profil.profil_id = ".$db->quote($stg_profil_id))->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function addAnsprechpartner($StgAnsprechpartner_id) {
        if (!$this->hasPermission()) {
            throw new Exception(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $db = DBManager::get();
        $db->query("INSERT IGNORE INTO stg_ansprech_zuord " .
                    "SET stg_ansprechpartner_id = ".$db->quote($StgAnsprechpartner_id).", " .
                    "stg_profil_id = ".$db->quote($this->getId()));
    }
    public function deleteAnsprechpartner($StgAnsprechpartner_id) {
        if (!$this->hasPermission()) {
            throw new Exception(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $db = DBManager::get();
        $db->query("DELETE FROM stg_ansprech_zuord " .
                    "WHERE stg_ansprechpartner_id = ".$db->quote($StgAnsprechpartner_id)." " .
                    "AND stg_profil_id = ".$db->quote($this->getId()));
    }

    public function addDoku($doku_id) {
        if (!$this->hasPermission()) {
            throw new Exception(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $db = DBManager::get();
        return $db->exec("INSERT IGNORE INTO stg_doku_zuord " .
                    "SET doku_id = ".$db->quote($doku_id).", " .
                    "stg_profil_id = ".$db->quote($this->getId()));
    }

    public function deleteDoku($doku_id) {
        if (!$this->hasPermission()) {
            throw new Exception(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $db = DBManager::get();
        $db->query("DELETE FROM stg_doku_zuord " .
                    "WHERE doku_id = ".$db->quote($doku_id)." " .
                    "AND stg_profil_id = ".$db->quote($this->getId()));
    }

    public function getVerlaufsplaene() {
        $db = DBManager::get();
        $verlaufsplan_statement = $db->prepare(
            "SELECT stg_verlaufsplan.verlaufsplan_id " .
            "FROM stg_verlaufsplan " .
                "LEFT JOIN stg_fach_kombination ON (stg_verlaufsplan.fach_kombi_id = stg_fach_kombination.fach_kombi_id) " .
            "WHERE stg_verlaufsplan.stg_profil_id = :profil_id " .
                "OR stg_fach_kombination.stg_profil_id = :profil_id " .
                "OR stg_fach_kombination.kombi_stg_profil_id = :profil_id " .
        "");
        $verlaufsplan_statement->execute(array('profil_id' => $this->getId()));
        $verlaufsplan_ids = $verlaufsplan_statement->fetchAll(PDO::FETCH_COLUMN, 0);
        $verlaufsplaene = array();
        foreach ($verlaufsplan_ids as $verlaufsplan_id) {
            if ($verlaufsplan_id) {
                $verlaufsplaene[] = new StgVerlaufsplan($verlaufsplan_id);
            }
        }
        return $verlaufsplaene;
    }

    public function delete() {
        if ($this->is_deletable()) {
            $db = DBManager::get();
            $db->exec("DELETE FROM stg_doku_zuord WHERE stg_profil_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_ansprech_zuord WHERE stg_profil_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_aufbaustudiengang WHERE aufbau_stg_profil_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_typ_zuordnung WHERE stg_profil_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_profil_information WHERE stg_profil_id = ".$db->quote($this->getId()));
            $db->exec("DELETE FROM stg_bewerben WHERE stg_profil_id = ".$db->quote($this->getId()));
            return parent::delete();
        } else {
            throw new Exception(_("Dieses Studiengangprofil darf nicht gelöscht werden, weil noch Fächerkombinationen mit ihm existieren."));
            return false;
        }
    }

    public function is_deletable() {
        $db = DBManager::get();
        $dependencies = $db->query(
            "SELECT * " .
            "FROM stg_fach_kombination " .
            "WHERE stg_profil_id = ".$db->quote($this->getId())." " .
                "OR kombi_stg_profil_id = ".$db->quote($this->getId())." " .
        "")->fetch();
        return $dependencies ? false : true;
    }

    public function offsetSet($offset, $value) {
        parent::offsetSet($offset, $value);
        //Trigger werden danach auf dem aktualisierten Bestand ausgeführt.
        //Aber die Datenbank mag noch nicht aktuell sein.
        $this->trigger($offset);
    }

    /*public function setValue($field, $value) {
        parent::setValue($field, $value);
        $this->trigger($field, $value);
    }*/

}


