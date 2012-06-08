<?

class Fristen {

    public static function GetZielgruppen()
    {
        $zielgruppen = array();
        try {
            $result = DBManager::Get()->query("SELECT * FROM stg_zielgruppen");
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $zielgruppen[$row['zielgruppen_id']] = $row;
            }
            return $zielgruppen;
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public static function GetAbschluesse()
    {
        $abschluesse = array();
        try {
            $result = DBManager::Get()->query("SELECT abschluss_id, name FROM abschluesse ORDER BY name ASC");
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $abschluesse[$row['abschluss_id']] = $row;
            }
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public static function GetFaecherByAbschluss($abschluss_id)
    {
        $faecher = array();
        try {
            $stmt = DBManager::Get()->prepare("SELECT fach_id, name FROM stg_profil sp LEFT JOIN studiengaenge ON (fach_id = studiengang_id) WHERE sp.abschluss_id = ? ORDER BY name ASC");
            $result = $stmt->execute(array($abschluss_id));
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $faecher[$row['fach_id']] = $row;
            }
            return $faecher;
        }
        catch (PDOException $e) {
            return false;
        }
    }

    public static function GetBewerbungsdatenByAbschluss($abschluss_id)
    {
        $bdaten = array();
        $stmt = DBManager::Get()->prepare("SELECT *, DATE_FORMAT(startzeit_sose, '%d.%m') as sz_sose, "
            . "DATE_FORMAT(startzeit_wise, '%d.%m') as sz_wise, "
            . "DATE_FORMAT(endzeit_sose, '%d.%m') as ez_sose, "
            . "DATE_FORMAT(endzeit_wise, '%d.%m') as ez_wise "
            . "FROM stg_profil p "
            . "LEFT JOIN studiengaenge g ON (fach_id = studiengang_id) "
            . "LEFT JOIN b stg_bewerben ON (p.profil_id = b.stg_profil_id) "
            . "LEFT JOIN stg_zielgruppen USING(zielgruppen_id) "
            . "WHERE p.abschluss_id = ? ORDER BY g.name");
        $result = $stmt->execute(array($abschluss_id));
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bdaten[$row['fach_id']]['name'] = $row['name'];
            $bdaten[$row['fach_id']]['fach_id'] = $row['fach_id'];

            /*
            switch ($row['zielgruppen_id']) {
                case 1:
                    $bdaten[$row['fach_id']]['dates']['BE_SS_DE_LO'] = $row['sz_sose'];
                    $bdaten[$row['fach_id']]['dates']['BE_WS_DE_LO'] = $row['sz_wise'];
                    $bdaten[$row['fach_id']]['dates']['EN_SS_DE_LO'] = $row['ez_sose'];
                    $bdaten[$row['fach_id']]['dates']['EN_WS_DE_LO'] = $row['ez_wise'];
                   // $bdaten[$row['fach_id']]['einschreibungsverfahren']['DE_LO'] = $row['einschreibungsverfahren'];
                   // $bdaten[$row['fach_id']]['bewerbungsverfahren']['DE_LO'] = $row['bewerbungsverfahren'];
                    $bdaten[$row['fach_id']]['begin']['SS_DE_LO'] = $row['begin_sose'] == 1;
                    $bdaten[$row['fach_id']]['begin']['WS_DE_LO'] = $row['begin_wise'] == 1;
                    break;
                case 2:
                    $bdaten[$row['fach_id']]['dates']['BE_SS_DE_HI'] = $row['sz_sose'];
                    $bdaten[$row['fach_id']]['dates']['BE_WS_DE_HI'] = $row['sz_wise'];
                    $bdaten[$row['fach_id']]['dates']['EN_SS_DE_HI'] = $row['ez_sose'];
                    $bdaten[$row['fach_id']]['dates']['EN_WS_DE_HI'] = $row['ez_wise'];
                   // $bdaten[$row['fach_id']]['einschreibungsverfahren']['DE_HI'] = $row['einschreibungsverfahren'];
                   // $bdaten[$row['fach_id']]['bewerbungsverfahren']['DE_HI'] = $row['bewerbungsverfahren'];
                    $bdaten[$row['fach_id']]['begin']['SS_DE_HI'] = $row['begin_sose'] == 1;
                    $bdaten[$row['fach_id']]['begin']['WS_DE_HI'] = $row['begin_wise'] == 1;
                    break;
                case 3:
                    $bdaten[$row['fach_id']]['dates']['BE_SS_EU_LO'] = $row['sz_sose'];
                    $bdaten[$row['fach_id']]['dates']['BE_WS_EU_LO'] = $row['sz_wise'];
                    $bdaten[$row['fach_id']]['dates']['EN_SS_EU_LO'] = $row['ez_sose'];
                    $bdaten[$row['fach_id']]['dates']['EN_WS_EU_LO'] = $row['ez_wise'];
                   // $bdaten[$row['fach_id']]['einschreibungsverfahren']['EU_LO'] = $row['einschreibungsverfahren'];
                   // $bdaten[$row['fach_id']]['bewerbungsverfahren']['EU_LO'] = $row['bewerbungsverfahren'];
                    $bdaten[$row['fach_id']]['begin']['SS_EU_LO'] = $row['begin_sose'] == 1;
                    $bdaten[$row['fach_id']]['begin']['WS_EU_LO'] = $row['begin_wise'] == 1;
                    break;
                case 4:
                    $bdaten[$row['fach_id']]['dates']['BE_SS_EU_HI'] = $row['sz_sose'];
                    $bdaten[$row['fach_id']]['dates']['BE_WS_EU_HI'] = $row['sz_wise'];
                    $bdaten[$row['fach_id']]['dates']['EN_SS_EU_HI'] = $row['ez_sose'];
                    $bdaten[$row['fach_id']]['dates']['EN_WS_EU_HI'] = $row['ez_wise'];
                   // $bdaten[$row['fach_id']]['einschreibungsverfahren']['EU_HI'] = $row['einschreibungsverfahren'];
                   // $bdaten[$row['fach_id']]['bewerbungsverfahren']['EU_HI'] = $row['bewerbungsverfahren'];
                    $bdaten[$row['fach_id']]['begin']['SS_EU_HI'] = $row['begin_sose'] == 1;
                    $bdaten[$row['fach_id']]['begin']['WS_EU_HI'] = $row['begin_wise'] == 1;
                    break;
            }
            */

            $bdaten[$row['profil_Id']]['dates'][$row['zielgruppen_id']]['BE_SS'] = $row['sz_sose'];
            $bdaten[$row['profil_Id']]['dates'][$row['zielgruppen_id']]['BE_WS'] = $row['sz_wise'];
            $bdaten[$row['profil_Id']]['dates'][$row['zielgruppen_id']]['EN_SS'] = $row['ez_sose'];
            $bdaten[$row['profil_Id']]['dates'][$row['zielgruppen_id']]['EN_WS'] = $row['ez_wise'];
            $bdaten[$row['fach_id']]['begin'][$row['zielgruppen_id']]['SS'] = $row['begin_sose'] == 1;
            $bdaten[$row['fach_id']]['begin'][$row['zielgruppen_id']]['WS'] = $row['begin_wise'] == 1;


            $bdaten[$row['fach_id']]['restrictions']['JA'] = $row['zulassungsvoraussetzungen'] == 'ja';
            $bdaten[$row['fach_id']]['restrictions']['V_JA'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich ja';
            $bdaten[$row['fach_id']]['restrictions']['NEIN'] = $row['zulassungsvoraussetzungen'] == 'nein';
            $bdaten[$row['fach_id']]['restrictions']['V_NEIN'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich nein';

            /*
            $bdaten[$row['fach_id']]['restrictions']['WS_JA'] = $row['zulassungsvoraussetzungen'] == 'ja';
            $bdaten[$row['fach_id']]['restrictions']['WS_JA?'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich ja';
            $bdaten[$row['fach_id']]['restrictions']['WS_NEIN'] = $row['zulassungsvoraussetzungen'] == 'nein';
            $bdaten[$row['fach_id']]['restrictions']['WS_NEIN?'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich nein';
            $bdaten[$row['fach_id']]['restrictions']['SS_JA'] = $row['zulassungsvoraussetzungen'] == 'ja';
            $bdaten[$row['fach_id']]['restrictions']['SS_JA?'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich ja';
            $bdaten[$row['fach_id']]['restrictions']['SS_NEIN'] = $row['zulassungsvoraussetzungen'] == 'nein';
            $bdaten[$row['fach_id']]['restrictions']['SS_NEIN?'] = $row['zulassungsvoraussetzungen'] == 'voraussichtlich nein';
            */
        }

    }

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
                . 'stg_profil_id = :pid, zielgruppen_id = :zid, '
                . "startzeit_wise = IF(:sw == '', NULL, STR_TO_DATE('%d.%m', :sw)), "
                . "startzeit_sose = IF(:ss == '', NULL, STR_TO_DATE('%d.%m', :ss)), "
                . "endzeit_wise = IF(:ew == '', NULL, STR_TO_DATE('%d.%m', :ew)), "
                . "endzeit_sose = IF(:es == '', NULL, STR_TO_DATE('%d.%m', :es)), "
                . 'begin_wise = :bw, begin_sose = :bs '
                . 'ON DUPLICATE KEY UPDATE VALUES(startzeit_wise, startzeit_sose, '
                . 'endzeit_wise, endzeit_sose, begin_wise, begin_sose)');
            foreach ($zielgruppen as $zielgruppe) {

                /*
                switch ($zielgruppe) {
                    case 1:
                        $stmt->execute(array('pid' => $profil_id,
                            'zid' => $zielgruppe,
                            'sw' => $data['dates']['BE_WS_DE_LO'],
                            'ss' => $data['dates']['BE_SS_DE_LO'],
                            'ew' => $data['dates']['EN_WS_DE_LO'],
                            'es' => $data['dates']['EN_SS_DE_LO'],
                            'bw' => $data['restrictions']['WS_DE_LO'],
                            'bs' => $data['restrictions']['SS_DE_LO']));
                        break;
                    case 2:
                        $stmt->execute(array('pid' => $profil_id,
                            'zid' => $zielgruppe,
                            'sw' => $data['dates']['BE_WS_DE_HI'],
                            'ss' => $data['dates']['BE_SS_DE_HI'],
                            'ew' => $data['dates']['EN_WS_DE_HI'],
                            'es' => $data['dates']['EN_SS_DE_HI'],
                            'bw' => $data['restrictions']['WS_DE_HI'],
                            'bs' => $data['restrictions']['SS_DE_HI']));
                        break;
                    case 3:
                        $stmt->execute(array('pid' => $profil_id,
                            'zid' => $zielgruppe,
                            'sw' => $data['dates']['BE_WS_EU_LO'],
                            'ss' => $data['dates']['BE_SS_EU_LO'],
                            'ew' => $data['dates']['EN_WS_EU_LO'],
                            'es' => $data['dates']['EN_SS_EU_LO'],
                            'bw' => $data['restrictions']['WS_EU_LO'],
                            'bs' => $data['restrictions']['SS_EU_LO']));
                        break;
                    case 4:
                        $stmt->execute(array('pid' => $profil_id,
                            'zid' => $zielgruppe,
                            'sw' => $data['dates']['BE_WS_EU_HI'],
                            'ss' => $data['dates']['BE_SS_EU_HI'],
                            'ew' => $data['dates']['EN_WS_EU_HI'],
                            'es' => $data['dates']['EN_SS_EU_HI'],
                            'bw' => $data['restrictions']['WS_EU_HI'],
                            'bs' => $data['restrictions']['SS_EU_HI']));
                        break;
                }
                */

                $stmt->execute(array('pid' => $profil_id,
                            'zid' => $zielgruppe,
                            'sw' => $data['dates'][$zielgruppe]['BE_WS'],
                            'ss' => $data['dates'][$zielgruppe]['BE_SS'],
                            'ew' => $data['dates'][$zielgruppe]['EN_WS'],
                            'es' => $data['dates'][$zielgruppe]['EN_SS'],
                            'bw' => $data['restrictions'][$zielgruppe]['WS'],
                            'bs' => $data['restrictions'][$zielgruppe]['SS']));

            }
        }
        catch (PDOException $e) {
            return false;
        }
    }


}