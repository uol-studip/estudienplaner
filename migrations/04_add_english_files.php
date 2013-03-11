<?php

/*
 *  Copyright (c) 2013 Jan-Hendrik Willms <tleilax+studip@gmail.com>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */


class AddEnglishFiles extends DBMigration
{
    function up(){
        $query = "ALTER IGNORE TABLE `stg_dokumente` ADD COLUMN language ENUM('de', 'en') NOT NULL DEFAULT 'de'";
        DBManager::get()->exec($query);
    }
}
