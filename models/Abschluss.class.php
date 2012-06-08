<?php

class Abschluss extends SimpleORMap {
    protected $db_table = 'abschluss';
    
    static function findAllUsed() {
        $db = DBManager::get();
        $abschluesse = $db->query(
            "SELECT DISTINCT abschluss.abschluss_id " .
            "FROM abschluss " .
                "INNER JOIN stg_profil ON (abschluss.abschluss_id = stg_profil.abschluss_id) " .
            "ORDER BY abschluss.name ASC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($abschluesse as $key => $abschluss_id) {
            $abschluesse[$key] = new Abschluss($abschluss_id);
        }
        return $abschluesse;
    }
    
    public function getPersonal($role_typ = null) {
        $db = DBManager::get();
    }
}