<? 
$module['kp'] = floor($module['kp']);
if (!$module['sem_tree_id']) {
    //Freitextmodul:
    $module['sem_tree_id'] = $random_id = "z".uniqid();
    $classes[] = $random_id;
    $classes[] = "free";
}
?>

<a class="<?= implode(' ', $classes) ?> <?= $module['sem_tree_id'] ?>" id="<?= $module['sem_tree_id'] ?>"
   style="background-color: <?= $color ? $color : 'none' ?>;"
   data-module='{ "id": "<?= $module['sem_tree_id'] ?>",
     "kp": "<?= $module['kp'] ?>",
     "type" : "<?= $module['verlauf_typ_id'] ? $module['verlauf_typ_id'] : '1' /* auch wenn 1 keine verlauf_typ_id sein muss */ ?>",
     "notiz": "<?= $module['modul_notiz'] ?>"}'>
    <? if (!empty($module['children'])) : ?>
        <span id="plus_<?= $module['sem_tree_id'] ?>"><?= Assets::img('icons/16/blue/arr_1right.png'); ?></span>
        <span id="minus_<?= $module['sem_tree_id'] ?>" style="display: none"><?= Assets::img('icons/16/blue/arr_1down.png'); ?></span>
    <? endif ?>
    
    <span class="title"><?= htmlReady($random_id ? $module['modul_notiz'] : $module['name']) ?></span>
    <span class="kp"><?= $module['kp'] ? '('. (int) $module['kp'] .' KP)' : '' ?></span>
    <div class="icons">
        <?= Assets::img('icons/16/white/edit.png', array(
              'class'   => 'icon',
              'title'   => _("Klicken zum Bearbeiten"),
              'onClick' => 'STUDIP.eStudienplaner.showEditDialog(this)'
            )) ?>
        
        <?= Assets::img('icons/16/white/trash.png', array(
              'class'   => 'icon',
              'title'   => _("Klicken zum Löschen"),
              'onClick' => 'STUDIP.eStudienplaner.deleteModule(this)'
            )) ?>
    </div>
</a>