<?
    $classes = array();
    if (!empty($module['children'])) {
        $classes[] = 'expandable';
    }
    if ($module['type'] == $module_type) {
        $classes[] = 'modul';
    }
?>
<li>
    <?= $this->render_partial('zsb_verlaufsplan/modul', compact('module', 'classes')) ?>
    <? if (!empty($module['children'])) : ?>
        <ul id="ul_<?= $module['sem_tree_id'] ?>" class="toexpand" style="display: none;">
        </ul>
    <? endif ?>
</li>
