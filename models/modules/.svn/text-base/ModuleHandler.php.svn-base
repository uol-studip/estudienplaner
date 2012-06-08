<?php
/**
 * ModuleHandler.php - Wrapper
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

define('STRATEGY', 'Oldenburg');
//define('STRATEGY', 'OS');
require_once($this->trails_root . '/models/modules/Module' . STRATEGY . '.php');
require_once($this->trails_root . '/models/modules/ModuleRetriever.php');


class ModuleHandler implements ModuleRetriever
{

    /**
     * Returns a tree in array-form of all modules
     *
     * @param  array  $filter
     * @param  int     $type
     * @return array
     */
    static function getAllModules ($filter = false, $type = false)
    {
        $class = 'Module' . STRATEGY;
        $strategy = new $class;
        return $strategy->getAllModules($filter, $type);
    }

    /**
     * returns parent nodes in sem-tree with name, denoting available modules
     * for submitted user
     *
     * @param  string  $user_id
     * @return  mixed  an array of sem_tree_ids
     */
    static function getModulesForUser($user_id, $filter = false)
    {
        $class = 'Module' . STRATEGY;
        $strategy = new $class;
        return $strategy->getModulesForUser($user_id, $filter);
    }

    /**
     * Get all child-modules for submitted sem_tree_ids
     *
     * @param  array  $list_of_module_ids
     * @return array
     */
    static function getModules($list_of_module_ids)
    {
        $class = 'Module' . STRATEGY;
        $strategy = new $class;
        return $strategy->getModules($list_of_module_ids);
    }

    /**
     * Return an array of names from the sem_tree,
     * which are the path to the submitted module
     *
     * @param  string  $module_id
     * @return array
     */
    static function getPath($module_id)
    {
        $class = 'Module' . STRATEGY;
        $strategy = new $class;
        return $strategy->getPath($module_id);
    }


    /**
     * Flatten multi-dimensional array to form a string representation,
     * adding two spaces for each subtree
     *
     * @param array $array
     *
     * @return array
     */
    static function flattenArray(array $array, $level = 0)
    {
        $spaces = '';
        for ($i = 0; $i < $level; $i++ & $spaces .= '  ');

        $ret_array = array();
        foreach($array as $value) {
            $ret_array[$value['sem_tree_id']] = $value;

            // add spaces in front of the element-name
            $ret_array[$value['sem_tree_id']]['select_name'] = $spaces . $value['name'];

            if (!empty($value['children'])) {
                $ret_array += self::flattenArray($value['children'], $level + 1);
            }
        }
        return $ret_array;
    }
}
