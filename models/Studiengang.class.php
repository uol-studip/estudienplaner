<?php

class Studiengang extends SimpleORMap {
    protected $db_table = 'studiengaenge';
    
    public function getPersonal($role_typ = null) {
        $db = DBManager::get();
    }
}