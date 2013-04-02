<?php
require 'application.php';
require_once dirname(__file__)."/zsb_controller.php";
require_once dirname(__file__).'/../models/PersonalRechte.php';
require_once dirname(__file__).'/../models/Abschluss.class.php';
require_once dirname(__file__).'/../models/Studiengang.class.php';
require_once dirname(__file__).'/../models/StgProfil.class.php';
require_once dirname(__file__).'/../models/StgFile.class.php';
require_once dirname(__file__).'/../models/StgAnsprechpartner.class.php';
require_once dirname(__file__).'/../models/StgVerlaufsplan.class.php';
if (!class_exists("DBHelper")) {
    include_once dirname(__file__).'/../models/DBHelper.class.php';
}
include_once 'lib/classes/exportdocument/ExportPDF.class.php';
require_once __DIR__ . '/../models/Textbaustein.class.php';

SimpleORMap::expireTableScheme();

class ZsbStudiengangController extends ZSBController {

    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::hasPermission()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        if ($GLOBALS['SOFTWARE_VERSION'] < 2.3) {
            PageLayout::addStylesheet('ui.multiselect.css');
        } else {
            PageLayout::addStylesheet('jquery-ui-multiselect.css');
        }
        PageLayout::addScript('ui.multiselect.js');
        URLHelper::bindLinkParam("abschluss_id", Request::get("abschluss_id"));
        URLHelper::bindLinkParam("studiengang_id", Request::get("studiengang_id"));
    }

    public function studiengaenge_action() {
        if (Request::get("delete_x") && Request::get("item_id") && Request::get("type") === "studienprofil") {
            $profil = new StgProfil(Request::get("item_id"));
            if ($profil->delete()) {
                $this->flash_now("success", _("Studiengangsprofil gelöscht"));
            } else {
                $this->flash_now("error", _("Konnte Studiengang nicht löschen"));
            }
        } elseif (Request::get("studienprofil_id")) {
            $this->studiengang_details();
            return;
        }
        $this->profile = array();

        $this->studiengaenge = PersonalRechte::meineStudiengaenge(null, true);
        $this->abschluesse = Abschluss::findAllUsed();
        if (Request::submitted("abschluss_id") or Request::submitted("studiengang_id")) {
            foreach (StgProfil::getMeineProfile(null, Request::get("studiengang_id"), Request::get("abschluss_id")) as $profil) {
                $this->profile[] = array(
                    'content' => array(
                        $profil->getStudiengang(),
                        $profil->getAbschluss()
                    ),
                    'url' => $this->link_for("zsb_studiengang/studiengaenge", array('studienprofil_id' => $profil->getId())),
                    'item' => $profil
                );
            }
        }
    }

    /**
     * Hiernach wird die Action nicht mehr gerendert.
     */
    public function studiengang_details() {

        //Sprachen rausfinden:
        $this->sprachen = StgProfil::getPossibleLanguages();
        //Abschlüsse suchen:
        $this->abschlusssuche = new SQLSearch("SELECT abschluss_id, name FROM abschluss WHERE name LIKE :input", _("Abschluss eingeben"));
        //zulassungsvoraussetzung:
        $this->moeglicheZulassungsvoraussetzungen_wise = DBHelper::getEnumOptions("stg_profil", "zulassungsvoraussetzung_wise");
        $this->moeglicheZulassungsvoraussetzungen_sose = DBHelper::getEnumOptions("stg_profil", "zulassungsvoraussetzung_sose");
        //Typen von Studiengängen:
        $this->moeglicheStgTypen = StgProfil::getMoeglicheTypen();

        $this->studiengaenge = PersonalRechte::meineStudiengaenge(null, true);
        $this->abschluesse = Abschluss::findAllUsed();
        $this->profile = StgProfil::getMeineProfile(null, Request::get("studiengang_id"), Request::get("abschluss_id"));

        //Neuen Eintrag in stg_profil erstellen
        if (Request::get("item_id") === "neu" && !Request::get("studiengang_id")) {
            //$this->profil = new StgProfil();
            $this->fachsuche = new SQLSearch("SELECT studiengang_id, name FROM studiengaenge WHERE name LIKE :input OR beschreibung LIKE :input ", _("Studiengang eingeben"));
            $this->render_template('zsb_studiengang/details_studiengang', $this->layout);
            return;
        }

        $this->profil = new StgProfil(Request::get("studienprofil_id") !== "neu" ? Request::get("studienprofil_id") : null);
        if (!$this->profil->hasPermission()) {
            throw new AccessDeniedException(_("Sie dürfen diesen Studiengang nicht bearbeiten"));
        }
        if (Request::get("delete_x") && Request::get("item_id") && Request::get("type") === "dokument") {
            $this->profil->deleteDoku(Request::get("item_id"));
            $this->flash_now("success", _("Dokumentenzuordnung gelöscht"));
        }
        if (Request::get("addDocument")) {
            $this->profil->addDoku(Request::get("addDocument"));
            $this->flash_now("success", _("Dokumentenzuordnung hinzugefügt"));
        }
        //Profil ändern, falls Informationen gesendet worden sind:
        if (Request::submitted("absenden")) {
            if (Request::get("studiengang_id")) {
                //Neuen Eintrag in stg_profil machen:
                $this->profil['fach_id'] = Request::get("studiengang_id");
                $this->profil->store(); //Nachher wird noch einmal gestored, aber jetzt wollen wir IDs bekommen.
            }
            $this->profil->setInformation(Request::getArray("informationen"));
            $this->profil['abschluss_id'] = Request::get("abschluss_id");
            $settings = Request::getArray("settings");
            foreach ($settings as $key => $value) {
                $this->profil[$key] = $value;
            }
            if (!$settings['sichtbar']) {
                $this->profil['sichtbar'] = 0;
            }
            if (Request::submitted("zielvereinbarung")) {
                $this->profil['zielvereinbarung'] = Request::get("zielvereinbarung");
            }
            if (Request::submitted("einleitungstext")) {
                $this->profil['einleitungstext'] = Request::get("einleitungstext");
            }
            $this->profil->setTypen(Request::getArray("typen"));
            $this->profil->store();

            $textcombinations = (array) @$_REQUEST['textcombination'];
            Textbaustein::removeCombination($this->profil->getId());
            foreach ($textcombinations as $code => $ids) {
                Textbaustein::addCombination($this->profil->getId(), $code, $ids);
            }

            $this->flash_now("success", _("Änderungen wurden übernommen"));
            if (Request::get("neues_dokument")) {
                $dokument = new StgFile();
                if ($_GET['qqfile'] || isset($_FILES['qqfile'])) {
                    ob_start();
                    $dokument->upload();
                    ob_clean();
                }
                $dokument['name'] = Request::get("neues_dokument_name");
                $dokument['sichtbar'] = Request::get("neues_dokument_sichtbar") ? '1' : '0';
                $dokument['jahr'] = Request::get("neues_dokument_jahr");
                $dokument['doku_typ_id'] = Request::get("neues_dokument_doku_typ_id");
                $dokument['quick_link'] = Request::get("neues_dokument_quick_link");
                $dokument->store();
                $dokument->setTags(Request::get("neues_dokument_tags"));
                $this->profil->addDoku($dokument->getId());
                if ($dokument->isPublic()) {
                    $this->flash_now("success", _("Neues Dokument angelegt und hinzugefügt"));
                } else {
                    $this->flash_now("success", _("Neues internes Dokument angelegt und hinzugefügt. Es kann unter Akkreditierung gefunden werden."));
                }
            }
            if (Request::get("neuer_ansprechpartner")) {
                $kontakt = new StgAnsprechpartner();
                $kontakt['range_typ'] = Request::get("range_typ");
                $kontakt['range_id'] = Request::get("range_typ") && Request::get("range_id") ? Request::get("range_id") : "";

                if (Request::get("ansprechpartner_typ_id")) {
                    $typen = StgAnsprechpartner::getAnsprechpartnerTypen();
                    foreach ($typen as $typ) {
                        if ($typ['ansprechpartner_typ_id'] === Request::get("ansprechpartner_typ_id")) {
                            $kontakt['ansprechpartner_typ_id'] = Request::get("ansprechpartner_typ_id");
                        }
                    }
                }
                $kontakt['freitext_name'] = Request::get("freitext_name");
                $kontakt['freitext_mail'] = Request::get("freitext_mail");
                $kontakt['freitext_telefon'] = Request::get("freitext_telefon");
                $kontakt['freitext_homepage'] = Request::get("freitext_homepage");
                if ($kontakt->store()) {
                    $this->profil->addAnsprechpartner($kontakt->getId());
                    $this->flash_now("success", _("Neuer Ansprechpartner angelegt und hinzugefügt"));
                }
            }
        }

        //Informationen:
        $this->informationen = $this->profil->getInformation();

        //Verlaufspläne
        $this->verlaufsplaene = $this->profil->getVerlaufsplaene();

        //Dateien:
        $this->dateien = StgFile::getByStgProfil($this->profil->getId());
        $this->dokumentensuche = $this->getDokumentensuche();

        //Kontakte:
        $this->kontakte = StgAnsprechpartner::getByStgProfil($this->profil->getId());
        $this->kontakt_neu = new StgAnsprechpartner();
        $this->ansprechpartnertypen = StgAnsprechpartner::getAnsprechpartnerTypen();


        //Aufbaustudiengänge:
        $this->mutterstudiengaenge = $this->profil->getAufbauendeStudiengaenge();
        $this->profilsuche = $this->getProfilSuche();


        // Textbausteine
        $this->textbausteine = array(
            'de' => Textbaustein::loadAll('de'),
            'en' => Textbaustein::loadAll('en'),
        );
        $this->textcombinations = Textbaustein::loadCombination($this->profil->getId());

        $this->ansprechpartnersuche = $this->getAnsprechpartnersuche();
        $this->datei_url = $this->link_for("zsb_dateien/download_file");
        $this->render_template('zsb_studiengang/details_studiengang', $this->layout);
        //hiernach wird nichts weiter ausgegeben
    }

    private function getDokumentensuche() {
        global $user;
        $db = DBManager::get();
        if (PersonalRechte::isRoot()) {
            return new SQLSearch(
                    "SELECT stg_dokumente.doku_id, CONCAT(stg_dokumente.name, ' - ', stg_dokumente.filename) " .
                    "FROM stg_dokumente " .
                    "WHERE CONCAT(stg_dokumente.name, ' - ', stg_dokumente.filename) LIKE :input " .
                "", _("Dokument suchen und hinzufügen"));
        } elseif (PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
            //P-Amt und I-Amt
            return new SQLSearch(
                    "SELECT DISTINCT stg_dokumente.doku_id, CONCAT(stg_dokumente.name, ' - ', stg_dokumente.filename) " .
                    "FROM stg_dokumente " .
                        "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_doku_typ_bereich_zuord.stg_doku_typ_id = stg_dokumente.doku_typ_id) " .
                        "INNER JOIN stg_bereiche ON (stg_doku_typ_bereich_zuord.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                        "JOIN roles_user " .
                        "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                        "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                        "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
                    "WHERE CONCAT(stg_dokumente.name, ' - ', stg_dokumente.filename) LIKE :input " .
                        "AND roles_user.userid = ".$db->quote($user->id)." " .
                        "AND plugins.pluginclassname = 'eStudienplaner' " .
                        "AND ( " .
                            "(roles.rolename = 'stg_p-amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                            "OR (roles.rolename = 'stg_i-amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                        ") " .
                "", _("Dokument suchen und hinzufügen"));
        } else {
            //FSB, StuKo
            return new SQLSearch(
                    "SELECT DISTINCT stg_dokumente.doku_id, CONCAT(stg_dokumente.name, ' - ', stg_dokumente.filename) " .
                    "FROM stg_dokumente " .
                        "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_doku_typ_bereich_zuord.stg_doku_typ_id = stg_dokumente.doku_typ_id) " .
                        "INNER JOIN stg_bereiche ON (stg_doku_typ_bereich_zuord.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                        "JOIN stg_fsb_rollen " .
                    "WHERE CONCAT(stg_dokumente.name, ' - ', stg_dokumente.filename) LIKE :input " .
                        "AND stg_fsb_rollen.user_id = ".$db->quote($user->id)." " .
                        "AND (" .
                            "(stg_fsb_rollen.rollen_typ = 'FSB' AND stg_bereiche.sichtbar_fsb = '1') " .
                        ") " .
                "", _("Dokument suchen und hinzufügen"));
        }
    }

    private function getAnsprechpartnersuche() {
        global $user;
        $db = DBManager::get();
        if (PersonalRechte::isRoot()) {
            return new SQLSearch(
                    "SELECT DISTINCT stg_ansprechpartner.ansprechpartner_id, CONCAT(IF(Institute.Institut_id IS NULL, IF(auth_user_md5.user_id IS NULL, stg_ansprechpartner.freitext_name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)), Institute.Name), ' (', stg_ansprechpartner_typ.name, ')') " .
                    "FROM stg_ansprechpartner " .
                        "LEFT JOIN Institute ON (stg_ansprechpartner.range_typ = 'institute' AND stg_ansprechpartner.range_id = Institute.Institut_id) " .
                        "LEFT JOIN auth_user_md5 ON (stg_ansprechpartner.range_typ = 'auth_user_md5' AND stg_ansprechpartner.range_id = auth_user_md5.user_id) " .
                        "INNER JOIN stg_ansprechpartner_typ ON (stg_ansprechpartner_typ.ansprechpartner_typ_id = stg_ansprechpartner.ansprechpartner_typ_id) " .
                    "WHERE IF(Institute.Institut_id IS NULL, IF(auth_user_md5.user_id IS NULL, stg_ansprechpartner.freitext_name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)), Institute.Name) LIKE :input " .
            "", _("Ansprechpartner hinzufügen"));
        } elseif (PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
            //sollte eigentlich gar nicht geschehen, weil der Reiter für diese Rollen nicht sichtbar ist
            return new SQLSearch(
                    "SELECT DISTINCT stg_ansprechpartner.ansprechpartner_id, CONCAT(IF(Institute.Institut_id IS NULL, IF(auth_user_md5.user_id IS NULL, stg_ansprechpartner.freitext_name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)), Institute.Name), ' (', stg_ansprechpartner_typ.name, ')') " .
                    "FROM stg_ansprechpartner " .
                        "LEFT JOIN Institute ON (stg_ansprechpartner.range_typ = 'institute' AND stg_ansprechpartner.range_id = Institute.Institut_id) " .
                        "LEFT JOIN auth_user_md5 ON (stg_ansprechpartner.range_typ = 'auth_user_md5' AND stg_ansprechpartner.range_id = auth_user_md5.user_id) " .
                        "INNER JOIN stg_ansprechpartner_typ ON (stg_ansprechpartner_typ.ansprechpartner_typ_id = stg_ansprechpartner.ansprechpartner_typ_id) " .
                        "INNER JOIN stg_bereiche ON (stg_ansprechpartner_typ.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                        "JOIN roles_user " .
                        "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                        "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                        "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
                    "WHERE IF(Institute.Institut_id IS NULL, IF(auth_user_md5.user_id IS NULL, stg_ansprechpartner.freitext_name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)), Institute.Name) LIKE :input " .
                        "AND roles_user.userid = ".$db->quote($user->id)." " .
                        "AND plugins.pluginclassname = 'eStudienplaner' " .
                        "AND ( " .
                            "(roles.rolename = 'stg_p-amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                            "OR (roles.rolename = 'stg_i-amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                        ") " .
                "", _("Ansprechpartner hinzufügen"));
        } else {
            return new SQLSearch(
                    "SELECT DISTINCT stg_ansprechpartner.ansprechpartner_id, CONCAT(IF(Institute.Institut_id IS NULL, IF(auth_user_md5.user_id IS NULL, stg_ansprechpartner.freitext_name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)), Institute.Name), ' (', stg_ansprechpartner_typ.name, ')') " .
                    "FROM stg_ansprechpartner " .
                        "LEFT JOIN Institute ON (stg_ansprechpartner.range_typ = 'institute' AND stg_ansprechpartner.range_id = Institute.Institut_id) " .
                        "LEFT JOIN auth_user_md5 ON (stg_ansprechpartner.range_typ = 'auth_user_md5' AND stg_ansprechpartner.range_id = auth_user_md5.user_id) " .
                        "INNER JOIN stg_ansprechpartner_typ ON (stg_ansprechpartner_typ.ansprechpartner_typ_id = stg_ansprechpartner.ansprechpartner_typ_id) " .
                        "INNER JOIN stg_bereiche ON (stg_ansprechpartner_typ.stg_bereichs_id = stg_bereiche.bereichs_id) " .
                        "JOIN stg_fsb_rollen " .
                    "WHERE IF(Institute.Institut_id IS NULL, IF(auth_user_md5.user_id IS NULL, stg_ansprechpartner.freitext_name, CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname)), Institute.Name) LIKE :input " .
                        "AND stg_fsb_rollen.user_id = ".$db->quote($user->id)." " .
                        "AND (" .
                            "(stg_fsb_rollen.rollen_typ = 'FSB' AND stg_bereiche.sichtbar_fsb = '1') " .
                            "OR (stg_fsb_rollen.rollen_typ = 'P-Amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                            "OR (stg_fsb_rollen.rollen_typ = 'I-Amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                        ") " .
            "", _("Ansprechpartner hinzufügen"));
        }
    }

    public function add_aufbaustudiengang_action() {
        $profil = new StgProfil(Request::get("profil_id"));
        $profil->addAufbauendenStudiengang(Request::get("aufbaustudiengang_id"));
        $this->render_nothing();
    }
    public function delete_aufbaustudiengang_action() {
        $profil = new StgProfil(Request::get("profil_id"));
        $profil->deleteAufbauendenStudiengang(Request::get("aufbau_stg_profil_id"));
        $this->render_nothing();
    }

    public function kontakt_profil_order_action() {
        $profil = new StgProfil(Request::get("profil_id"));
        if (!$profil->hasPermission()) {
            throw new AccessDeniedException(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $db = DBManager::get();
        $reihenfolge = Request::getArray("ansprechpartner");
        foreach ($reihenfolge as $position => $id) {
            $db->query(
                "UPDATE stg_ansprech_zuord " .
                "SET position = ".$db->quote(count($reihenfolge) - $position)." " .
                "WHERE stg_ansprechpartner_id = ".$db->quote($id)." " .
                    "AND stg_profil_id = ".$db->quote($profil->getId())." " .
            "");
        }
        $this->render_nothing();
    }

    public function dokumente_profil_order_action() {
        $profil = new StgProfil(Request::get("profil_id"));
        if (!$profil->hasPermission()) {
            throw new AccessDeniedException(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $db = DBManager::get();
        $reihenfolge = Request::getArray("dokumente");
        foreach ($reihenfolge as $position => $id) {
            $db->query(
                "UPDATE stg_doku_zuord " .
                "SET position = ".$db->quote(count($reihenfolge) - $position)." " .
                "WHERE doku_id = ".$db->quote($id)." " .
                    "AND stg_profil_id = ".$db->quote($profil->getId())." " .
            "");
        }
        $this->render_nothing();
    }

    public function profil_pdf_action() {
        $profil = new StgProfil(Request::option("stg_profil_id"));
        if (!$profil->hasPermission()) {
            throw new AccessDeniedException(_("Sie dürfen diesen Studiengang nicht bearbeiten."));
            return;
        }
        $sprachname = array(
            'de_DE' => _("Deutsch"),
            'en_GB' => _("Englisch")
        );
        $pdf = new ExportPDF();
        $pdf->setHeaderTitle(StgProfil::getName($profil->getId()));
        $pdf->setHeaderSubtitle("Datenblatt des Studiengangprofils");
        //Einstellungen / Allgemeine Daten
        $pdf->addPage();
        $pdf->addContent("!!"._("Allgemeine Daten"));
        $factsheettabelle = "";
        $factsheettabelle .= "| Studiendauer | ".$profil['studiendauer']." |\n";
        $factsheettabelle .= "| Studienplätze | ".$profil['studienplaetze']." |\n";
        $factsheettabelle .= "| Typen | ";
        foreach ($profil->getTypen() as $typ_id) {
            $factsheettabelle .= StgFile::getDokuTypName($typ_id);
        }
        $factsheettabelle .= "|\n";
        $factsheettabelle .= "| Zulassungsbeschränkung im Wintersemester | ".$profil['zulassungsvoraussetzung_wise']." |\n";
        $factsheettabelle .= "| Zulassungsbeschränkung im Sommersemester | ".$profil['zulassungsvoraussetzung_sose']." |\n";

        $aufbauendeStudiengaenge = $profil->getAufbauendeStudiengaenge();
        if (count($aufbauendeStudiengaenge)) {
            $factsheettabelle .= "| Aufbaustudiengang | ";
            foreach ($aufbauendeStudiengaenge as $key => $mutterstudiengang) {
                $key === 0 || ($factsheettabelle .= ", ");
                $factsheettabelle .= StgProfil::getName($mutterstudiengang->getId());
            }
            $factsheettabelle .= " |\n";
        }

        $kombinationen = $profil->getKombinationen();
        if (count($kombinationen)) {
            $factsheettabelle .= "| Mögliche Kombinationen | ";
            foreach ($kombinationen as $key => $studiengang) {
                $key === 0 || ($factsheettabelle .= "; ");
                $factsheettabelle .= StgProfil::getName($studiengang['stg_profil_id']) . " - " .StgProfil::getName($studiengang['kombi_stg_profil_id']);
            }
            $factsheettabelle .= " |\n";
        }

        $pdf->addContent($factsheettabelle);

        if ($profil['ausland']) {
            $pdf->addContent("!"._("Ausland"));
            $pdf->addContent($profil['ausland']);
        }

        //Informationen
        $informationen = $profil->getInformation();
        foreach (StgProfil::getPossibleLanguages() as $sprache) {
            foreach (array('kurz', 'lang') as $info_form) {
                $pdf->addPage();
                $pdf->addContent("!!".sprintf(_("%s Informationen auf %s"), $info_form === "kurz" ? _("Kurze") : _("Ausführliche"), $sprachname[$sprache] ? $sprachname[$sprache] : $sprache));

                foreach (array(
                    'einleitung' => "Einleitung",
                    'profil' => "Profil",
                    'inhalte' => "Inhalte",
                    'lernformen' => "Lernformen",
                    'gruende' => "Gründe",
                    'berufsfelder' => "Berufsfelder",
                    'weitere_infos' => "Weitere Infos",
                    'aktuelles' => "Aktuelles",
                    'besonderezugangsvoraussetzungen' => "Besondere Zugangsvoraussetzungen",
                    'schwerpunkte' => "Schwerpunkte",
                    'sprachkenntnisse' => "Sprachkenntnisse"
                ) as $subject => $text) {
                    if ($informationen[$sprache][$info_form][$subject]) {
                        $pdf->addContent("!".dgettext($sprache, $text));
                        $pdf->addContent($informationen[$sprache][$info_form][$subject]);
                    }
                }


                /*$pdf->addContent("!".dgettext($sprache, "Sichtbar"));
                $pdf->addContent($informationen[$sprache][$info_form]['sichtbar'] ? dgettext($sprache, "Ja") : dgettext($sprache, "Nein"));

                $pdf->addContent("!".dgettext($sprache, "Vollständig"));
                $pdf->addContent($informationen[$sprache][$info_form]['vollstaendig'] ? dgettext($sprache, "Ja") : dgettext($sprache, "Nein"));
                 */

                foreach (array(
                    'einschreibungsverfahren' => "Einschreibungsverfahren",
                    'bewerbungsverfahren' => "Bewerbungsverfahren"
                ) as $subject => $text) {
                    if ($informationen[$sprache][$info_form][$subject]) {
                        $pdf->addContent("!".dgettext($sprache, $text));
                        $pdf->addContent($informationen[$sprache][$info_form][$subject]);
                    }
                }
                if (is_array($informationen[$sprache][$info_form]['datenfelder'])) {
                    foreach($informationen[$sprache][$info_form]['datenfelder'] as $datafield_id => $datafield) {
                        if ($datafield->getDisplayValue("informationen[$sprache][$info_form][datenfelder]")) {
                            $pdf->addContent("!".$datafield->getName());
                            $pdf->addContent($datafield->getDisplayValue("informationen[$sprache][$info_form][datenfelder]"));
                        }
                    }
                }

            }
        }

        //Ansprechpartner
        $pdf->addPage();
        $ansprechpartner = StgAnsprechpartner::getByStgProfil($profil->getId());
        foreach ($ansprechpartner as $key => $kontakt) {
            if (!$kontakt->isPublic()) {
                unset($ansprechpartner[$key]);
            }
        }
        if (count($ansprechpartner)) {
            $pdf->addContent("!!"._("Verknüpfte Ansprechpartner"));
            $ansprechpartner_content = "";
            foreach ($ansprechpartner as $key => $kontakt) {
                $ansprechpartner_content .= "- ".$kontakt->getName()." (".StgAnsprechpartner::getAnsprechpartnerTypName($kontakt['ansprechpartner_typ_id']). ")\n";
            }
            $ansprechpartner_content .= "\n";
            $pdf->addContent($ansprechpartner_content);
        }

        //Dokumente
        //$pdf->addPage();
        $dokumente = StgFile::getByStgProfil($profil->getId());
        foreach ($dokumente as $key => $dokument) {
            if (!$dokument->isPublic()) {
                unset($dokumente[$key]);
            }
        }
        if (is_array($dokumente) && count($dokumente)) {
            $pdf->addContent("!!"._("Verknüpfte Dokumente"));
            $pdf->addContent("\n\n");
            $dokumententabelle = "\n\n\n\n| Name | Dateiname | Typ | Letzte Änderung | Größe |\n";
            foreach ($dokumente as $dokument) {
                $dokumententabelle .= "| ".htmlReady($dokument['name']) .
                                    " | ".htmlReady($dokument['filename']) .
                                    " | ".htmlReady(StgFile::getDokuTypName($dokument['doku_typ_id'])) .
                                    " | ".htmlReady($dokument['chdate']) .
                                    " | ".$dokument->getSize()." |\n";
            }
            $dokumententabelle .= "\n";
            $pdf->addContent($dokumententabelle);
        }

        //Ausgabe:
        $pdf->dispatch("Datenblatt ".StgProfil::getName($profil->getId()));

        $this->render_nothing();
    }

}

