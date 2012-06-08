<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__file__)."/application.php";


class ZsbController extends ApplicationController {

    protected function setHorizontalNavigation($item_id, $all_items, $class_name) {
        $this->next = $this->last = $lastone = null;
        foreach ($all_items as $some_item_id) {
            if ($this->last === null) {
                //wir sind noch nicht bei dem aktuellen Profil angekommen gewesen
                if ($some_item_id === $item_id) {
                    $this->last = $lastone ? new $class_name($lastone) : false;
                }
                $lastone = $some_item_id;
            } elseif ($this->next === null) {
                //in letzter Iteration waren wir beim aktuellen Profil
                $this->next = new $class_name($some_item_id);
            } else {
                break;
            }
        }
    }

    protected function getProfilSuche() {
        global $user;
        $db = DBManager::get();
        if (PersonalRechte::isRoot()) {
            return new SQLSearch(
                    "SELECT stg_profil.profil_id, CONCAT(studiengaenge.name, ' (', abschluss.name , ')') " .
                    "FROM stg_profil " .
                        "LEFT JOIN studiengaenge ON (studiengaenge.studiengang_id = stg_profil.fach_id) " .
                        "LEFT JOIN abschluss ON (abschluss.abschluss_id = stg_profil.abschluss_id) " .
                    "WHERE CONCAT(abschluss.name, ' ', studiengaenge.name, ' (', abschluss.name , ')') LIKE :input " .
            "", _("Studiengang hinzufügen"));
        } else {
            return new SQLSearch(
                    "SELECT stg_profil.profil_id, CONCAT(studiengaenge.name, ' (', abschluss.name , ')') " .
                    "FROM stg_profil " .
                        "INNER JOIN studiengaenge ON (studiengaenge.studiengang_id = stg_profil.fach_id) " .
                        "INNER JOIN stg_fsb_rollen ON (studiengaenge.studiengang_id = stg_fsb_rollen.studiengang_id) " .
                        "LEFT JOIN abschluss ON (abschluss.abschluss_id = stg_profil.abschluss_id) " .
                    "WHERE CONCAT(abschluss.name, ' ', studiengaenge.name, ' (', abschluss.name , ')') LIKE :input " .
                        "AND stg_fsb_rollen.user_id = ".$db->quote($user->id)." " .
                        "AND stg_fsb_rollen.rollen_typ IN ('FSB', 'StuKo') " .
            "", _("Studiengang suchen"));
        }
    }

    protected function getFachkombiSuche() {
        global $user;
        $db = DBManager::get();
        if (PersonalRechte::isRoot()) {
            return new SQLSearch(
                    "SELECT stg_fach_kombination.fach_kombi_id, CONCAT(studiengang1.name, ' (', abschluss1.name , ') - ', studiengang2.name, ' (', abschluss2.name , ')') " .
                    "FROM stg_fach_kombination " .
                        "INNER JOIN stg_profil AS profil1 ON (profil1.profil_id = stg_fach_kombination.stg_profil_id) " .
                        "LEFT JOIN studiengaenge AS studiengang1 ON (studiengang1.studiengang_id = profil1.fach_id) " .
                        "LEFT JOIN abschluss AS abschluss1 ON (abschluss1.abschluss_id = profil1.abschluss_id) " .
                        "INNER JOIN stg_profil AS profil2 ON (profil2.profil_id = stg_fach_kombination.kombi_stg_profil_id) " .
                        "LEFT JOIN studiengaenge AS studiengang2 ON (studiengang2.studiengang_id = profil2.fach_id) " .
                        "LEFT JOIN abschluss AS abschluss2 ON (abschluss2.abschluss_id = profil2.abschluss_id) " .
                    "WHERE CONCAT(studiengang1.name, ' (', abschluss1.name , ') - ', studiengang2.name, ' (', abschluss2.name , ')') LIKE :input " .
            "", _("Kombinationsstudiengang suchen"));
        } else {
            return new SQLSearch(
                    "SELECT stg_fach_kombination.fach_kombi_id, CONCAT(studiengang1.name, ' (', abschluss1.name , ') - ', studiengang2.name, ' (', abschluss2.name , ')') " .
                    "FROM stg_fach_kombination " .
                        "INNER JOIN stg_profil AS profil1 ON (profil1.profil_id = stg_fach_kombination.stg_profil_id) " .
                        "LEFT JOIN studiengaenge AS studiengang1 ON (studiengang1.studiengang_id = profil1.fach_id) " .
                        "LEFT JOIN abschluss AS abschluss1 ON (abschluss1.abschluss_id = profil1.abschluss_id) " .
                        "INNER JOIN stg_profil AS profil2 ON (profil2.profil_id = stg_fach_kombination.kombi_stg_profil_id) " .
                        "LEFT JOIN studiengaenge AS studiengang2 ON (studiengang2.studiengang_id = profil2.fach_id) " .
                        "LEFT JOIN abschluss AS abschluss2 ON (abschluss2.abschluss_id = profil2.abschluss_id) " .
                        "INNER JOIN stg_fsb_rollen ON (studiengang1.studiengang_id = stg_fsb_rollen.studiengang_id OR studiengang2.studiengang_id = stg_fsb_rollen.studiengang_id) " .
                    "WHERE CONCAT(studiengang1.name, ' (', abschluss1.name , ') - ', studiengang2.name, ' (', abschluss2.name , ')') LIKE :input " .
                        "AND stg_fsb_rollen.user_id = ".$db->quote($user->id)." " .
                        "AND stg_fsb_rollen.rollen_typ IN ('FSB', 'StuKo') " .
            "", _("Studiengang hinzufügen"));
        }
    }


}
