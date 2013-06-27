<form action="?" method="post">
<?= add_safely_security_token() ?>
<h2 style="text-align: center;"><?= _("Fächerkombination") ?></h2>
<div class="accordion" data-active="<?= Request::int('active_tab', 0) ?>">
    <h2><?= _("Details") ?></h2>
    <div>
    <ul>
        <li>
            <input type="hidden" name="fach_kombi_id" value="<?= $kombination['fach_kombi_id'] ?>">
            <label for="stg_profil_id_1"><?= _("Erstes Fach:") ?></label>
            <?= QuickSearch::get("stg_profil_id", $profilsuche)->defaultValue($kombination['stg_profil_id'], StgProfil::getName($kombination['stg_profil_id']))->render() ?>
        </li>
        <li>
            <label for="kombi_stg_profil_id_2"><?= _("Zweites Fach:") ?></label>
            <?= QuickSearch::get("kombi_stg_profil_id", $profilsuche)->defaultValue($kombination['kombi_stg_profil_id'], StgProfil::getName($kombination['kombi_stg_profil_id']))->render() ?>
        </li>
        <li>
            <label for="beschreibung"><?= _("Beschreibung:") ?></label>
            <textarea name="beschreibung" id="beschreibung"><?= htmlReady($kombination['beschreibung']) ?></textarea>
        </li>
    </ul>
    </div>
    <? if ($kombination) : ?>
    <h2><?= _("Fächerkombination löschen") ?></h2>
    <div>
        <?= makebutton('loeschen', 'input', _("Fächerkombination löschen - kann nicht rückgängig gemacht werden."), 'delete') ?>
    </div>
    <? endif ?>
</div>
<div style="text-align: center; margin-left: auto; margin-right: auto; margin-top: 12px;">
    <?= makebutton("absenden", "input") ?>
    <? if ($kombination) : ?>
    <a href="?"><?= makebutton("abbrechen") ?></a>
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
    <option value="">'. _("auswählen") .'</option>
    '.$studiengang_suche.'
</select>
</form>';
foreach ($abschluesse as $abschluss) {
    $abschluss_suche .= '<option value="'. htmlReady($abschluss->getId()).'" title="'.htmlReady($abschluss['name']).'"'.($abschluss->getId() === Request::get("abschluss_id") ? " selected" : "").'>'. htmlReady($abschluss['name']).'</option>';
}
$abschluss_suche =
'<form action="?" method="get">
<select name="abschluss_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("auswählen").'</option>
    '.$abschluss_suche.'
</select>
</form>';

if (count($kombinationen)) {
    $nav_select = '<form action="'.URLHelper::getLink("?").'" method="GET">';
    $nav_select .= '<input type="hidden" name="studiengang_id" value="'.htmlReady(Request::option("studiengang_id")).'">';
    $nav_select .= '<input type="hidden" name="abschluss_id" value="'.htmlReady(Request::option("abschluss_id")).'">';
    $nav_select .= '<select class="text-top" aria-label="'._("Springen Sie zu einem anderen Studiengangsprofil.").'" name="fach_kombi_id" onkeydown="if (event.keyCode === 13) { jQuery(this).closest('."'form'".')[0].submit(); }" onclick="jQuery(this).closest('."'form'".')[0].submit();" size="'.(count($kombinationen) < 8 ? count($kombinationen) : 8).'" style="max-width: 200px; cursor: pointer;">';
    $lastone = $nextone = null;
    $one = false;
    foreach ($kombinationen as $kombination) {
        $name = $kombination->getName();
        $nav_select .= '<option value="'.$kombination->getId().'"'.($kombination->getId() === Request::get("fach_kombi_id") ? " selected" : "").' title="'.htmlReady($kombination->getName()).'">'.htmlReady($kombination->getName()).'</option>';
        if ($kombination->getId() !== Request::get("fach_kombi_id") && $lastone !== null && $nextone === null) {
            $nextone = $kombination;
        }
        if ($kombination->getId() === Request::get("fach_kombi_id")) {
            $lastone = $one;
        }
        $one = $kombination;
    }
    $nav_select .= "</select></form>";
    if ($lastone) {
        $zurueck = '<a class="icon_arr_1left" href="'.URLHelper::getLink("?", array('fach_kombi_id' => $lastone->getId())).'" title="'._("zurück").'"></a>';
    }
    if ($nextone) {
        $vor = '<div style="float: right;"><a class="icon_arr_1right" href="'.URLHelper::getLink("?", array('fach_kombi_id' => $nextone->getId())).'" title="'._("vorwärts").'"></a></div>';
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
                "text" => "<label>"._("Filter nach Fach")." ".$studiengang_suche."</label>"
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