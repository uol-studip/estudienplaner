<?php
$sprachname = array(
    'de_DE' => _("Deutsch"),
    'en_GB' => _("Englisch")
);
$abschlusssuchfeld = QuickSearch::get("abschluss_id", $abschlusssuche);
if ($profil) {
    $abschlusssuchfeld->defaultValue($profil['abschluss_id'], $profil->getAbschluss());
}
$abschlussfeld = $abschlusssuchfeld->render();

?>
<form action="?" method="post" enctype="multipart/form-data">
<?= add_safely_security_token() ?>
<input type="hidden" id="profil_id" name="item_id" value="<?= $profil ? $profil->getId() : "neu" ?>">
<h2 style="text-align: center;">
<? if ($profil) : ?>
<?= $profil->getStudiengang()." ".$profil->getAbschluss() ?>
<? else : ?>
<?= _("Neue Kombination Studiengang und Abschluss anlegen") ?>
<? endif ?>
</h2>
<div id="settings" class="accordion">
    <?  if (!((PersonalRechte::isPamt() || PersonalRechte::isIamt()) && !PersonalRechte::isRoot())) : ?>
    <h2><?= _("Einstellungen") ?></h2>
    <div>
        <ul class="zsb_detail_list">
            <? if (!$profil) : ?>
            <li>
                <label for="studiengang_id_2"><?= _("Studiengang") ?></label>
                <?= QuickSearch::get("studiengang_id", $fachsuche)->render() ?>
                <p class="info"><?= _("Tippen Sie den Namen des Studiengangs ein und w�hlen Sie aus der Liste das Richtige aus.") ?></p>
            </li>
            <? endif ?>
            <li>
                <label for="studiendauer"><?= _("Studiendauer") ?></label>
                <input type="text" name="settings[studiendauer]" id="studiendauer" value="<?= htmlReady($profil['studiendauer']) ?>">
            </li>
            <li>
                <label for="studienplaetze"><?= _("Studienpl�tze") ?></label>
                <input type="text" name="settings[studienplaetze]" id="studienplaetze" value="<?= htmlReady($profil['studienplaetze']) ?>">
            </li>
            <li>
                <label for="lehrsprache"><?= _("Lehrsprache") ?></label>
                <select name="settings[lehrsprache]" id="lehrsprache">
                    <option value="de"<?= $profil['lehrsprache'] === "de" ? " selected" : "" ?>><?= _("Deutsch") ?></option>
                    <option value="en"<?= $profil['lehrsprache'] === "en" ? " selected" : "" ?>><?= _("Englisch") ?></option>
                </select>
            </li>
            <li>
                <label for="status"><?= _("Status") ?></label>
                <select name="settings[status]" id="status">
                    <option value="discontinued"<?= $profil['status'] === "discontinued" ? " selected" : "" ?>><?= _("Auslaufend") ?></option>
                    <option value="current"<?= $profil['status'] === "current" ? " selected" : "" ?>><?= _("Aktuell") ?></option>
                    <option value="planned"<?= $profil['status'] === "planned" ? " selected" : "" ?>><?= _("In Planung") ?></option>
                </select>
            </li>
            <li>
                <label for="sichtbar"><?= _("Sichtbar nach Au�en") ?></label>
                <input type="checkbox" value="1" name="settings[sichtbar]" id="sichtbar"<?= $profil['sichtbar'] ? " checked" : "" ?>>
            </li>
            <li>
                <label for="abschluss_id_1"><?= _("Abschluss") ?></label>
                <?= $abschlussfeld ?>
                <p class="info"><?= _("Tippen Sie den Namen des Abschlusses ein und w�hlen Sie aus der Liste das Richtige aus.") ?></p>
            </li>
            <li>
                <label for="stg_typ"><?= _("Typen") ?></label><br>
                <select name="typen[]" id="stg_typ" multiple style="display:block; width: 80%; height: 150px;">
                <? foreach ($moeglicheStgTypen as $typ) : ?>
                    <option value="<?= htmlReady($typ['stg_typ_id']) ?>"<?=
                        ($profil && in_array($typ['stg_typ_id'], $profil->getTypen()))
                            ? " selected"
                            : "" ?>><?= htmlReady($typ['typ_name']) ?></option>
                <? endforeach ?>
                </select>
                <script>
                    jQuery(function () { STUDIP.MultiSelect.create("#stg_typ", "Typen"); });
                </script>
            </li>
            <li>
                <label for="zulassungsvorraussetzung_wise"><?= _("Zulassungsbeschr�nkung im Wintersemester") ?></label>
                <select name="settings[zulassungsvoraussetzung_wise]" id="zulassungsvorraussetzung_wise">
                <? foreach ($moeglicheZulassungsvoraussetzungen_wise as $option) : ?>
                    <option value="<?= htmlReady($option) ?>"<?= $option === $profil['zulassungsvoraussetzung_wise'] ? " selected" : "" ?>><?= htmlReady($option) ?></option>
                <? endforeach ?>
                </select>
            </li>
            <li>
                <label for="zulassungsvorraussetzung_sose"><?= _("Zulassungsbeschr�nkung im Sommersemester") ?></label>
                <select name="settings[zulassungsvoraussetzung_sose]" id="zulassungsvorraussetzung_sose">
                <? foreach ($moeglicheZulassungsvoraussetzungen_sose as $option) : ?>
                    <option value="<?= htmlReady($option) ?>"<?= $option === $profil['zulassungsvoraussetzung_sose'] ? " selected" : "" ?>><?= htmlReady($option) ?></option>
                <? endforeach ?>
                </select>
            </li>
            <? if ($profil) : ?>
            <li>
                <label for="mutterstudiengang_profil_id_2"><?= _("Aufbaustudiengang") ?></label>
                <ul id="aufbaustudiengaenge">
                    <? foreach ($mutterstudiengaenge as $stg_profil) : ?>
                    <li id="aufbau_stg_profil_id_<?= $stg_profil->getId() ?>">
                        <?= StgProfil::getName($stg_profil->getId()) ?>
                        <a class="icon_trash"></a>
                    </li>
                    <? endforeach ?>
                </ul>
                <?= QuickSearch::get("mutterstudiengang_profil_id", $profilsuche)
                            ->noSelectbox()
                            ->fireJSFunctionOnSelect("STUDIP.zsb.addAufbaustudiengang")
                            ->render() ?>
                <p class="info"><?= _("Tippen Sie den Namen des Studiengangs ein und w�hlen Sie aus der Liste das Richtige aus.") ?></p>
            </li>
            <? endif ?>
            <? if ($profil) : ?>
            <li>
                <?= _("Angeboten in Kombination mit") ?>
                <ul>
                    <? $kombinationen = $profil->getKombinationen() ?>
                    <? if ($kombinationen) : ?>
                    <? foreach ($kombinationen as $kombi) : ?>
                    <li>
                        <? if (PersonalRechte::isRoot()) : ?>
                        <a href="<?= URLHelper::getLink(Navigation::getItem("/zsb/fach_kombinationen")->getURL(), array('kombi_id' => $kombi['fach_kombi_id'])) ?>" class="icon_link-intern">
                        <? endif ?>
                        <?= $kombi['stg_profil_id'] === $profil->getId()
                            ? htmlReady(StgProfil::getName($kombi['kombi_stg_profil_id']))
                            : htmlReady(StgProfil::getName($kombi['stg_profil_id'])) ?>
                        <? if (PersonalRechte::isRoot()) : ?></a><? endif ?>
                    </li>
                    <? endforeach ?>
                    <? else : ?>
                    <li><?= _("keinem Fach") ?></li>
                    <? endif ?>
                </ul>
            </li>
            <? endif ?>
            <li>
                <label for="ausland"><?= _("Ausland") ?></label>
                <textarea name="settings[ausland]" id="ausland"><?= htmlReady($profil['ausland']) ?></textarea>
            </li>
        </ul>
    </div>
    <? foreach ($sprachen as $key => $sprache) : ?>
    <? foreach (array('kurz', 'lang') as $info_form) : ?>
    <h2><?= sprintf(_("%s Informationen auf %s"), $info_form === "kurz" ? _("Kurze") : _("Ausf�hrliche"), $sprachname[$sprache] ? $sprachname[$sprache] : $sprache) ?></h2>
    <div>
        <ul class="zsb_detail_list">
            <? //dgettext funktioniert nicht, da nur eine Domain angemeldet ist in Stud.IP. Laufen sollte es trotzdem. ?>
            <li>
                <label for="einleitung_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Einleitung") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][einleitung]" id="einleitung_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['einleitung']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['einleitung']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="profil_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Profil") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][profil]" id="profil_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['profil']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['profil']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="inhalte_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Inhalte") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][inhalte]" id="inhalte_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['inhalte']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['inhalte']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="lernformen_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Lernformen") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][lernformen]" id="lernformen_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['lernformen']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['lernformen']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="gruende_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Gr�nde") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][gruende]" id="gruende_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['gruende']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['gruende']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="berufsfelder_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Berufsfelder") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][berufsfelder]" id="berufsfelder_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['berufsfelder']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['berufsfelder']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="weitere_infos_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Weitere Infos") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][weitere_infos]" id="weitere_infos_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['weitere_infos']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['weitere_infos']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="aktuelles_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Aktuelles") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][aktuelles]" id="aktuelles_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['aktuelles']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['aktuelles']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="besonderezugangsvoraussetzungen_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Besondere Zugangsvoraussetzungen") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][besonderezugangsvoraussetzungen]" id="besonderezugangsvoraussetzungen_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['besonderezugangsvoraussetzungen']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['besonderezugangsvoraussetzungen']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="schwerpunkte_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Schwerpunkte") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][schwerpunkte]" id="schwerpunkte_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['schwerpunkte']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['schwerpunkte']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="sprachkenntnisse_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Sprachkentnisse") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][sprachkenntnisse]" id="sprachkenntnisse_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['sprachkenntnisse']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['sprachkenntnisse']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="sichtbar_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Sichtbar") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <input type="checkbox" name="informationen[<?= $sprache ?>][<?= $info_form ?>][sichtbar]" id="sichtbar_<?= $sprache ?>_<?= $info_form ?>" value="1"<?= $informationen[$sprache][$info_form]['sichtbar'] ? " checked" : "" ?>>
                <? else : ?>
                <div class="description"><?= $informationen[$sprache][$info_form]['sichtbar'] ? Assets::img("icons/16/grey/accept.png") : Assets::img("icons/16/grey/decline.png") ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="vollstaendig_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Vollst�ndig") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <input type="checkbox" name="informationen[<?= $sprache ?>][<?= $info_form ?>][vollstaendig]" id="vollstaendig_<?= $sprache ?>_<?= $info_form ?>" value="1"<?= $informationen[$sprache][$info_form]['vollstaendig'] ? " checked" : "" ?>>
                <? else : ?>
                <div class="description"><?= $informationen[$sprache][$info_form]['vollstaendig'] ? Assets::img("icons/16/grey/accept.png") : Assets::img("icons/16/grey/decline.png") ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="einschreibungsverfahren_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Einschreibungsverfahren") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][einschreibungsverfahren]" id="einschreibungsverfahren_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['einschreibungsverfahren']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['einschreibungsverfahren']) ?></div>
                <? endif ?>
            </li>
            <li>
                <label for="bewerbungsverfahren_<?= $sprache ?>_<?= $info_form ?>"><?= dgettext($sprache, "Bewerbungsverfahren") ?></label>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <textarea name="informationen[<?= $sprache ?>][<?= $info_form ?>][bewerbungsverfahren]" id="bewerbungsverfahren_<?= $sprache ?>_<?= $info_form ?>"><?= htmlReady($informationen[$sprache][$info_form]['bewerbungsverfahren']) ?></textarea>
                <? else : ?>
                <div class="description"><?= htmlReady($informationen[$sprache][$info_form]['bewerbungsverfahren']) ?></div>
                <? endif ?>
            </li>
            <? if (is_array($informationen[$sprache][$info_form]['datenfelder'])) : ?>
            <? foreach($informationen[$sprache][$info_form]['datenfelder'] as $datafield_id => $datafield) : ?>
            <li>
                <label for="df_<?= $datafield->getId() ?>"><?= htmlReady($datafield->getName()) ?>:</label><br>
                <? if ($info_form === "lang" || PersonalRechte::isRoot()) : ?>
                <?= $datafield->getHtml("informationen[$sprache][$info_form][datenfelder]") ?>
                <? else : ?>
                <div class="description"><?= $datafield->getDisplayValue("informationen[$sprache][$info_form][datenfelder]") ?></div>
                <? endif ?>
            </li>
            <? endforeach ?>
            <? endif ?>
        </ul>
        
    </div>
    <? endforeach ?>
    <? endforeach ?>
    <? endif ?>
    <? if ($profil) : ?>
    <h2><?= _("Verlaufspl�ne") ?></h2>
    <div>
        <ul>
            <? foreach ($verlaufsplaene as $verlaufsplan) : ?>
            <li>
                <?= htmlReady($verlaufsplan['titel']) ?>
                <? if (Navigation::hasItem("/zsb/verlaufsplan")) : ?>
                <a href="<?= URLHelper::getLink(Navigation::getItem("/zsb/verlaufsplan")->getURL(), array('item_id' => $verlaufsplan->getId())) ?>" class="icon_edit">
                </a>
                <? endif ?>
            </li>
            <? endforeach ?>
        </ul>
        <div style="text-align: center;">
            <a class="icon_doctoral_cap" href="<?= PluginEngine::getLink($plugin, array('item_id' => "neu", 'stg_profil_id' => $profil->getId()), "zsb_verlaufsplan/verlaufsplan") ?>">
                <?= _("Neuen Verlaufsplan erstellen") ?>
            </a>
        </div>
    </div>
    <h2><?= _("Verkn�pfte Dokumente") ?></h2>
    <div>
        <div style="float:left; width: 45%;">
            <?= QuickSearch::get("doku_id", $dokumentensuche)
                        ->noSelectbox()
                        ->fireJSFunctionOnSelect("STUDIP.zsb.profilAddDokumentToStudiengaenge")
                        ->render() ?>
            <ul class="sortable" id="dateien">
                <? foreach ($dateien as $key => $datei) : ?>
                <? if ($datei->isPublic()) : ?>
                <li id="doku_id_<?= $datei->getId() ?>">
                    <? /*<a class="icon_file" href="<?= URLHelper::getLink($datei_url, array('doku_id' => $datei->getId())) ?>"></a>*/ ?>
                    <?= htmlReady($datei['name']." (".StgFile::getDokuTypName($datei['doku_typ_id']).")") ?>
                    <? if (Personalrechte::isRoot() || in_array($datei->getId(), Personalrechte::meineDateien())) : ?>
                        <a class="icon_trash"></a>
                        <a class="icon_edit"></a>
                    <? else : ?>
                        <? Assets::img("icons/16/grey/trash.png") ?>
                        <? Assets::img("icons/16/grey/edit.png") ?>
                    <? endif ?>
                </li>
                <? else : ?>
                    <? $versteckteDokumente = true ?>
                <? endif ?>
                <? endforeach ?>
            </ul>
            <p class="info">
                <?= _("Ziehen Sie die Dokumente, um die interne Reihenfolge zu bestimmen. Diese �nderung wird sofort wirksam, auch wenn Sie nicht auf <i>absenden</i> klicken.") ?>
            </p>
            <div id="dokument_delete_question" style="display: none;">
                <?= _("M�chten Sie wirklich die Verbindung zu dem Dokument l�schen?") ?>
                <input type="hidden" id="dokument_delete_question_id">
                <br>
                <a href="" onClick="STUDIP.zsb.deleteDokumentFromProfil(); return false;"><?= makebutton("ok") ?></a>
                <a href="" onClick="jQuery('#dokument_delete_question').dialog('close'); return false;"><?= makebutton("abbrechen") ?></a>
            </div>
        </div>
        <div style="float:left; width: 45%; margin-left: 5px; border: thin solid #aaaaaa; -moz-border-radius: 10px; padding: 5px;">
            <label for="neues_dokument"><?= _("Direkt neues Dokument hochladen und hinzuf�gen") ?></label>
            <input type="checkbox" id="neues_dokument" name="neues_dokument" onClick="jQuery('#neues_dokument_daten').toggle('slide');">
            <table id="neues_dokument_daten" style="display: none;">
                <tbody>
                    <tr>
                        <td>
                            <label for="neues_dokument_name"><?= _("Name") ?></label>
                        </td>
                        <td>
                            <input type="text" name="neues_dokument_name" id="neues_dokument_name">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="neues_dokument_sichtbar"><?= _("Sichtbar") ?></label>
                        </td>
                        <td>
                            <input type="checkbox" name="neues_dokument_sichtbar" id="neues_dokument_sichtbar" checked>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="neues_dokument_jahr"><?= _("Jahr") ?></label>
                        </td>
                        <td>
                            <input type="input" name="neues_dokument_jahr" id="neues_dokument_jahr">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="neues_dokument_doku_typ_id"><?= _("Dokumententyp") ?></label>
                        </td>
                        <td>
                            <select name="neues_dokument_doku_typ_id" id="neues_dokument_doku_typ_id">
                                <? foreach (StgFile::getTypen() as $typ) : ?>
                                <option value="<?= htmlReady($typ['doku_typ_id']) ?>"><?= htmlReady($typ['name']) ?></option>
                                <? endforeach ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="neues_dokument_quick_link"><?= _("Dokument") ?></label>
                        </td>
                        <td>
                            <?= _("URL") ?><br>
                            <input type="text" name="neues_dokument_quick_link" id="neues_dokument_quick_link"><br>
                            <?= _("oder hochladen") ?><br>
                            <input type="file" name="qqfile"><!-- muss qqfile hei�en, damit es angenommen wird -->
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="neues_dokument_tags"><?= _("Schlagw�rter") ?></label>
                        </td>
                        <td>
                            <textarea name="neues_dokument_tags" id="neues_dokument_tags" class="clean" style="width: 270px; height: 70px;"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <? endif ?>
    <? if ($profil) : ?>
    <h2><?= _("Verkn�pfte Ansprechpartner") ?></h2>
    <div>
        <div style="float:left; width: 45%;">
            <?= QuickSearch::get("ansprechpartner_id", $ansprechpartnersuche)
                        ->noSelectbox()
                        ->fireJSFunctionOnSelect("STUDIP.zsb.addAnsprechpartnerToStudiengaenge")
                        ->render() ?>
            <ul class="sortable" id="ansprechpartner">
                <? foreach ($kontakte as $key => $kontakt) : ?>
                <? if ($kontakt->isPublic()) : ?>
                <li id="ansprechpartner_<?= $kontakt->getId() ?>">
                    <?= htmlReady($kontakt->getName()." (".StgAnsprechpartner::getAnsprechpartnerTypName($kontakt['ansprechpartner_typ_id']).")") ?>
                    <? if (Personalrechte::isRoot() || in_array($kontakt->getId(), PersonalRechte::meineAnsprechpartner())) : ?>
                        <a class="icon_trash"></a>
                        <a class="icon_edit"></a>
                    <? else : ?>
                        <?= Assets::img("icons/16/grey/trash.png") ?>
                        <?= Assets::img("icons/16/grey/edit.png") ?>
                    <? endif ?>
                </li>
                <? else : ?>
                    <? $versteckteAnsprechpartner = true ?>
                <? endif ?>
                <? endforeach ?>
            </ul>
            <p class="info">
                <?= _("Ziehen Sie die Ansprechpartner, um die interne Reihenfolge zu bestimmen. Diese �nderung wird sofort wirksam, auch wenn Sie nicht auf <i>absenden</i> klicken.") ?>
            </p>
            <div id="ansprechpartner_delete_question" style="display: none;">
                <?= _("M�chten Sie wirklich die Verbindung zu dem Dokument l�schen?") ?>
                <input type="hidden" id="ansprechpartner_delete_question_id">
                <br>
                <a href="" onClick="STUDIP.zsb.deleteAnsprechpartnerFromProfil(); return false;"><?= makebutton("ok") ?></a>
                <a href="" onClick="jQuery('#ansprechpartner_delete_question').dialog('close'); return false;"><?= makebutton("abbrechen") ?></a>
            </div>
        </div>
        <div style="float:left; width: 45%; margin-left: 5px; border: thin solid #aaaaaa; -moz-border-radius: 10px; padding: 5px;">
            <?= _("Gesuchter Ansprechpartner nicht auffindbar?") ?><br>
            <label for="neuer_ansprechpartner"><?= _("Ansprechpartner erstellen und hinzuf�gen:") ?></label>
            <input type="checkbox" id="neuer_ansprechpartner" name="neuer_ansprechpartner" onClick="jQuery('#neuer_ansprechpartner_daten').toggle('slide');" value="1">
            <table id="neuer_ansprechpartner_daten" style="display: none;">
                <tbody>
                    <tr>
                        <td><label for="range_typ"><?= _("Typ:") ?></label></td>
                        <td>
                            <select name="range_typ" id="range_typ" onChange="if (this.value == '') { jQuery('tr.freitext').show(); } else { jQuery('tr.freitext').hide(); };">
                                <option value=""<?= !$kontakt_neu['range_typ'] ? " selected" : "" ?>><?= _("Externer Ansprechpartner") ?></option>
                                <option value="auth_user_md5"<?= $kontakt_neu['range_typ'] === "auth_user_md5" ? " selected" : "" ?>><?= _("Nutzer im System") ?></option>
                                <option value="institute"<?= $kontakt_neu['range_typ'] === "institute" ? " selected" : "" ?>><?= _("Einrichtung") ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?= _("Identit�t:") ?></td>
                        <td><?= QuickSearch::get("range_id", StgAnsprechpartner::getAnsprechpartnerIdentitaetSuche())->defaultValue($kontakt_neu['range_id'], $kontakt_neu->getName())->render() ?></td>
                    </tr>
                    <tr>
                        <td><label><?= _("Ansprechpartner-Typ") ?></label></td>
                        <td>
                            <?
                            if (PersonalRechte::isRoot() || !$kontakt_neu['ansprechpartner_typ_id']) {
                                $editieren = true;
                            } else {
                                $editieren = false;
                                foreach ($ansprechpartnertypen as $typ) {
                                    if ($typ['ansprechpartner_typ_id'] === $kontakt_neu['ansprechpartner_typ_id']) {
                                        $editieren = true;
                                    }
                                }
                            }
                            ?>
                            <? if ($editieren) : ?>
                            <select id="ansprechpartner_typ_id" name="ansprechpartner_typ_id">
                                <? foreach ($ansprechpartnertypen as $typ) : ?>
                                <option value="<?= $typ['ansprechpartner_typ_id'] ?>"<?= $typ['ansprechpartner_typ_id'] === $kontakt_neu['ansprechpartner_typ_id'] ? " selected" : ""?>><?= htmlReady($typ['name']) ?></option>
                                <? endforeach ?>
                            </select>
                            <? else : ?>
                                <?= htmlReady(StgAnsprechpartner::getAnsprechpartnerTypName($kontakt_neu['ansprechpartner_typ_id'])) ?>
                            <? endif ?>
                        </td>
                    </tr>
                    <tr class="freitext" style="<?= !$kontakt_neu['range_typ'] ? "" : "display: none;" ?>">
                        <td><label for="freitext_name"><?= _("Freitext Name") ?></label></td>
                        <td><input type="text" id="freitext_name" name="freitext_name" value="<?= htmlReady($kontakt_neu['freitext_name']) ?>"></td>
                    </tr>
                    <tr class="freitext" style="<?= !$kontakt_neu['range_typ'] ? "" : "display: none;" ?>">
                        <td><label for="freitext_homepage"><?= _("Freitext Homepage") ?></label></td>
                        <td><input type="text" id="freitext_homepage" name="freitext_homepage" value="<?= htmlReady($kontakt_neu['freitext_homepage']) ?>"></td>
                    </tr>
                    <tr class="freitext" style="<?= !$kontakt_neu['range_typ'] ? "" : "display: none;" ?>">
                        <td><label for="freitext_mail"><?= _("Freitext Email") ?></label></td>
                        <td><input type="text" id="freitext_mail" name="freitext_mail" value="<?= htmlReady($kontakt_neu['freitext_mail']) ?>"></td>
                    </tr>
                    <tr class="freitext" style="<?= !$kontakt_neu['range_typ'] ? "" : "display: none;" ?>">
                        <td><label for="freitext_telefon"><?= _("Freitext Telefonnummer") ?></label></td>
                        <td><input type="text" id="freitext_telefon" name="freitext_telefon" value="<?= htmlReady($kontakt_neu['freitext_telefon']) ?>"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="clear: both;"></div>
        
    </div>
    <? endif ?>
    <? if ($profil && (PersonalRechte::isStab() or PersonalRechte::meineVerstecktenBereiche())) : ?>
    <h2><?= _("Akkreditierung") ?></h2>
    <div>
        <? if (PersonalRechte::isStab()) : ?>
        <ul class="zsb_detail_list">
            <li>
                <label for="zielvereinbarung">
                    <?= _("Zielvereinbarung") ?>
                </label>
                <textarea name="zielvereinbarung" id="zielvereinbarung"><?= htmlReady($profil['zielvereinbarung']) ?></textarea>
            </li>
            <li>
                <label for="einleitungstext">
                    <?= _("Einleitungstext") ?>
                </label>
                <textarea name="einleitungstext" id="einleitungstext"><?= htmlReady($profil['einleitungstext']) ?></textarea>
            </li>
        </ul>
        <? endif ?>
        <? if ($versteckteDokumente) : ?>
        <h3><?= _("Interne Dokumente") ?></h3>
        <ul id="interne_dokumente">
        <? foreach ($dateien as $key => $datei) : ?>
            <? if (!$datei->isPublic()) : ?>
            <li id="doku_id_<?= $datei->getId() ?>">
                <? /*<a class="icon_file" href="<?= URLHelper::getLink($datei_url, array('doku_id' => $datei->getId())) ?>"></a>*/ ?>
                <?= htmlReady($datei['name']." (".StgFile::getDokuTypName($datei['doku_typ_id']).")") ?>
                <? if (Personalrechte::isRoot() || in_array($datei->getId(), Personalrechte::meineDateien())) : ?>
                    <a class="icon_edit"></a>
                <? else : ?>
                    <? Assets::img("icons/16/grey/edit.png") ?>
                <? endif ?>
            </li>
            <? endif ?>
        <? endforeach ?>
        </ul>
        <? endif ?>
        <? if ($versteckteAnsprechpartner) : ?>
        <h3><?= _("Interne Ansprechpartner") ?></h3>
        <ul id="interne_ansprechpartner">
            <? foreach ($kontakte as $key => $kontakt) : ?>
            <? if (!$kontakt->isPublic()) : ?>
            <li id="ansprechpartner_<?= $kontakt->getId() ?>">
                <?= htmlReady($kontakt->getName()." (".StgAnsprechpartner::getAnsprechpartnerTypName($kontakt['ansprechpartner_typ_id']).")") ?>
                <? if (Personalrechte::isRoot() || in_array($kontakt->getId(), PersonalRechte::meineAnsprechpartner())) : ?>
                    <a class="icon_edit"></a>
                <? else : ?>
                    <?= Assets::img("icons/16/grey/edit.png") ?>
                <? endif ?>
            </li>
            <? endif ?>
            <? endforeach ?>
        </ul>
        <? endif ?>
    </div>
    <? endif ?>
