<?php

class PersonalRechte {

    protected static $usersStudiengaenge = array();
    protected static $usersVerlaufsplaene = array();
    protected static $usersAnsprechpartner = array();
    protected static $usersDateien = array();
    protected static $userIsRoot = array();
    protected static $userIsPamt = array();
    protected static $userIsIamt = array();
    protected static $userHasPermission = array();
    protected static $userShowStudiengaenge = array();
    protected static $userShowVerlaufsplaene = array();
    protected static $userShowAnsprechpartner = array();
    protected static $userShowDokumente = array();
    protected static $usersStgProfile = array();
    protected static $usersModule = array();
    protected static $userIsStab = array();
    protected static $userVersteckteBereiche = array();

    static public function studiengangRecht($studiengang_id, $user_id = null) {
        global $user, $perm;
        if (self::isRoot()) {
            return true;
        }
        if ($user_id === null) {
            $user_id = $user->id;
        }
        return in_array($studiengang_id, self::meineStudiengaenge($user_id));
    }

    /**
     * Gibt zu dem Nutzer einen Array von Studiengang_id's an, die er/sie
     * administrieren darf.
     * @return: array of id's (maybe empty array)
     */
    static public function meineStudiengaenge($user_id = null, $existent = false) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersStudiengaenge[$user_id]) && $existent === false) {
            return self::$usersStudiengaenge[$user_id];
        }
        $studiengaenge = array();
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            $studiengaenge = $db->query(
                "SELECT DISTINCT studiengaenge.studiengang_id " .
                "FROM studiengaenge " .
                    ($existent !== false ? "INNER JOIN stg_profil ON (studiengaenge.studiengang_id = stg_profil.fach_id)" : "") .
                "ORDER BY name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $studiengaenge = $db->query(
                "SELECT DISTINCT studiengaenge.studiengang_id " .
                "FROM studiengaenge " .
                    "INNER JOIN stg_fsb_rollen ON (studiengaenge.studiengang_id = stg_fsb_rollen.studiengang_id) " .
                    ($existent !== false ? "INNER JOIN stg_profil ON (studiengaenge.studiengang_id = stg_profil.fach_id)" : "") .
                "WHERE stg_fsb_rollen.rollen_typ IN ('FSB', 'StuKo') " .
                    "AND stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
                "GROUP BY studiengaenge.studiengang_id " .
                "ORDER BY name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $studiengaenge = $studiengaenge ? $studiengaenge : array();
        if ($existent === false) {
            self::$usersStudiengaenge[$user_id] = $studiengaenge;
        }
        return $studiengaenge;
    }

    /**
     * Gibt zu dem Nutzer einen Array von Stg_profil_id's an, die er/sie
     * administrieren darf.
     * @return: array of id's (maybe empty array)
     */
    static public function meineProfile($user_id = null) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersStgProfile[$user_id])) {
            return self::$usersStgProfile[$user_id];
        }
        $profile = array();
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            $profile = $db->query(
                "SELECT stg_profil.profil_id " .
                "FROM stg_profil " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $profile = $db->query(
                "SELECT stg_profil.profil_id " .
                "FROM stg_profil " .
                    "INNER JOIN studiengaenge ON (stg_profil.fach_id = studiengaenge.studiengang_id) " .
                    "INNER JOIN stg_fsb_rollen ON (studiengaenge.studiengang_id = stg_fsb_rollen.studiengang_id) " .
                "WHERE stg_fsb_rollen.rollen_typ IN ('FSB', 'StuKo') " .
                    "AND stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $profile = $profile ? $profile : array();
        return self::$usersStgProfile[$user_id] = $profile;
    }

    /**
     * Gibt zu dem Nutzer einen Array von Stg_profil_id's an, die er/sie
     * administrieren darf.
     * @return: array of id's (maybe empty array)
     */
    static public function meineFachkombinationen($user_id = null, $studiengang = null, $abschluss = null) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersStgProfile[$user_id]) && $studiengang === null && $abschluss === null) {
            return self::$usersStgProfile[$user_id];
        }
        $profile = array();
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            $profile = $db->query(
                "SELECT stg_fach_kombination.fach_kombi_id " .
                "FROM stg_fach_kombination " .
                    "LEFT JOIN stg_profil AS p1 ON (stg_fach_kombination.stg_profil_id = p1.profil_id) " .
                    "LEFT JOIN stg_profil AS p2 ON (stg_fach_kombination.kombi_stg_profil_id = p2.profil_id) " .
                    "LEFT JOIN studiengaenge AS s1 ON (p1.fach_id = s1.studiengang_id) " .
                    "LEFT JOIN studiengaenge AS s2 ON (p2.fach_id = s2.studiengang_id) " .
                "WHERE 1=1 " .
                    ($studiengang ? "AND (s1.studiengang_id = ".$db->quote($studiengang)." OR s2.studiengang_id = ".$db->quote($studiengang)." ) " : "") .
                    ($abschluss ? "AND (p1.abschluss_id = ".$db->quote($abschluss)." OR p2.abschluss_id = ".$db->quote($abschluss)." ) " : "") .
                "ORDER BY s1.name COLLATE latin1_german2_ci ASC, s2.name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $studiengaenge = self::meineStudiengaenge($user_id);
            $profile = $db->query(
                "SELECT stg_fach_kombination.fach_kombi_id " .
                "FROM stg_fach_kombination " .
                    "LEFT JOIN stg_profil AS p1 ON (stg_fach_kombination.stg_profil_id = p1.profil_id) " .
                    "LEFT JOIN stg_profil AS p2 ON (stg_fach_kombination.stg_profil_id = p2.profil_id) " .
                    "LEFT JOIN studiengaenge AS s1 ON (p1.fach_id = s1.studiengang_id) " .
                    "LEFT JOIN studiengaenge AS s2 ON (p2.fach_id = s2.studiengang_id) " .
                "WHERE 1=1 " .
                    ($studiengang ? "AND (s1.studiengang_id = ".$db->quote($studiengang)." OR s2.studiengang_id = ".$db->quote($studiengang)." ) " : "") .
                    ($abschluss ? "AND (p1.abschluss_id = ".$db->quote($abschluss)." OR p2.abschluss_id = ".$db->quote($abschluss)." ) " : "") .
                    "AND (s1.studiengang_id IN ('".implode("',", $studiengaenge)."') AND s2.studiengang_id IN ('".implode("',", $studiengaenge)."')) " .
                "ORDER BY s1.name COLLATE latin1_german2_ci ASC, s2.name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $profile = $profile ? $profile : array();
        if ($studiengang === null && $abschluss === null) {
            self::$usersStgProfile[$user_id] = $profile;
        }
        return $profile;
    }

    static public function meineVerlaufsplaene($user_id = null, $studiengang_id = null, $abschluss_id = null) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersVerlaufsplaene[$user_id]) && $studiengang_id === null && $abschluss_id === null) {
            return self::$usersVerlaufsplaene[$user_id];
        }
        $verlaufsplaene = array();
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            $verlaufsplaene = $db->query(
                "SELECT stg_verlaufsplan.verlaufsplan_id " .
                "FROM stg_verlaufsplan " .
                    "LEFT JOIN stg_fach_kombination ON (stg_verlaufsplan.fach_kombi_id = stg_fach_kombination.fach_kombi_id) " .
                    "INNER JOIN stg_profil ON (" .
                        "stg_verlaufsplan.stg_profil_id = stg_profil.profil_id " .
                        "OR stg_fach_kombination.stg_profil_id = stg_profil.profil_id " .
                        "OR stg_fach_kombination.kombi_stg_profil_id = stg_profil.profil_id " .
                    ") " .
                "WHERE 1=1 " .
                    ($studiengang_id ? "AND stg_profil.fach_id = ".$db->quote($studiengang_id)." " : "") .
                    ($abschluss ? "AND stg_profil.abschluss_id = ".$db->quote($abschluss_id)." " : "") .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $verlaufsplaene = $db->query(
                "SELECT DISTINCT stg_verlaufsplan.verlaufsplan_id " .
                "FROM stg_verlaufsplan " .
                    "LEFT JOIN stg_fach_kombination ON (stg_verlaufsplan.fach_kombi_id = stg_fach_kombination.fach_kombi_id) " .
                    "INNER JOIN stg_profil ON (" .
                        "stg_verlaufsplan.stg_profil_id = stg_profil.profil_id " .
                        "OR stg_fach_kombination.stg_profil_id = stg_profil.profil_id " .
                        "OR stg_fach_kombination.kombi_stg_profil_id = stg_profil.profil_id " .
                    ") " .
                    //"INNER JOIN studiengaenge ON (studiengaenge.studiengang_id = stg_profil.fach_id) " .
                    "INNER JOIN stg_fsb_rollen ON (stg_profil.fach_id = stg_fsb_rollen.studiengang_id) " .
                "WHERE stg_fsb_rollen.rollen_typ IN ('FSB', 'StuKo') " .
                    "AND stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
                    ($studiengang_id ? "AND stg_profil.fach_id = ".$db->quote($studiengang_id)." " : "") .
                    ($studiengang_id ? "AND stg_profil.abschluss_id = ".$db->quote($abschluss_id)." " : "") .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $verlaufsplaene = $verlaufsplaene ? $verlaufsplaene : array();
        if ($studiengang_id === null && $abschluss_id === null) {
            self::$usersVerlaufsplaene[$user_id] = $verlaufsplaene;
        }
        return $verlaufsplaene;
    }


    static public function meineModule($user_id = null) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersModule[$user_id])) {
            return self::$usersModule[$user_id];
        }
        $module = array();
        $useless_types = array("0");
        if (self::isRoot($user_id)) {
            $statement = $db->prepare(
                "SELECT DISTINCT sem_tree.sem_tree_id, sem_tree.parent_id, sem_tree.type, sem_tree.name " .
                "FROM sem_tree " .
                    "INNER JOIN mod_zuordnung ON (sem_tree.sem_tree_id = mod_zuordnung.sem_tree_id) " .
                    "INNER JOIN studiengaenge ON (" .
                        "mod_zuordnung.fach_id = studiengaenge.studiengang_id " .
                        //Folgende Studiengänge sind nur virtuell. Hat auch mit dem Hack für die FSBs zu tun.
                        "OR mod_zuordnung.fach_id IN ('x0000000000000000000000000011988','x0000000000000000000000000011989','x0000000000000000000000000011990') " .
                    ") " .
                "WHERE sem_tree.type NOT IN ('".implode("', '", $useless_types)."') " .
            "");
            $statement->execute();
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $statement = $db->prepare(
                "SELECT DISTINCT sem_tree.sem_tree_id, sem_tree.parent_id, sem_tree.type, sem_tree.name " .
                "FROM sem_tree " .
                    "INNER JOIN mod_zuordnung ON (sem_tree.sem_tree_id = mod_zuordnung.sem_tree_id) " .
                    "INNER JOIN stg_fsb_rollen ON (" .
                        "mod_zuordnung.fach_id = stg_fsb_rollen.studiengang_id " .
                        //Etwas freaky, aber in Oldenburg will man, dass Zuständige für das eine Fach, auch die Module der anderen Fächer editieren dürfen. Diese Zeile sollte auch anderen Unis nicht weh tun.
                        "OR (stg_fsb_rollen.studiengang_id = 'x0000000000000000000000000000900' AND mod_zuordnung.fach_id IN ('x0000000000000000000000000011988','x0000000000000000000000000011989','x0000000000000000000000000011990') ) " .
                    ") " .
                "WHERE stg_fsb_rollen.user_id = :user_id " .
                    "AND sem_tree.type NOT IN ('".implode("', '", $useless_types)."') " .
            "");
            $statement->execute(array('user_id' => $user->id));
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach ($results as $result) {
            $module[$result['sem_tree_id']] = $result;
            $module[$result['sem_tree_id']]['children'] = array();
        }

        //Bis jetzt haben wir schon alle wichtigen Module, aber sie müssen noch
        //nicht zusammen hängen (können aber). Damit wir eine schöne hierarchische Struktur bekommen,
        //müssen wir noch bis zur Wurzel hin vervollständigen. Hoffentlich nimmt das nicht
        //zuviele Datenbankabfragen in Beschlag. Überdies kann es dann sein, dass
        //hierdurch auch Module, die streng genommen nicht meine Module sind,
        //mit in den Baum aufgenommen werden. Lässt sich nicht anders machen und wird
        //im Normalfall auch nicht passieren.
        $get_parent_statement = $db->prepare(
            "SELECT sem_tree.sem_tree_id, sem_tree.parent_id, sem_tree.type, sem_tree.name " .
            "FROM sem_tree " .
            "WHERE sem_tree_id = :sem_tree_id " .
        "");
        do {
            $updated = false;
            foreach ($module as $modul_id => $modul) {
                if (!isset($module[$modul['parent_id']]) && $modul['parent_id'] !== 'root') {
                    $get_parent_statement->execute(array('sem_tree_id' => $modul['parent_id']));
                    $neues_modul = $get_parent_statement->fetch(PDO::FETCH_ASSOC);
                    if ($neues_modul) {
                        $module[$neues_modul['sem_tree_id']] = $neues_modul;
                        $module[$neues_modul['sem_tree_id']]['children'] = array();
                        $updated = true;
                    }
                }
            }
        } while ($updated);

        //Bis jetzt weiß jedes Modul über seine Eltern bescheid, und jetzt kommen
        //noch die Kinder des Moduls hinzu:
        foreach ($module as $modul_id => $modul) {
            if ($modul['parent_id'] !== "root" && isset($module[$modul['parent_id']])) {
                $module[$modul['parent_id']]['children'][] = $modul_id;
            }
        }
        return self::$usersModule[$user_id] = $module;
    }


    static public function meineAnsprechpartner($user_id = null, $ansprechpartner_typ_id = null) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersAnsprechpartner[$user_id]) && $ansprechpartner_typ_id === null) {
            return self::$usersAnsprechpartner[$user_id];
        }
        $ansprechpartner = array();
        if (self::isRoot($user_id)) {
            $ansprechpartner = $db->query(
                "SELECT stg_ansprechpartner.ansprechpartner_id " .
                "FROM stg_ansprechpartner " .
                ($ansprechpartner_typ_id ? "WHERE ansprechpartner_typ_id = ".$db->quote($ansprechpartner_typ_id)." " : "") .
                "ORDER BY freitext_name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } elseif(self::isPamt($user_id) || self::isIamt($user_id)) {
            $ansprechpartner = $db->query(
                "SELECT DISTINCT stg_ansprechpartner.ansprechpartner_id " .
                "FROM stg_ansprechpartner " .
                    "INNER JOIN stg_ansprechpartner_typ ON (stg_ansprechpartner_typ.ansprechpartner_typ_id = stg_ansprechpartner.ansprechpartner_typ_id) " .
                    "INNER JOIN stg_bereiche ON (stg_bereiche.bereichs_id = stg_ansprechpartner_typ.stg_bereichs_id) " .
                    "JOIN roles_user " .
                    "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                    "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                    "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
                "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                    "AND plugins.pluginclassname = 'eStudienplaner' " .
                    "AND ( " .
                        "(roles.rolename = 'stg_p-amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                        "OR (roles.rolename = 'stg_i-amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                        "OR (roles.rolename = 'stg_stabstelle_akkreditierung' AND stg_bereiche.sichtbar_stab = '1') " .
                    " ) " .
                    ($ansprechpartner_typ_id ? "AND stg_ansprechpartner.ansprechpartner_typ_id = ".$db->quote($ansprechpartner_typ_id)." " : "") .
                "ORDER BY freitext_name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $ansprechpartner = $db->query(
                "SELECT DISTINCT stg_ansprechpartner.ansprechpartner_id " .
                "FROM stg_ansprechpartner " .
                    "INNER JOIN stg_ansprechpartner_typ ON (stg_ansprechpartner_typ.ansprechpartner_typ_id = stg_ansprechpartner.ansprechpartner_typ_id) " .
                    "INNER JOIN stg_bereiche ON (stg_bereiche.bereichs_id = stg_ansprechpartner_typ.stg_bereichs_id) " .
                    "INNER JOIN stg_ansprech_zuord ON (stg_ansprechpartner.ansprechpartner_id = stg_ansprech_zuord.stg_ansprechpartner_id) " .
                    "INNER JOIN stg_profil ON (stg_ansprech_zuord.stg_profil_id = stg_profil.profil_id) " .
                    "INNER JOIN stg_fsb_rollen ON (stg_profil.fach_id = stg_fsb_rollen.studiengang_id) " .
                "WHERE stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
                    "AND ( " .
                        "(stg_fsb_rollen.rollen_typ = 'FSB' AND stg_bereiche.sichtbar_fsb = '1') " .
                        "OR (stg_fsb_rollen.rollen_typ = 'StuKo' OR stg_bereiche.sichtbar_stuko = '1') " .
                    ") " .
                    ($ansprechpartner_typ_id ? "AND stg_ansprechpartner.ansprechpartner_typ_id = ".$db->quote($ansprechpartner_typ_id)." " : "") .
                "ORDER BY freitext_name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $ansprechpartner = $ansprechpartner ? $ansprechpartner : array();
        if ($ansprechpartner_typ_id === null) {
            self::$usersAnsprechpartner[$user_id] = $ansprechpartner;
        }
        return $ansprechpartner;
    }

    static public function meineDateien($user_id = null, $doku_typ_id = null) {
        global $user;
        $db = DBManager::get();
        $user_id || $user_id = $user->id;
        if (isset(self::$usersDateien[$user_id]) && $doku_typ_id === null) {
            return self::$usersDateien[$user_id];
        }
        $dateien = array();
        if (self::isRoot($user_id)) {
            $dateien = $db->query(
                "SELECT stg_dokumente.doku_id " .
                    "FROM stg_dokumente " .
                ($doku_typ_id ? "WHERE doku_typ_id = ".$db->quote($doku_typ_id)." " : "") .
                "ORDER BY name COLLATE latin1_german2_ci ASC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } elseif (self::isPamt($user_id) || self::isIamt($user_id))  {
            $dateien = $db->query(
                "SELECT DISTINCT stg_dokumente.doku_id " .
                "FROM stg_dokumente " .
                    "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_doku_typ_bereich_zuord.stg_doku_typ_id = stg_dokumente.doku_typ_id) " .
                    "INNER JOIN stg_bereiche ON (stg_bereiche.bereichs_id = stg_doku_typ_bereich_zuord.stg_bereichs_id) " .
                    "JOIN roles_user " .
                    "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                    "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                    "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
                "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                    "AND plugins.pluginclassname = 'eStudienplaner' " .
                    ($doku_typ_id ? "AND doku_typ_id = ".$db->quote($doku_typ_id)." " : "") .
                    "AND ( " .
                        "(roles.rolename = 'stg_p-amt' AND stg_bereiche.sichtbar_pamt = '1') " .
                        "OR (roles.rolename = 'stg_i-amt' AND stg_bereiche.sichtbar_iamt = '1') " .
                        "OR (roles.rolename = 'stg_stabstelle_akkreditierung' AND stg_bereiche.sichtbar_stab = '1') " .
                    " ) " .
                "ORDER BY name COLLATE latin1_german2_ci" .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            //FSB, StuKo
            $dateien = $db->query(
                "SELECT DISTINCT stg_dokumente.doku_id " .
                "FROM stg_dokumente " .
                    "INNER JOIN stg_doku_typ_bereich_zuord ON (stg_doku_typ_bereich_zuord.stg_doku_typ_id = stg_dokumente.doku_typ_id) " .
                    "INNER JOIN stg_bereiche ON (stg_bereiche.bereichs_id = stg_doku_typ_bereich_zuord.stg_bereichs_id) " .
                    "INNER JOIN stg_doku_zuord ON (stg_dokumente.doku_id = stg_doku_zuord.doku_id) " .
                    "INNER JOIN stg_profil ON (stg_doku_zuord.stg_profil_id = stg_profil.profil_id) " .
                    "INNER JOIN stg_fsb_rollen ON (stg_profil.fach_id = stg_fsb_rollen.studiengang_id) " .
                "WHERE stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
                    "AND ( " .
                        "(stg_fsb_rollen.rollen_typ = 'FSB' AND stg_bereiche.sichtbar_fsb = '1') " .
                        "OR (stg_fsb_rollen.rollen_typ = 'StuKo' OR stg_bereiche.sichtbar_stuko = '1') " .
                    ") " .
                    ($doku_typ_id ? "AND doku_typ_id = ".$db->quote($doku_typ_id)." " : "") .
                "ORDER BY name COLLATE latin1_german2_ci" .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        if ($doku_typ_id === null) {
            self::$usersDateien[$user_id] = $dateien;
        }
        return $dateien;
    }

    /**
     * Löscht den internen statischen Cache dieser Klasse
     */
    static public function restore() {
        self::$userIsRoot = array();
        self::$userIsPamt = array();
        self::$userIsIamt = array();
        self::$userHasPermission = array();
        self::$usersAnsprechpartner = array();
        self::$usersVerlaufsplaene = array();
        self::$usersStudiengaenge = array();
        self::$userShowStudiengaenge = array();
        self::$userShowAnsprechpartner = array();
        self::$userShowDokumente = array();
        self::$userShowVerlaufsplaene = array();
        self::$userIsStab = array();
    }

    /**
     * findet heraus, ob der Nutzer Root oder ein Mitglied des ZSB ist.
     */
    static function isRoot($user_id = null) {
        global $perm, $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userIsRoot[$user_id])) {
            return self::$userIsRoot[$user_id];
        }
        $db = DBManager::get();
        $roles = $db->query(
            "SELECT roles.rolename " .
            "FROM roles_user " .
                "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
                "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                "AND plugins.pluginclassname = 'eStudienplaner' " .
                "AND roles.rolename IN ('Root-Administrator(in)', 'stg_zsb') " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        return self::$userIsRoot[$user_id] = (count($roles) || $perm->get_perm($user_id) === "root");
    }

    static function hasPermission($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userHasPermission[$user_id])) {
            return self::$userHasPermission[$user_id];
        }
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            return self::$userHasPermission[$user_id] = true;
        }
        $meineStudiengaenge = self::meineStudiengaenge($user_id);
        return self::$userHasPermission[$user_id] = (count($meineStudiengaenge) > 0);
    }

    static function showStudiengaenge() {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userShowStudiengaenge[$user_id])) {
            return self::$userShowStudiengaenge[$user_id];
        }
        if (self::isRoot($user_id)) {
            //nicht für P-Amt oder I-Amt
            return self::$userShowStudiengaenge[$user_id] = true;
        } else {
            $db = DBManager::get();
            $erlaubt = $db->query(
                "SELECT rollen_typ " .
                "FROM stg_fsb_rollen " .
                "WHERE user_id = ".$db->quote($user_id)." " .
                    "AND rollen_typ IN ('FSB', 'StuKo') " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
            return self::$userShowStudiengaenge[$user_id] = $erlaubt ? true : false;
        }
    }

    static function showVerlaufsplaene() {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userShowVerlaufsplaene[$user_id])) {
            return self::$userShowVerlaufsplaene[$user_id];
        }
        if (self::isRoot($user_id)) {
            //nicht für P-Amt oder I-Amt
            return self::$userShowVerlaufsplaene[$user_id] = true;
        } else {
            $db = DBManager::get();
            $erlaubt = $db->query(
                "SELECT rollen_typ " .
                "FROM stg_fsb_rollen " .
                "WHERE user_id = ".$db->quote($user_id)." " .
                    "AND rollen_typ IN ('FSB', 'StuKo') " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
            return self::$userShowVerlaufsplaene[$user_id] = $erlaubt ? true : false;
        }
    }

    static function showAnsprechpartner() {
        //Nur ZSB und FSB und StuKo?
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userShowAnsprechpartner[$user_id])) {
            return self::$userShowAnsprechpartner[$user_id];
        }
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            return self::$userShowAnsprechpartner[$user_id] = true;
        } else {
            if (!self::hasPermission($user_id)) {
                //keine Studiengaenge zugeordnet - kann zwar FSB sein, nützt ihm aber nix.
                return self::$userShowAnsprechpartner[$user_id] = false;
            } else {
                $db = DBManager::get();
                $erlaubt = $db->query(
                    "SELECT rollen_typ " .
                    "FROM stg_fsb_rollen " .
                    "WHERE user_id = ".$db->quote($user_id)." " .
                        "AND rollen_typ IN (" .
                            "'FSB', " .
                            "'StuKo' " .
                        ") " .
                "")->fetch(PDO::FETCH_COLUMN, 0);
                return self::$userShowAnsprechpartner[$user_id] = $erlaubt ? true : false;
            }
        }
    }

    static function showDokumente() {
        //ZSB, FSB (?), StuKo, I-Amt? P-Amt greift verknüpft über Studiengänge die Dokumente?
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userShowDokumente[$user_id])) {
            return self::$userShowDokumente[$user_id];
        }
        if (self::isRoot($user_id) || self::isPamt($user_id) || self::isIamt($user_id)) {
            return self::$userShowDokumente[$user_id] = true;
        } else {
            return self::$userShowDokumente[$user_id] = true;
            /*if (!self::hasPermission($user_id)) {
                //keine Studiengaenge zugeordnet - kann zwar FSB sein, nützt ihm aber nix.
                return self::$userShowDokumente[$user_id] = false;
            } else {
                $db = DBManager::get();
                $erlaubt = $db->query(
                    "SELECT rollen_typ " .
                    "FROM stg_fsb_rollen " .
                    "WHERE user_id = ".$db->quote($user_id)." " .
                        "AND rollen_typ IN ('FSB', 'StuKo') " .
                "")->fetch(PDO::FETCH_COLUMN, 0);
                return self::$userShowDokumente[$user_id] = $erlaubt ? true : false;
            }
            */
        }
    }


    static public function isPamt($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userIsPamt[$user_id])) {
            return self::$userIsPamt[$user_id];
        }
        if (self::isRoot($user_id)) {
            return self::$userIsPamt[$user_id] = true;
        }
        $db = DBManager::get();
        $roles = $db->query(
            "SELECT roles.rolename " .
            "FROM roles_user " .
                "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
            "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                "AND plugins.pluginclassname = 'eStudienplaner' " .
                "AND roles.rolename IN ('stg_p-amt') " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        return self::$userIsPamt[$user_id] = (count($roles) > 0);
    }

    static public function isIamt($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userIsIamt[$user_id])) {
            return self::$userIsIamt[$user_id];
        }
        if (self::isRoot($user_id)) {
            return self::$userIsIamt[$user_id] = true;
        }
        $db = DBManager::get();
        $roles = $db->query(
            "SELECT roles.rolename " .
            "FROM roles_user " .
                "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
            "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                "AND plugins.pluginclassname = 'eStudienplaner' " .
                "AND roles.rolename IN ('stg_i-amt') " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        return self::$userIsIamt[$user_id] = (count($roles) > 0);
    }

    static public function isStab($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userIsStab[$user_id])) {
            return self::$userIsStab[$user_id];
        }
        if (self::isRoot($user_id)) {
            return self::$userIsStab[$user_id] = true;
        }
        $db = DBManager::get();
        $roles = $db->query(
            "SELECT roles.rolename " .
            "FROM roles_user " .
                "INNER JOIN roles_plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN plugins ON (roles_user.roleid = roles_plugins.roleid) " .
                "INNER JOIN roles ON (roles.roleid = roles_plugins.roleid) " .
            "WHERE roles_user.userid = ".$db->quote($user_id)." " .
                "AND plugins.pluginclassname = 'eStudienplaner' " .
                "AND roles.rolename IN ('stg_stabstelle_akkreditierung') " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        return self::$userIsStab[$user_id] = (count($roles) > 0);
    }

    static public function meineVerstecktenBereiche($user_id = null) {
        global $user;
        $user_id || $user_id = $user->id;
        if (isset(self::$userVersteckteBereiche[$user_id])) {
            return self::$userVersteckteBereiche[$user_id];
        }
        if (self::isRoot($user_id)) {
            return self::$userVersteckteBereiche[$user_id] = true;
        } elseif (self::isIamt($user_id)) {
            $db = DBManager::get();
            return self::$userVersteckteBereiche[$user_id] = (bool) $db->query(
                "SELECT COUNT(*) " .
                "FROM stg_bereiche " .
                "WHERE stg_bereiche.oeffentlich = '0' " .
                    "AND stg_bereiche.sichtbar_iamt = '1' " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
        } elseif (self::isPamt($user_id)) {
            $db = DBManager::get();
            return self::$userVersteckteBereiche[$user_id] = (bool) $db->query(
                "SELECT COUNT(*) " .
                "FROM stg_bereiche " .
                "WHERE stg_bereiche.oeffentlich = '0' " .
                    "AND stg_bereiche.sichtbar_pamt = '1' " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
        } elseif (self::isStab($user_id)) {
            $db = DBManager::get();
            return self::$userVersteckteBereiche[$user_id] = (bool) $db->query(
                "SELECT COUNT(*) " .
                "FROM stg_bereiche " .
                "WHERE stg_bereiche.oeffentlich = '0' " .
                    "AND stg_bereiche.sichtbar_stab = '1' " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            $db = DBManager::get();
            $is_fsb = (bool) $db->query(
                "SELECT DISTINCT COUNT(*) " .
                "FROM stg_fsb_rollen " .
                "WHERE stg_fsb_rollen.rollen_typ = 'FSB' " .
                    "AND stg_fsb_rollen.user_id = ".$db->quote($user_id)." " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
            if ($is_fsb) {
                $has_fsb_versteckte_bereiche = (bool) $db->query(
                    "SELECT DISTINCT COUNT(*) " .
                    "FROM stg_bereiche " .
                    "WHERE stg_bereiche.oeffentlich = '0' " .
                        "AND stg_bereiche.sichtbar_fsb = '1' " .
                "")->fetch(PDO::FETCH_COLUMN, 0);
                self::$userVersteckteBereiche[$user_id] = $is_fsb && $has_fsb_versteckte_bereiche;
            }
        }
        return self::$userVersteckteBereiche[$user_id] = false;
    }

}

