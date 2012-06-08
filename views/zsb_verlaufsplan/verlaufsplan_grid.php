<?
$plan  = $gettersetter_verlaufsplan->getAllModules();
$kp    = $gettersetter_verlaufsplan->getKP();

// TODO: getVerlauf_Typen liefert bisher nur zufällig als Index die verlauf_typ_id
$types = $gettersetter_verlaufsplan->getVerlauf_Typen();
$meineModule = PersonalRechte::meineModule();

?>
<table id="studyplan">
    <? for ($height = $plan['height']; $height > 0; $height--) : ?>
    <? $kp = 0 ?>
    <tr id="sem<?= $height ?>">
        <td class="sem_label"><?= $height ?>. Sem.</td>
        <? for ($width = 0; $width <= $plan['width']; $width++) : ?>
        <? if ($width % 2 == 0) : ?>
            <? $has_middy = !empty($plan['modules'][$height][$width+1]) ?>
            <? $hide = !($plan['modules'][$height+1][$width][0]->position_hoehe != 2) ?>

            <!-- Erste Spalte -->
            <td id="field_<?= $height ?>_<?= $width ?>" class="ui-widget-header1 ui-droppable droppable lefty<?= $has_middy ? ' twocolumn' : '' ?>"
                colspan="<?= $has_middy ? '1' : '2' ?>" rowspan="<?= $plan['modules'][$height][$width][0]->position_hoehe ?>"
                <?= $hide ? 'style="display: none"' : '' ?>>
                <? $max = 0; ?>
                <? if(!empty($plan['modules'][$height][$width])) foreach ($plan['modules'][$height][$width] as $module):  ?>
                    <?= $this->render_partial('zsb_verlaufsplan/modul', array('module' => $module, 'color' => $types[$module->verlauf_typ_id]['farbcode'],
                        'classes' => array(
                            'modul',
                            'placedmodule'//,
                            //!isset($meineModule[$module['sem_tree_id']]) ? 'fixed' : null
                        )
                     )) ?>
                    <? $max = ($max >= $module['kp'] ? $max : $module['kp']) ?>
                <? endforeach ?>
                <? $kp += $max ?>
            </td>

        <? else : ?>
            <!-- Zweite Spalte -->
            <? if (!empty($plan['modules'][$height][$width])) : ?>
            <? $hide = !($plan['modules'][$height+1][$width-1][0]->position_hoehe != 2) ?>
            <td id="field_<?= $height ?>_<?= $width ?>" class="droppable middy twocolumn" <?= $hide ? 'style="display: none"' : '' ?>
                rowspan="<?= $plan['modules'][$height][$width-1][0]->position_hoehe ?>">
                <? $max = 0; ?>
                <? if(!empty($plan['modules'][$height][$width])) foreach ($plan['modules'][$height][$width] as $module):  ?>                
                    <?= $this->render_partial('zsb_verlaufsplan/modul', array('module' => $module, 'color' => $types[$module->verlauf_typ_id]['farbcode'],
                        'classes' => array(
                            'modul',
                            'placedmodule'//,
                            //!isset($meineModule[$module['sem_tree_id']]) ? 'fixed' : null
                        )
                     )) ?>
                    <? $max = ($max >= $module['kp'] ? $max : $module['kp']) ?>
                <? endforeach ?>
                <? $kp += $max ?>
            </td>
            <? endif ?>
        <? endif /* odd/even cycle */ ?>

        <!-- Feld, um zweite Spalte für Module hinzuzufügen -->
        <? if ($width % 2 == 1) : ?>
            <? $hide = !($plan['modules'][$height+1][$width-1][0]->position_hoehe != 2) ?>            
            <td id="drop_<?= $height ?>_<?= $width ?>" class="ui-widget-header1 ui-droppable droppable righty"
                rowspan="<?= $plan['modules'][$height][$width-1][0]->position_hoehe ?>"
                <?= $hide ? 'style="display: none"' : '' ?>></td>            
        <? endif ?>

        <? endfor /* iterate over cells */ ?>
        <td id="kp_<?= $height ?>" class="kp">
            <?= $kp ?> <?= _("KP") ?>
        </td>
    </tr>
    <? endfor /* iterate over rows */ ?>
</table>