</div>

<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton("absenden", "input") ?>
    <a href="?"><?= makebutton("abbrechen") ?></a>
    <? if ($profil && !$profil->isNew()) : ?>
    <a href="<?= URLHelper::getLink("plugins.php/estudienplaner/zsb_studiengang/profil_pdf", array('stg_profil_id' => $profil->getId())) ?>" target="_blank" title="<?= _("PDF-Datenblatt erstellen") ?>"><?= makebutton('erstellen-group') ?></a>
    <? endif ?>
</div>
</form>

<? 
foreach ($studiengaenge as $studiengang_id) {
    $studiengang = new Studiengang($studiengang_id);
    $studiengang_suche .= '<option value="'. htmlReady($studiengang_id) .'" title="'.htmlReady($studiengang['name']).'"'.($studiengang_id === Request::get("studiengang_id") ? " selected" : "").'>'. htmlReady($studiengang['name']) .'</option>';
}
$studiengang_suche = 
'<form action="'.URLHelper::getLink("?").' method="get">
<select name="studiengang_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("ausw�hlen") .'</option>
    '.$studiengang_suche.'
</select>
</form>';
foreach ($abschluesse as $abschluss) {
    $abschluss_suche .= '<option value="'. htmlReady($abschluss->getId()).'" title="'.htmlReady($abschluss['name']).'"'.($abschluss->getId() === Request::get("abschluss_id") ? " selected" : "").'>'. htmlReady($abschluss['name']).'</option>';
}
$abschluss_suche = 
'<form action="?" method="get">
<select name="abschluss_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("ausw�hlen").'</option>
    '.$abschluss_suche.'
