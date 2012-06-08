<h1 style="text-align: center;"><?= _("Personal nach Studiengängen") ?></h1>

<?= MessageBox::info(_("Bitte wählen Sie in der Infobox einen Studiengang aus.")) ?>

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

$infobox = array(
    array("kategorie" => _("Aktionen:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Studiengang auswählen")." ".$studiengang_suche."</label>"
            )
        )
    )
);

$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);