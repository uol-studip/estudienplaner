<h1 style="text-align: center;"><?= _("Bewerbungsfristen") ?></h1>

<?= MessageBox::info(_("Bitte wählen Sie in der Infobox einen Filter aus.")) ?>

<?
foreach ($studiengaenge as $studiengang_id) {
    $studiengang = new Studiengang($studiengang_id);
    $studiengang_suche .= '<option value="'. htmlReady($studiengang_id) .'" title="'.htmlReady($studiengang['name']).'">'. htmlReady($studiengang['name']) .'</option>';
}
$studiengang_suche =
'<form action="'.URLHelper::getLink("?").' method="get">
<select name="studiengang_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("auswählen") .'</option>
    '.$studiengang_suche.'
</select>
</form>';
foreach ($abschluesse as $abschluss) {
    $abschluss_suche .= '<option value="'. htmlReady($abschluss->getId()).'" title="'.htmlReady($abschluss['name']).'">'. htmlReady($abschluss['name']).'</option>';
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
            )
        )
    )
);

$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);