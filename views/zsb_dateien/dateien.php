<?php
$headers = array(_("Name"), _("Dateiname"), _("Typ"), _("Datum"), _("Größe"));
$items = array();
foreach ($dateien as $datei) {
    $items[] = array(
        'content' => array(
            $datei['name'],
            $datei['filename'],
            StgFile::getDokuTypName($datei['doku_typ_id']),
            date("j.n.Y", strtotime($datei['chdate'])),
            $datei['filesize'] ? round($datei['filesize'] / 1024, 2) : "0"
        ),
        'url' => URLHelper::getLink("?", array('doku_id' => $datei->getId())),
        'item' => $datei
    );
}
if (PersonalRechte::isRoot() || PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
    $neu = URLHelper::getLink("?", array('doku_id' => "neu"));
}
$preformatted = true;
?>
<h1 style="text-align: center;"><?= _("Dokumente") ?></h1>

<? if (count($items)) : ?>
<?= $this->render_partial("zsb/partials/editable.php", compact("headers", "items", "preformatted", "neu")) ?>
<? else : ?>
<?= MessageBox::info(_("Bitte wählen Sie in der Infobox einen Filter aus.")) ?>
<? endif ?>

<? 
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
    array("kategorie" => _("Aktionen:"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/search.png",
                "text" => "<label>"._("Filter nach Typen")." ".$typ_suche."</label>"
            ),
            ($neu ? array(
                "icon" => "icons/16/black/plus.png",
                "text" => '<a href="'.$neu.'">'._("Neues Dokument anlegen")."</a>"
            ) : null)
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);