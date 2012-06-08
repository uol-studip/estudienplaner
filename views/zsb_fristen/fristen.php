<h1 style="text-align: center;"><?= _("Bewerbungsfristen") ?></h1>

<? if (PersonalRechte::isRoot()) : ?>
<form action="<?= URLHelper::getLink("?") ?>" method="post">
<?= add_safely_security_token() ?>
<div style="width: 100%; text-align: center;">
    <?= makebutton("absenden", "input") ?> <a href="?"><?= makebutton("abbrechen") ?></a>
</div>
<script>
STUDIP.zsb.editFristen = true;
</script>
<? endif ?>
<?= $this->render_partial("zsb_fristen/fristen_table.php", compact('zielgruppen', 'profile')) ?>
<? if (PersonalRechte::isRoot()) : ?>
<div style="width: 100%; text-align: center;">
    <?= makebutton("absenden", "input") ?> <a href="?"><?= makebutton("abbrechen") ?></a>
</div>
</form>
<? endif ?>


