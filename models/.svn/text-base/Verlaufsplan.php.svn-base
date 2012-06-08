<?php
/**
 * Verlaufsplan.php - Represents a Studyplan
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

require_once dirname(__file__) . '/exceptions/VerlaufsplanNotFoundException.php';
require_once dirname(__file__) . '/GetterSetter.php';
require_once dirname(__file__) . '/Module.php';

class Verlaufsplan extends GetterSetter
{
    protected $verlaufsplan_id;
    protected $stg_profil_id;
    protected $version;
    protected $titel;
    protected $untertitel;
    protected $notiz;
    protected $fach_kombi_id;
    protected $sichtbar_fach1;
    protected $sichtbar_fach2;
    protected $user_id;
    protected $modules = array();
    protected $verlauf_typen = array();
    
    /**
     * data from/for the table stg_verlaufsplan_kp
     */
    protected $kp;

    /**
     * constructs and loads a plan, if id is submitted, creates an empty one otherwise
     *
     * @param  int  $verlaufsplan_id  id or false
     */
    public function __construct($verlaufsplan_id = false)
    {
        if ($verlaufsplan_id) {
            $this->restore($verlaufsplan_id);
        }
    }

    /**
     * returns the name of the stg_profil_id (degree)
     *
     * @return string name of the degree
     */
    public function getAbschluss()
    {
        return implode(' » ', ModuleHandler::getPath($this->stg_profil_id));
    }

    /**
     * Returns an array of modules arranged by pos in the plan
     *
     * @return  array  multidimensional array, containing modules
     */
    public function getAllModules() {
        $ret = array(
            'width'   => 0,
            'height'  => 0,
            'modules' => array()
        );

        foreach ($this->modules as $module) {
            $ret['height'] = max($module->fachsem, $ret['height']);
            $ret['width']  = max($module->position, $ret['width']);
            $ret['modules'][$module->fachsem][$module->position][] = $module;
        }

        if ($ret['height'] == 0) $ret['height']++;
        if ($ret['width'] % 2 == 0) $ret['width']++;

        return $ret;
    }

    public function clearModules() {
        DBManager::get()->query(
            "DELETE FROM stg_verlaufsplan_eintraege " .
            "WHERE stg_verlaufsplan_id = '{$this->verlaufsplan_id}' " .
        "");
    }
    
    public function addModule($fachsem, $position, $hoehe, $sem_tree_id, $kp, $verlauf_typ, $notiz) {
        $stmt = DBManager::get()->prepare(
            "REPLACE INTO stg_verlaufsplan_eintraege (" .
                "stg_verlaufsplan_id, " .
                "fachsem, " .
                "position, " .
                "position_hoehe, " .
                "kp, " .
                "sem_tree_id, " .
                "verlauf_typ_id, " .
                "modul_notiz) " .
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute(array(
            $this->verlaufsplan_id, 
            $fachsem,
            $position,
            $hoehe,
            $kp,
            $sem_tree_id,
            $verlauf_typ,
            $notiz
        ));
    }
    
    /**
     * Fetches the data for the submitted studyplan from the database into the
     * object-instance
     *
     * @param  string  $verlaufsplan_id  The id of the plan to fetch.
     */
    public function restore($verlaufsplan_id)
    {
        $stmt = DBManager::get()->prepare("SELECT * FROM stg_verlaufsplan
            WHERE verlaufsplan_id = ?");
        $stmt->execute(array($verlaufsplan_id));

        // restore Verlaufsplan
        if($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach ($data as $field => $content) {
                $this->$field = $content;
            }
        } else {
            throw new VerlaufsplanNotFoundException(__CLASS__ .', '. __FUNCTION__ .' on line '. __LINE__);
        }

        // restore modules of Verlaufsplan
        $stmt = DBManager::get()->prepare(
            "SELECT stg_verlaufsplan_eintraege.*, sem_tree.name " .
            "FROM stg_verlaufsplan_eintraege " .
                "LEFT JOIN sem_tree USING (sem_tree_id) " .
            "WHERE stg_verlaufsplan_id = ? " .
        "");
        $stmt->execute(array($verlaufsplan_id));

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $module = new Module();
            foreach ($data as $field => $content) {
                $module->$field = $content;
            }
            $this->modules[] = $module;
            unset($module);
        }

        // restore verlauf_typen
        $verlauf_typen = DBManager::get()->query(
            "SELECT * " .
            "FROM stg_verlauf_typ " .
            "WHERE 1 " .
        "")->fetchAll(PDO::FETCH_ASSOC);
        $this->verlauf_typen = array();
        foreach ($verlauf_typen as $typ) {
            $this->verlauf_typen[$typ['verlauf_typ_id']] = $typ;
        }

    }

    /**
     * Stores the plan to the db
     *
     * @return void
     */
    public function store()
    {
        $db = DBManager::get();
        if ($this->verlaufsplan_id) { 
            // update 
            $stmt = $db->prepare("UPDATE stg_verlaufsplan
                SET stg_profil_id = ?, titel = ?, untertitel = ?, version = ?, notiz = ?,
                    fach_kombi_id = ?, sichtbar_fach1 = ?, sichtbar_fach2 = ?, user_id = ?
                WHERE verlaufsplan_id = ?");
        } else {
            // insert
            $this->verlaufsplan_id = $db->query("SELECT verlaufsplan_id FROM stg_verlaufsplan
                ORDER BY verlaufsplan_id DESC LIMIT 1")->fetchColumn() + 1;

            $stmt = $db->prepare("INSERT INTO stg_verlaufsplan
                (stg_profil_id, titel, untertitel, version, notiz, fach_kombi_id,
                    sichtbar_fach1, sichtbar_fach2, user_id, verlaufsplan_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        }

        $stmt->execute(array($this->stg_profil_id, $this->titel, $this->untertitel, 
            $this->version, $this->notiz, $this->fach_kombi_id, $this->sichtbar_fach1,
            $this->sichtbar_fach2, $GLOBALS['user']->id, $this->verlaufsplan_id));
    }

    /* * * * * * * * * * * * * * * * * *
     *   S T A T I C   M E T H O D S   *
     * * * * * * * * * * * * * * * * * */

    /**
     * get list of sutdyplans, leave user_id blank to receive a complete list
     * of all availabe studyplans
     *
     * @param string $user_id
     * @return Verlaufsplan list of studyplans
     */
    static function getList($user_id = false)
    {
        $ret = array();

        // TODO: Berücksichtigen der Zuordnung des FSB zu Abschlüssen
        
        // if no user_id is given, fetch all studyplans
        foreach (DBManager::get()->query("SELECT verlaufsplan_id FROM stg_verlaufsplan
                WHERE 1 ORDER BY user_id, version DESC, titel")->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $ret[] = new Verlaufsplan($data['verlaufsplan_id']);
        }

        return $ret;
    }

    /**
     * Delete the submitted plan
     * @param  int  $verlaufsplan_id
     * @return boolean true
     */
    static function delete($verlaufsplan_id)
    {
        $stmt = DBManager::get()->prepare("DELETE FROM stg_verlaufsplan
            WHERE verlaufsplan_id = ?");
        $stmt->execute(array($verlaufsplan_id));

        $stmt = DBManager::get()->prepare("DELETE FROM stg_verlaufsplan_eintraege
            WHERE stg_verlaufsplan_id = ?");
        $stmt->execute(array($verlaufsplan_id));

        return true;
    }
}