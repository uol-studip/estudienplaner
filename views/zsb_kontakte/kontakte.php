<?php
$headers = array(_("Name"), _("Typ"));
$items = array();
foreach ($ansprechpartner as $kontakt) {
    $items[] = array(
        'content' => array(
            $kontakt->getAvatar()." ".$kontakt->getName(),
            StgAnsprechpartner::getAnsprechpartnerTypName($kontakt['ansprechpartner_typ_id'])
        ),
        'url' => URLHelper::getLink("?", array('kontakt_id' => $kontakt->getId())),
        'item' => $kontakt
    );
}
$preformatted = true;
if (PersonalRechte::isRoot() || PersonalRechte::isPamt() || PersonalRechte::isIamt()) {
    $neu = URLHelper::getURL("?", array('kontakt_id' => "neu"));
}
?>
<h1 style="text-align: center;"><?= _("Ansprechpartner") ?></h1>

<script>
    jQuery(function () {
        jQuery('table.zsb_editable > tbody > tr > td:nth-child(2) ').each(function (index, value) {
            if (jQuery('#prefilter > option[value=' + jQuery(value).text() + ']').length === 0) {
                jQuery('#prefilter').append('<option value="' + jQuery(value).text() + '">' + jQuery(value).text() + '</option>');
            }
        });
    });
</script>

<? if (count($items)) : ?>
<?= $this->render_partial("zsb/partials/editable.php", compact("headers", "items", "preformatted", "neu")) ?>
<? else : ?>
<?= MessageBox::info(_("Bitte wählen Sie in der Infobox einen Filter aus.")) ?>
<? endif ?>

<?
foreach ($typen as $typ) {
    $typ_suche .= '<option value="'. htmlReady($typ['ansprechpartner_typ_id']) .'" title="'.htmlReady($typ['name']).'"'.(Request::get('typ_id') === $typ['ansprechpartner_typ_id'] ? " selected" : "").'>'. htmlReady($typ['name']) .'</option>';
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
                'icon' => "icons/16/black/search.png",
                'text' => "<label>"._("Filter nach Typen")." ".$typ_suche."</label>"
            ),
            ($neu ? array(
                'icon' => "icons/16/black/plus.png",
                'text' => '<a href="'.URLHelper::getLink($neu).'">'._("Neuen Ansprechpartner anlegen").'</a>'
            ) : null)
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/monument.jpg",
    'content' => $infobox
);