<?php

class SearchStg extends SearchType {
    
	private $uselessIDs = array();
	
    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle() {
        return "";
    }
    
    /**
     * @ids array: array of id-strings that won't appear in future searches
     */
    public function setUselessIDs($IDs = array()) {
        
    }
    
    public function getResults($keyword, $contextual_data = array()) 
    {
        $db = DBManager::get();
        $statement = $db->prepare($this->get_sql(), array(PDO::FETCH_NUM));
        $data = array();
        if (is_array($contextual_data)) {
            foreach ($contextual_data as $name => $value) {
               if (($name !== "input") && (strpos($this->sql, ":".$name) !== FALSE)) {
                  $data[":".$name] = $value;
               }
            }
        }
        $data[":input"] = "%".$input."%";
        $statement->execute($data);
        $results = $statement->fetchAll();
        return $results;
    }
    
    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return string path to this class
     */
    public function includePath() {
        return __file__;
    }
    
    private function get_sql() {
        return "SELECT stg_profil.profil_id, CONCAT('(', abschluss.name, ') ', studiengaenge.name) " .
               "FROM studiengaenge " .
                   "LEFT JOIN stg_profil ON (studiengaenge.studiengang_id = stg_profil.fach_id) " .
                   "LEFT JOIN abschluss ON (stg_profil.abschluss_id = abschluss.abschluss_id) " .
               "WHERE " .
                   "stg_profil.profil_id NOT IN ('".implode("', '", $this->uselessIDs)."') " .
                   "AND " .
                       "(abschluss.name LIKE :input OR abschluss.name LIKE :input)" .
               "LIMIT 10 ";
    }
}

