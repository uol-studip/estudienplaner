<?php

require_once dirname(__file__).'/../../models/StgProfil.class.php';

function oldenburg_spezial_modul_erweiterung($object) {
    $db = DBManager::get();
    if ($db->query("SHOW TABLES LIKE 'mod_zuordnung'")->fetch()) {
        $new_sem_tree_id = $db->query(
            "SELECT sem_tree.parent_id " .
            "FROM mod_zuordnung " .
                "INNER JOIN sem_tree ON (mod_zuordnung.sem_tree_id = sem_tree.sem_tree_id) " .
            "WHERE mod_zuordnung.fach_id = ".$db->quote($object['fach_id'])." " .
                "AND mod_zuordnung.abschluss_id = ".$db->quote($object['abschluss_id'])." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        $object['sem_tree_id'] = $new_sem_tree_id;
    }
}

StgProfil::addTrigger("abschluss_id", "oldenburg_spezial_modul_erweiterung");
