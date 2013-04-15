<?php
    require_once '../../../../lib/bootstrap.php';

    $id = Request::int('id');
    if (!$id) {
        header('HTTP/1.0 400 Bad request');
        echo '400 - Bad request';
        die;
    }

    $query = "SELECT doku_id, stg_dokumente.sichtbar = 1 AND stg_bereiche.oeffentlich = 1 AS visible
              FROM stg_dokumente
              LEFT JOIN stg_dokument_typ USING (doku_typ_id)
              LEFT JOIN stg_doku_typ_bereich_zuord ON stg_doku_typ_id = doku_typ_id
              LEFT JOIN stg_bereiche ON stg_bereichs_id = bereichs_id
              WHERE doku_id = :doku_id
                AND stg_dokumente.sichtbar = 1
                AND stg_bereiche.oeffentlich = 1";
    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':doku_id', Request::int('id'));
    $statement->execute();
    $db = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$db['doku_id']) {
        header('HTTP/1.0 404 Not found');
        echo '404 - Not found';
        die;
    }

    if (!$db['visible']) {
        header('HTTP/1.0 403 Forbidden');
        echo '403 - Forbidden';
        die;
    }

    require_once 'models/StgFile.class.php';
    $file = new StgFile($id);
    $file->download(true);
