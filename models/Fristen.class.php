<?

require_once dirname(__file__)."/DBHelper.class.php";

class Fristen {

    public static function GetZielgruppen()
    {
        $zielgruppen_innenaussen = DBHelper::getEnumOptions("stg_bewerben", "de_eu");
        $zielgruppen_fachsemester = DBHelper::getEnumOptions("stg_bewerben", "erst_hoeher");

        $zielgruppen = array();
        foreach ($zielgruppen_innenaussen as $de_eu) {
            foreach ($zielgruppen_fachsemester as $erst_hoeher) {
                $zielgruppen[$de_eu."_".$erst_hoeher] = "Bewerbung in ".
                    ($erst_hoeher === "erst" ? "das erste" : "ein h�heres")." Semester, " .
                    ($de_eu === "de" ? "dt." : "internat.")." HZB";
            }
        }
        return $zielgruppen;
    }

    public static function GetAbschluesse()
    {
        $abschluesse = array();
        try {
            $result = DBManager::Get()->query("SELECT abschluss_id, name FROM abschluss ORDER BY name ASC");
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $abschluesse[$row['abschluss_id']] = $row;
            }
        }
        catch (PDOException $e) {
            return false;
        }
        return $abschluesse;
    }

    public static function GetFaecherByAbschluss($abschluss_id)
    {
        $faecher = array();
        try {
            $stmt = DBManager::Get()->prepare("SELECT fach_id, name FROM stg_profil sp LEFT JOIN studiengaenge ON (fach_id = studiengang_id) WHERE sp.abschluss_id = ? ORDER BY name ASC");
            $stmt->execute(array($abschluss_id));
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $faecher[$row['fach_id']] = $row;
            }
            return $faecher;
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public static function GetBewerbungsdatenByStudiengang($studiengang_id = null)
    {
        $bdaten = array();
        $query = "SELECT g.name, abschluss.name AS abschluss_name, "
            . "b.*, p.*, "
            . "DATE_FORMAT(startzeit_sose, '%d.%m.%Y') as sz_sose, "
            . "DATE_FORMAT(startzeit_wise, '%d.%m.%Y') as sz_wise, "
            . "DATE_FORMAT(endzeit_sose, '%d.%m.%Y') as ez_sose, "
            . "DATE_FORMAT(endzeit_wise, '%d.%m.%Y') as ez_wise "
            . "FROM stg_profil AS p "
            . "INNER JOIN studiengaenge AS g ON (fach_id = studiengang_id) "
            . "LEFT JOIN stg_bewerben AS b ON (p.profil_id = b.stg_profil_id) "
            . "LEFT JOIN abschluss ON (p.abschluss_id = abschluss.abschluss_id) ";
        if ($studiengang_id) {
            $query .= "WHERE g.studiengang_id = ? ";
            $query .= "ORDER BY g.name ASC, abschluss.name ASC ";
            $stmt = DBManager::Get()->prepare($query);
            $stmt->execute(array($studiengang_id));
        } else {
            $query .= "ORDER BY g.name ASC, abschluss.name ASC ";
            $stmt = DBManager::Get()->prepare($query);
            $stmt->execute();
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bdaten[$row['profil_id']]['name'] = $row['name'];
            $bdaten[$row['profil_id']]['fach_id'] = $row['fach_id'];
            $bdaten[$row['profil_id']]['abschluss_name'] = $row['abschluss_name'];

            $zielgruppe = $row['de_eu']."_".$row['erst_hoeher'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['BE_SS'] = $row['sz_sose'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['BE_WS'] = $row['sz_wise'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['EN_SS'] = $row['ez_sose'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['EN_WS'] = $row['ez_wise'];
            $bdaten[$row['profil_id']]['begin'][$zielgruppe]['SS'] = $row['begin_sose'] == 1;
            $bdaten[$row['profil_id']]['begin'][$zielgruppe]['WS'] = $row['begin_wise'] == 1;


            $bdaten[$row['profil_id']]['restrictions']['JA'] = $row['zulassungsvoraussetzungen'] == 'ja';
            $bdaten[$row['profil_id']]['restrictions']['V_JA'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich ja';
            $bdaten[$row['profil_id']]['restrictions']['NEIN'] = $row['zulassungsvoraussetzungen'] == 'nein';
            $bdaten[$row['profil_id']]['restrictions']['V_NEIN'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich nein';

            $bdaten[$row['profil_id']]['zulassungsvoraussetzung_wise'] = $row['zulassungsvoraussetzung_wise'];
            $bdaten[$row['profil_id']]['zulassungsvoraussetzung_sose'] = $row['zulassungsvoraussetzung_sose'];
            $bdaten[$row['profil_id']]['besonderezulassungsvoraussetzung_wise'] = $row['besonderezulassungsvoraussetzung_wise'];
            $bdaten[$row['profil_id']]['besonderezulassungsvoraussetzung_sose'] = $row['besonderezulassungsvoraussetzung_sose'];
        }
        return $bdaten;
    }
    
    public static function GetBewerbungsdatenByAbschluss($abschluss = null)
    {
        $bdaten = array();
        $query = "SELECT g.name, abschluss.name AS abschluss_name, "
            . "b.*, p.*, "
            . "DATE_FORMAT(startzeit_sose, '%d.%m.%Y') as sz_sose, "
            . "DATE_FORMAT(startzeit_wise, '%d.%m.%Y') as sz_wise, "
            . "DATE_FORMAT(endzeit_sose, '%d.%m.%Y') as ez_sose, "
            . "DATE_FORMAT(endzeit_wise, '%d.%m.%Y') as ez_wise "
            . "FROM stg_profil AS p "
            . "INNER JOIN studiengaenge AS g ON (fach_id = studiengang_id) "
            . "LEFT JOIN stg_bewerben AS b ON (p.profil_id = b.stg_profil_id) "
            . "LEFT JOIN abschluss ON (p.abschluss_id = abschluss.abschluss_id) ";
        if ($abschluss) {
            $query .= "WHERE abschluss.abschluss_id = ? ";
            $query .= "ORDER BY g.name ASC, abschluss.name ASC ";
            $stmt = DBManager::Get()->prepare($query);
            $stmt->execute(array($abschluss));
        } else {
            $query .= "ORDER BY g.name ASC, abschluss.name ASC ";
            $stmt = DBManager::Get()->prepare($query);
            $stmt->execute();
        }
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bdaten[$row['profil_id']]['name'] = $row['name'];
            $bdaten[$row['profil_id']]['fach_id'] = $row['fach_id'];
            $bdaten[$row['profil_id']]['abschluss_name'] = $row['abschluss_name'];

            $zielgruppe = $row['de_eu']."_".$row['erst_hoeher'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['BE_SS'] = $row['sz_sose'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['BE_WS'] = $row['sz_wise'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['EN_SS'] = $row['ez_sose'];
            $bdaten[$row['profil_id']]['dates'][$zielgruppe]['EN_WS'] = $row['ez_wise'];
            $bdaten[$row['profil_id']]['begin'][$zielgruppe]['SS'] = $row['begin_sose'] == 1;
            $bdaten[$row['profil_id']]['begin'][$zielgruppe]['WS'] = $row['begin_wise'] == 1;


            $bdaten[$row['profil_id']]['restrictions']['JA'] = $row['zulassungsvoraussetzungen'] == 'ja';
            $bdaten[$row['profil_id']]['restrictions']['V_JA'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich ja';
            $bdaten[$row['profil_id']]['restrictions']['NEIN'] = $row['zulassungsvoraussetzungen'] == 'nein';
            $bdaten[$row['profil_id']]['restrictions']['V_NEIN'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich nein';

            $bdaten[$row['profil_id']]['zulassungsvoraussetzung_wise'] = $row['zulassungsvoraussetzung_wise'];
            $bdaten[$row['profil_id']]['zulassungsvoraussetzung_sose'] = $row['zulassungsvoraussetzung_sose'];
            $bdaten[$row['profil_id']]['besonderezulassungsvoraussetzung_wise'] = $row['besonderezulassungsvoraussetzung_wise'];
            $bdaten[$row['profil_id']]['besonderezulassungsvoraussetzung_sose'] = $row['besonderezulassungsvoraussetzung_sose'];
        }
        return $bdaten;
    }
    
    /**
     * not tested or used yet
     */
    public static function StoreBewerbungsdatenByAbschluss($abschluss_id, $data)
    {
        try {
            $stmt = DBManager::Get()->prepare("SELECT profil_id FROM stg_profil WHERE abschluss_id = ?");
            $result = $stmt->execute(array(abschluss_id));
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                foreach ($zielgruppen as $zielgruppe) {
                    self::StoreBewerbungByZielgruppe($row['profil_id'], $data[$row['profil_id']]);
                    self::StoreZulassung($row['profil_id'], $data[$row['profil_id']]['restrictions']);
                }
            }
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public static function StoreZulassung($profil_id, $zulassung)
    {
        try {
            $stmt = DBManager::Get()->prepare("UPDATE stg_profil SET zulassungsvoraussetzungen = ? WHERE profil_id = ?");
            return $stmt->execute(array($zulassung, $profil_id));
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public static function StoreBewerbungByProfil($profil_id, $data)
    {
        $zielgruppen = self::GetZielgruppen();
        try {
            $stmt = DBManager::Get()->prepare('INSERT INTO stg_bewerben SET '
                . 'stg_profil_id = :pid, de_eu = :de_eu, erst_hoeher = :erst_hoeher, ' //der unique-index
                . "startzeit_wise = IF(:sw = '', NULL, STR_TO_DATE(:sw, '%d.%m.%Y')), "
                . "startzeit_sose = IF(:ss = '', NULL, STR_TO_DATE(:ss, '%d.%m.%Y')), "
                . "endzeit_wise = IF(:ew = '', NULL, STR_TO_DATE(:ew, '%d.%m.%Y')), "
                . "endzeit_sose = IF(:es = '', NULL, STR_TO_DATE(:es, '%d.%m.%Y')), "
                . 'begin_wise = :bw, begin_sose = :bs '
                . 'ON DUPLICATE KEY UPDATE '
                . "startzeit_wise = IF(:sw = '', NULL, STR_TO_DATE(:sw, '%d.%m.%Y')), "
                . "startzeit_sose = IF(:ss = '', NULL, STR_TO_DATE(:ss, '%d.%m.%Y')), "
                . "endzeit_wise = IF(:ew = '', NULL, STR_TO_DATE(:ew, '%d.%m.%Y')), "
                . "endzeit_sose = IF(:es = '', NULL, STR_TO_DATE(:es, '%d.%m.%Y')), "
                . 'begin_wise = :bw, begin_sose = :bs '
                );
            foreach (array_keys($zielgruppen) as $zielgruppe) {
                list($de_eu, $erst_hoeher) = explode("_", $zielgruppe);
                $stmt->execute(array(
                            'pid' => $profil_id,
                            'de_eu' => $de_eu,
                            'erst_hoeher' => $erst_hoeher,
                            'sw' => $data['dates'][$zielgruppe]['BE_WS'],
                            'ss' => $data['dates'][$zielgruppe]['BE_SS'],
                            'ew' => $data['dates'][$zielgruppe]['EN_WS'],
                            'es' => $data['dates'][$zielgruppe]['EN_SS'],
                            'bw' => $data['begin'][$zielgruppe]['WS'] === "on" ? 1 : 0,
                            'bs' => $data['begin'][$zielgruppe]['SS'] === "on" ? 1 : 0)
                );
            }
            $stmt = DBManager::Get()->prepare('UPDATE stg_profil SET '
                . 'zulassungsvoraussetzung_wise = :zulassungsvoraussetzung_wise, '
                . 'zulassungsvoraussetzung_sose = :zulassungsvoraussetzung_sose, '
                . 'besonderezulassungsvoraussetzung_wise = :besonderezulassungsvoraussetzung_wise, '
                . 'besonderezulassungsvoraussetzung_sose = :besonderezulassungsvoraussetzung_sose '
                . 'WHERE profil_id = :pid ');
            $stmt->execute(array(
                'pid' => $profil_id,
                'zulassungsvoraussetzung_wise' => $data['zulassungsvoraussetzung_wise'],
                'zulassungsvoraussetzung_sose' => $data['zulassungsvoraussetzung_sose'],
                'besonderezulassungsvoraussetzung_wise' => $data['besonderezulassungsvoraussetzung_wise'],
                'besonderezulassungsvoraussetzung_sose' => $data['besonderezulassungsvoraussetzung_sose']
            ));
        }
        catch (PDOException $e) {
            throw $e;
        }
    }


}