<h1 style="text-align: center;"><?= _("Datenfelder") ?></h1>
<?
$headers = array(_("Name"), _("Typ"), _("Einträge"), _("Reihenfolge"));
$items = $datenfelder;
$neu = URLHelper::getLink($url, array('datenfeld_id' => 'neu'));
?>
<?= $this->render_partial("zsb/partials/editable.php", compact('headers', 'items', 'neu')) ?>