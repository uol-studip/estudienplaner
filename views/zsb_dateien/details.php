<? $item_name = "doku_id" ?>

<form action="?" method="post">
<?= add_safely_security_token() ?>
<input type="hidden" id="doku_id" name="doku_id" value="<?= $datei->getId() ? $datei->getId() : "neu" ?>">
<h2 style="text-align: center;"><?= _("Datei:") ?> <?= htmlReady($datei['name']) ?></h2>

<div class="accordion">
    <h2><?= _("Daten") ?></h2>
    <div>
        <ul>
            <li>
                <label for="name"><?= _("Name") ?></label>
                <input type="text" id="name" name="name" value="<?= htmlReady($datei['name']) ?>">
            </li>
            <? if (false && $datei->getId()) : ?>
            <li>
                <a class="studip_button" href="<?= URLHelper::getLink($download_action, array('doku_id' => $datei->getId())) ?>"<?= trim($datei['quick_link']) ? ' target="_blank"' : "" ?>>
                    <?= _("runterladen") ?>
                </a>
            </li>
            <? endif ?>
            <li>
                <label for="sichtbar"><?= _("Sichtbar") ?></label>
                <input type="checkbox" id="sichtbar" name="sichtbar" <?= $datei->isNew() || $datei['sichtbar'] ? " checked" : "" ?>>
            </li>
            <li>
                <label for="doku_typ_id"><?= _("Art des Dokumentes") ?></label>
                <?
                $typen = StgFile::getTypen();
                if ($datei['doku_typ_id']) {
                    $editieren = false;
                    foreach ($typen as $typ) {
                        if ($typ['doku_typ_id'] === $datei['doku_typ_id']) {
                            $editieren = true;
                        }
                    }
                } else {
                    $editieren = true;
                }

                ?>
                <? if ($editieren) : ?>
                <select id="doku_typ_id" name="doku_typ_id">
                    <? foreach ($typen as $typ) : ?>
                    <option value="<?= htmlReady($typ['doku_typ_id']) ?>"<?= $typ['doku_typ_id'] === $datei['doku_typ_id'] ? " selected" : "" ?>><?= htmlReady($typ['name']) ?></option>
                    <? endforeach ?>
                </select>
                <? else : ?>
                <?= htmlReady(StgFile::getDokuTypName($datei['doku_typ_id'])) ?>
                <? endif ?>
            </li>
            <li>
                <label for="language"><?= _('Sprache des Dokuments') ?></label>
                <select id="language" name="language">
                    <option value="de" <? if ($datei['language'] === 'de') echo 'selected'; ?>><?= _('deutsch') ?></option>
                    <option value="en" <? if ($datei['language'] === 'en') echo 'selected'; ?>><?= _('englisch') ?></option>
                </select>
            </li>
            <li>
                <label for="jahr"><?= _("Jahr") ?></label>
                <input type="text" maxlength="4" id="jahr" name="jahr" value="<?= htmlReady($datei['jahr']) ?>">
            </li>
            <? if ($datei['quick_link'] || $datei->isNew()) : ?>
            <li>
                <label for="quick_link"><?= _("URL") ?></label>
                <input type="text" id="quick_link" name="quick_link" value="<?= htmlReady($datei['quick_link']) ?>">
            </li>
            <? endif ?>
            <? if (!$datei['quick_link'] || $datei->isNew()) : ?>
            <li>
                <? if ($datei['filename']) : ?>
                <a href="<?= $datei->getDownloadLink() ?>">
                <?= Assets::img("icons/16/grey/file.png", array('class' => "text-bottom", 'title' => _("Es ist eine Datei angehängt, sie wird allerdings nur präsentiert, wenn keine URL angegeben wurde.")))." "._("Dateiname") ?>: <span id="filename"><?= htmlReady($datei['filename']) ?></span>
                </a>
                <? endif ?>
                <!-- lokalisierte Text für die Buttons und Dateiliste -->
                <div id="text-button" style="display:none;"><?= $datei['filename'] ? _("Neu hochladen") : _("Datei hochladen") ?></div>
                <div id="text-droparea" style="display:none;"><?= _("Dateien hier abladen!") ?></div>
                <div id="text-failed" style="display:none;"><?= _("Fehler!") ?></div>
                <div id="text-cancel" style="display:none;"><?= _("abbrechen") ?></div>
                <!-- Und hier kommt der Uploader hin -->
                <div id="file-uploader">
                    <noscript>
                        <p>Please enable JavaScript to use file uploader.</p>
                        <!-- oder iframe für non-JS-Uploader -->
                    </noscript>
                </div>
                <i><?= _("Alte Datei wird überschrieben.") ?></i>
                <script>
                    var uploader = new qq.FileUploader({
                        element: jQuery('#file-uploader')[0],
                        // path to server-side upload script
                        action: '<?= URLHelper::getURL("plugins.php/estudienplaner/zsb_dateien/upload") ?>',
                        params: {<?= $datei->isNew() ? "" : " doku_id : '".$datei->getId()."'" ?>},
                        onComplete: function(id, fileName, responseJSON) {
                            uploader.setParams({ doku_id : responseJSON.id });
                            jQuery("#doku_id").val(responseJSON.id);
                            jQuery("#filename").text(responseJSON.filename);
                            //jQuery("#file-uploader .qq-upload-list:first-child").slideUp(function () { jQuery(this).remove(); });
                        }
                    });
                </script>
            </li>
            <? endif ?>
            <li>
                <label for="tags"><?= _("Schlagwörter") ?></label>
                <textarea id="tags" name="tags" class="clean"><?= htmlReady(implode(" ", $datei->getTags())) ?></textarea>
            </li>
        </ul>
        <p class="info">
            <?= _("Sie können sowohl ein Bild hochladen, als auch eine URL angeben. Ausgegeben wird am Ende aber immer die URL, falls es eine gibt, und nur ansonsten die hochgeladene Datei. Löschen Sie die URL, damit wieder das hochgeladene Bild erreichbar wird.") ?>
        </p>
    </div>
    <? if (!$datei->isNew()) : ?>
    <h2><?= _("Studiengänge") ?></h2>
    <div>
        <div>
            <?= QuickSearch::get("profil_id", $profilsuche)
                        ->withoutButton()
                        ->noSelectBox()
                        ->fireJSFunctionOnSelect("STUDIP.zsb.addDokumentToStudiengaenge")
                        ->render() ?>
            <ul id="stg_dokumente_profil">
            <? $verknupefteStudiengaenge = array(); foreach ($datei->getStudiengaenge() as $stg_profil) : ?>
                <? $verknupefteStudiengaenge[] = $stg_profil->getId() ?>
                <li id="profil_id_<?= $stg_profil->getId() ?>"><?= htmlReady(StgProfil::getName($stg_profil->getId())) ?><?= $stg_profil->hasPermission() ? '<a class="icon_trash"></a>' : "" ?></li>
            <? endforeach ?>
            </ul>
        </div>
        <? if (PersonalRechte::isPamt() || PersonalRechte::isIamt()) : ?>
        <a class="icon_folder-empty" onClick="jQuery(this).hide().next().show(); STUDIP.MultiSelect.create('#verknuepfte_studiengaenge', '<?= _("Studiengänge") ?>'); return false;"><?= _("Großes Auswahlfeld anzeigen") ?></a>
        <div style="display: none;">
            <select name="verknuepfte_studiengaenge[]" id="verknuepfte_studiengaenge" multiple style="width: 80%; height: 150px;">
            <? foreach (StgProfil::getMeineProfile() as $profil) : ?>
                <option value="<?= $profil->getId() ?>"<?= in_array($profil->getId(), $verknupefteStudiengaenge) ? " selected" : "" ?>><?= StgProfil::getName($profil->getId()) ?></option>
            <? endforeach ?>
            </select>
        </div>
        <? endif ?>
    </div>
    <? endif ?>