</select>
</form>';

if (count($profile)) {
    $nav_select = '<form action="'.URLHelper::getLink("?").'" method="GET">';
    $nav_select .= '<input type="hidden" name="studiengang_id" value="'.htmlReady(Request::option("studiengang_id")).'">';
    $nav_select .= '<input type="hidden" name="abschluss_id" value="'.htmlReady(Request::option("abschluss_id")).'">';
    $nav_select .= '<select class="text-top" aria-label="'._("Springen Sie zu einem anderen Studiengangsprofil.").'" name="item_id" onchange="jQuery(this).closest('."'form'".')[0].submit();" style="max-width: 200px; cursor: pointer;">';
    $lastone = $nextone = null;
    $one = false;
    foreach ($profile as $profil) {
        $nav_select .= '<option value="'.$profil->getId().'"'.($profil->getId() === Request::get("item_id") ? " selected" : "").'>'.htmlReady(StgProfil::getName($profil->getId())).'</option>';
        if ($profil->getId() !== Request::get("item_id") && $lastone !== null && $nextone === null) {
            $nextone = $profil;
        }
        if ($profil->getId() === Request::get("item_id")) {
            $lastone = $one;
        }
        $one = $profil;
    }
    $nav_select .= "</select></form>";
    if ($lastone) {
        $zurueck = '<a class="icon_arr_1left" href="'.URLHelper::getLink("?", array('item_id' => $lastone->getId())).'" title="'._("zur�ck").'"></a>';
    }
    if ($nextone) {
        $vor = '<div style="float: right;"><a class="icon_arr_1right" href="'.URLHelper::getLink("?", array('item_id' => $nextone->getId())).'" title="'._("vorw�rts").'"></a></div>';
    }
}
$infobox = array(
    ($nav_select ? array("kategorie" => _("Navigation:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/link-intern.png",
                "text" => $nav_select
            ),
            array(
                "icon" => "",
                "text" => $zurueck." ".$vor
            )
        )
    ) : null),
    array("kategorie" => _("Aktionen:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Filter nach Studiengang")." ".$studiengang_suche."</label>"
            ),
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Filter nach Abschluss")." ".$abschluss_suche."</label>"
            )
        )
    )
);

$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);