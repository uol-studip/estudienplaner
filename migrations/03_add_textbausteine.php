<?php

/*
 *  Copyright (c) 2013 Jan-Hendrik Willms <tleilax+studip@gmail.com>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */


class AddTextbausteine extends DBMigration
{
    function up(){
        $query = "CREATE TABLE IF NOT EXISTS `stg_textbausteine` (
            `textbaustein_id` char(32) NOT NULL DEFAULT '',
            `code` varchar(64) NOT NULL DEFAULT '',
            `title` varchar(255) NOT NULL DEFAULT '',
            `content` text NOT NULL,
            `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
            `chdate` int(11) unsigned NOT NULL DEFAULT '0',
            `user_id` char(32) NOT NULL DEFAULT '',
            PRIMARY KEY (`textbaustein_id`),
            UNIQUE KEY `code` (`code`)
        )";
        DBManager::get()->exec($query);
    }
}
