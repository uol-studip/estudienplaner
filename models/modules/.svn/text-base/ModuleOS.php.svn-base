<?php
/**
 * ModuleOldenburg.php - Retrieves the modules Oldenburg-style
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
require_once(dirname(__file__).'/ModuleRetriever.php');
require_once(dirname(__file__).'/ModuleOldenburg.php');

class ModuleOS extends ModuleOldenburg implements ModuleRetriever
{
    /**
     * Returns a tree in array-form of all modules
     *
     * @param  array  $filter
     * @param  int     $type
     * @return array
     */
    static function getAllModules ($filter = false, $type = false) {
        return parent::getAllModules($filter, $type = 3);
    }
}