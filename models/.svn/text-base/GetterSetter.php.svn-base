<?php
/**
 * GetterSetter.php - Automagically allows to call getVariablename and setVariablename for defined variables
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

class GetterSetter {

    /**
     * This function is executed every time a function is called in the object-instance.
     * Checks, if whe try to call a get*- or set*-function and returns / sets the queried
     * variable.
     *
     * Cannot be invoked directly!
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 3) == 'get') {
            $variable = strtolower(substr($method, 3, strlen($method) -3));
            if (property_exists($this, $variable)) {
                return $this->$variable;
            } else {
                throw new Exception(__CLASS__ ."::$method() does not exist!");
            }   
        } else if (substr($method, 0, 3) == 'set') {            
            $variable = strtolower(substr($method, 3, strlen($method) -3));
            if (sizeof($args) != 1) {
                throw new Exception("wrong parameter count: ".__CLASS__ ."::$method() expects 1 parameter!");
            }   

            if (!property_exists($this, $variable)) {
                throw new Exception(__CLASS__ ."::$method() does not exist!");
            }

            $this->$variable = $args[0];
            return true;
        }   

        throw new Exception(__CLASS__ . "::$method() does not exists!");
    }   
}
