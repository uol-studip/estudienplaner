<?php
/**
 * ModuleRetriever.php - Interface of functions to implmenet for retrieving modules 
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

interface ModuleRetriever
{
    /**
     * Returns a tree in array-form of all modules
     *
     * @param  array  $filter
     * @param  int     $type
     * @return array
     */
    static function getAllModules ($filter = false, $type = 1);

    /**
     * returns parent nodes in sem-tree with name, denoting available modules
     * for submitted user
     *
     * @param  string  $user_id
     * @return  mixed  an array of sem_tree_ids
     */
    static function getModulesForUser($user_id, $filter = false);

    /**
     * Get all child-modules for submitted sem_tree_ids
     *
     * @param  array  $list_of_module_ids
     * @return array
     */
    static function getModules($list_of_module_ids);

    /**
     * Return an array of names from the sem_tree,
     * which are the path to the submitted module
     *
     * @param  string  $module_id
     * @return array
     */
    static function getPath($module_id);
}
