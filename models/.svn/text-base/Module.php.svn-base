<?php

/**
 * Module.php - Represents a single module
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <aklassen@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class Module extends GetterSetter implements ArrayAccess
{
    var $stg_verlaufsplan_id;
    var $fachsem;
    var $position;
    var $position_hoehe;
    var $sem_tree_id;
    var $verlauf_typ_id;
    var $modul_notiz;

    /**
     * name of the module in the sem_tree-table, referenced by $sem_tree_id
     */
    var $name;

    /**
     * Credit points of the module in mod_zuordnung
     */
    var $kp;



    /* * * * * * * * * * * * * * * */
    /* ArrayAccess implementation  */
    /* * * * * * * * * * * * * * * */

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetUnset($offset) {
        unset($this->$offset);
    }

    public function offsetGet($offset) {
        return isset($this->$offset) ? $this->$offset : null;
    }
}