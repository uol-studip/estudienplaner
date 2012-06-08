<script type="text/javascript">
  jQuery(document).ready(function() {

    // set constants
    STUDIP.eStudienplaner.url = '<?= $controller->url_for('zsb_verlaufsplan', array('abschluss_id' => null, 'studiengang_id' => null)) ?>';
    STUDIP.eStudienplaner.verlaufsplan_id = <?= $gettersetter_verlaufsplan->getVerlaufsplan_Id() ?>;
    STUDIP.eStudienplaner.current_sem = <?= $dimensions['height'] ? (int) $dimensions['height'] : 1 ?>;
    STUDIP.eStudienplaner.current_module = <?= $dimensions['width'] ? (int) floor($dimensions['width'] / 2)+1 : 1 ?>;

    STUDIP.eStudienplaner.dropCell = '<td class="ui-widget-header1 ui-droppable droppable lefty" colspan="2">'
      + '<td class="ui-widget-header1 ui-droppable droppable righty"></td>';

    STUDIP.eStudienplaner.plusImage = '<?= Assets::img('icons/16/black/plus.png', array('class' => 'plus')) ?>';
    STUDIP.eStudienplaner.plusSign  = '<?= Assets::image_path('icons/16/black/plus.png', array('class' => 'plus')) ?>';
    STUDIP.eStudienplaner.minusSign = '<?= Assets::image_path('icons/16/black/minus.png', array('class' => 'plus')) ?>';

    <? foreach ($gettersetter_verlaufsplan->getVerlauf_typen() as $typ) : ?>
    STUDIP.eStudienplaner.verlauf_typen[<?= $typ['verlauf_typ_id'] ?>] = '#<?= $typ['farbcode'] ?>';
    <? endforeach; ?>

    // initialize the plan
    STUDIP.eStudienplaner.init();
  });
</script>

<div id="dialog"></div>



<div style="display: table-row;">
    
    <div id="dropzone" style="width: 75%; max-width: 75%; overflow: auto; display: table-cell;">

        <div id="up_down">
            <?= Assets::img('icons/16/blue/arr_1up.png', array('class' => 'arrow',
                'id'    => 'add_sem',
                'title' => _("Ein weiteres Semester hinzufügen"),
                'alt'   => _("Ein weiteres Semester hinzufügen"))) ?>
            <?= Assets::img('icons/16/blue/arr_1down.png', array('class' => 'arrow',
                'id'    => 'rem_sem',
                'title' => _("Das letzte Semester entfernen."),
                'alt'   => _("Das letzte Semester entfernen."))) ?>
        </div>

        <div style="">
        <?= $this->render_partial('zsb_verlaufsplan/verlaufsplan_grid', compact('verlaufsplan')); ?>
        </div>

        <div id="left_right">
            <?= Assets::img('icons/16/blue/arr_1left.png', array('class' => 'arrow',
                'id'    => 'rem_mod',
                'title' => _("Das letzte Modul entfernen."),
                'alt'   => _("Das letzte Modul entfernen."))) ?>
            <?= Assets::img('icons/16/blue/arr_1right.png', array('class' => 'arrow',
                'id'    => 'add_mod',
                'title' => _("Ein weiteres Modul hinzufügen"),
                'alt'   => _("Ein weiteres Modul hinzufügen"))) ?>
        </div>

        <div id="save_verlaufsplan">
            <span id="verlaufsplan_saved" style="display: none"><?= _('Verlaufsplan wurde gespeichert!') ?></span>
            <a href="javascript:STUDIP.eStudienplaner.saveVerlaufsplan()"><?= makebutton('speichern') ?></a>
        </div>

        <div class="dragzone" style="text-align: center; margin: 20px;">
            <span style="border: thin solid #000000; padding: 5px;">
                <?= $this->render_partial('zsb_verlaufsplan/modul.php', array(
                        'module' => array(
                            'sem_tree_id' => "free",
                            "kp" => 0,
                            "children" => array(),
                            "name" => _("Freitext-Modul")
                        ),
                        'classes' => array("modul", "free")
                )); ?>
            </span>
            
        </div>

        
    </div>

    <div id="dragzone_container" style="display: table-cell; width: 25%; max-width: 25%; ">
        <div style="display: none;">
        <a class="icon_folder-empty" onClick="STUDIP.eStudienplaner.moduleListAsWindow();"><?= _("Modulliste als Fenster") ?></a>
        </div>
        <div class="dragzone" style="height: 400px; max-height: 400px; overflow: auto;">
            <ul>
            <?
            uasort($modules, create_function('$a, $b', 'return strcasecmp($a["name"], $b["name"]);'));
            foreach ($modules as $module) {
                if (!isset($modules[$module['parent_id']])) {
                    print $this->render_partial('zsb_verlaufsplan/modulliste.php', array(
                        'module' => $module,
                        'module_type' => $module_type
                    ));
                }
            }
            ?>
            </ul>
        </div>
        <?= _("Ziehen Sie die gewünschten Module in den Verlaufsplan.") ?>
    </div>
</div>

<div id="color_legend">
    <?= $this->render_partial('zsb_verlaufsplan/_color_legend.php') ?>
</div>

<div id="edit_module">

    <label>
    <?= _("KP:") ?>
    <select name="kp">
        <? for ($i = 0; $i <= 20; $i++) : ?>
        <option value="<?= $i ?>"><?= $i ?></option>
        <? endfor ?>
    </select>
    </label>

    <label><?= _("Modultyp:") ?>
    <select name="type">
        <? foreach ($gettersetter_verlaufsplan->getVerlauf_typen() as $type) : ?>
            <option style="background-color: <?= $type['farbcode'] ?>; color: white; font-weight: bold;" value="<?= $type['verlauf_typ_id'] ?>">
                <?= $type['typ_name'] ?>
            </option>
        <? endforeach ?>
    </select>
    </label>

    <textarea name="notiz" aria-label="<?= _("Beschreibung des Moduls") ?>"></textarea>
    <input type="hidden" name="id">
        
</div>

