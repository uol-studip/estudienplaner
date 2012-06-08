<?php
$headers = array(_("Name"), _("sichtbar für"));
$items = array();
foreach ($bereiche as $bereich) {
    $items[] = array(
        'content' => array(
            $bereich['bereich_name'],
            ($bereich['sichtbar_fsb'] ? "FSB " : "").($bereich['sichtbar_pamt'] ? "P-Amt " : "").($bereich['sichtbar_iamt'] ? "I-Amt " : "")
        ),
        'url' => URLHelper::getLink("?", array('bereichs_id' => $bereich->getId())),
        'item' => $bereich
    );
}
$neu = URLHelper::getLink("?", array('bereichs_id' => "neu"));
$preformatted = true;
?>
<h1 style="text-align: center;"><?= _("Bereiche") ?></h1>
<?= $this->render_partial("zsb/partials/editable.php", compact("headers", "items", "preformatted", "neu")) ?>