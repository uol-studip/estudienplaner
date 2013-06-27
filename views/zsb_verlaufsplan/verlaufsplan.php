<?php
$headers = array(_("Name"), _("Untertitel"), _("Fach 1"), _("Fach 2"), _("Pr�fungsversion"));
$items = array();
foreach ($verlaufsplaene as $verlaufsplan) {
    $profile = $verlaufsplan->getProfile();
    $items[] = array(
        'content' => array(
            $verlaufsplan['titel'],
            $verlaufsplan['untertitel'],
            $profile[0] ? StgProfil::getName($profile[0]->getId()) : "",
            $profile[1] ? StgProfil::getName($profile[1]->getId()) : "",
            $verlaufsplan['version']
        ),
        'url' => URLHelper::getLink("?", array('item_id' => $verlaufsplan->getId())),
        'item' => $verlaufsplan
    );
}
$neu = URLHelper::getURL("?", array('item_id' => "neu"));
$preformatted = true;
?>
<h1 style="text-align: center;"><?= _("Verlaufspl�ne") ?></h1>
<? if (count($items)) : ?>
<?= $this->render_partial("zsb/partials/editable.php", compact("headers", "items", "preformatted", "neu")) ?>
<? else : ?>
<?= MessageBox::info(_("Bitte w�hlen Sie in der Infobox einen Filter aus.")) ?>
<? endif ?>

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
foreach ($abschluesse as $abschluss_id) {
    $abschluss = new Abschluss($abschluss_id);
    $abschluss_suche .= '<option value="'. htmlReady($abschluss->getId()).'" title="'.htmlReady($abschluss['name']).'"'.($abschluss->getId() === Request::get("abschluss_id") ? " selected" : "").'>'. htmlReady($abschluss['name']).'</option>';
}
$abschluss_suche =
'<form action="?" method="get">
<select name="abschluss_id" onChange="jQuery(this).closest('."'form'".').submit();" style="max-width: 200px;">
    <option value="">'. _("ausw�hlen").'</option>
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
                'text' => '<a href="'.URLHelper::getLink($neu).'">'._("Neuen Verlaufsplan anlegen").'</a>'
            ) : null)
        )
    )
);

$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);