</div>

<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton("absenden", "input") ?> <a href="?"><?= makebutton("abbrechen") ?></a>
</div>
</form>

<?
if (count($dokumente)) {
    $nav_select = '<form action="'.URLHelper::getLink("?").'" method="GET">';
    $nav_select .= '<input type="hidden" name="typ_id" value="'.htmlReady(Request::option("typ_id")).'">';
    $nav_select .= '<select class="text-top" aria-label="'._("Springen Sie zu einem anderen Ansprechpartner.").'" name="doku_id" onchange="jQuery(this).closest('."'form'".')[0].submit();" style="max-width: 200px; cursor: pointer;">';
    $lastone = $nextone = null;
    $one = false;
    foreach ($dokumente as $dokument) {
        $nav_select .= '<option value="'.$dokument->getId().'"'.($dokument->getId() === Request::get("doku_id") ? " selected" : "").'>'.htmlReady($dokument["name"]).'</option>';
        if ($dokument->getId() !== Request::get("doku_id") && $lastone !== null && $nextone === null) {
            $nextone = $dokument;
        }
        if ($dokument->getId() === Request::get("doku_id")) {
            $lastone = $one;
        }
        $one = $dokument;
    }
    $nav_select .= "</select></form>";
    if ($lastone) {
        $zurueck = '<div style="float: left;"><a class="icon_arr_1left" href="'.URLHelper::getLink("?", array('doku_id' => $lastone->getId())).'" title="'._("zurück").'"></a></div>';
    }
    if ($nextone) {
        $vor = '<div style="float: right;"><a class="icon_arr_1right" href="'.URLHelper::getLink("?", array('doku_id' => $nextone->getId())).'" title="'._("vorwärts").'"></a></div>';
    }
}

foreach ($typen as $typ) {
    $typ_suche .= '<option value="'. htmlReady($typ['doku_typ_id']) .'" title="'.htmlReady($typ['name']).'"'.(Request::get('typ_id') === $typ['doku_typ_id'] ? " selected" : "").'>'. htmlReady($typ['name']) .'</option>';
}
$typ_suche =
'<form action="?" method="get">
<select name="typ_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("auswählen") .'</option>
    '.$typ_suche.'
</select>
</form>';


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
                "text" => '<div style="text-align: center;">'.$zurueck.' <a class="icon_arr_1up" href="'.URLHelper::getLink("?", array('doku_id' => null, 'typ_id' => Request::get('typ_id'))).'" title="'._("Zur Dokumentübersicht").'"></a> '.$vor.'</div>'
            )
        )
    ) : null),
    array("kategorie" => _("Aktionen:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Filter nach Typen")." ".$typ_suche."</label>"
            ),
            ($neu ? array(
                "icon" => "icons/16/black/file.png",
                "text" => '<a href="'.$neu.'">'._("Neues Dokument anlegen")."</a>"
            ) : null)
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);
