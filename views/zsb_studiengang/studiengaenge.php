<h1 style="text-align: center;"><?= _("Studiengangsprofile") ?></h1>
<?
$headers = array(_("Fach"), _("Abschluss"));
$items = $profile;
$neu = PersonalRechte::isRoot() ? $controller->url_for('zsb_studiengang/studiengaenge?item_id=neu&studienprofil_id=neu') : null;
$type = "studienprofil";
?>

<? if (count($items)) : ?>
<?= $this->render_partial("zsb/partials/editable.php", compact('headers', 'items', 'informationen', 'neu', 'type')) ?>
<? else : ?>
<?= MessageBox::info(_("Bitte wählen Sie in der Infobox einen Filter aus.")) ?>
<? endif ?>

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

$infobox = array(
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
            ),
            ($neu ? array(
                'icon' => "icons/16/black/plus.png",
                'text' => '<a href="'.$neu.'">'._("Neues Studiengangprofil anlegen").'</a>'
            ) : null)
        )
    )
);

$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);