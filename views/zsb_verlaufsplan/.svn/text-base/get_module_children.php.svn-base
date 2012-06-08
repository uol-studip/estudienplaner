<?php
uasort($modules, create_function('$a, $b', 'return strcasecmp($a["name"], $b["name"]);'));
foreach ($modules as $modul) {
    if ($modul['parent_id'] === $modul_id) {
        print $this->render_partial('zsb_verlaufsplan/modulliste.php', array(
            'module' => $modul, 
            'modules' => $modules,
            'module_type' => $this->module_type
        ));
    }
}


