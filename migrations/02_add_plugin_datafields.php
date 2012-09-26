<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

//see also diffs/

class AddPluginDatafields extends DBMigration
{
	function up(){
		DBManager::get()->exec("
            ALTER TABLE `datafields`
            CHANGE `object_type` `object_type` ENUM( 'sem', 'inst', 'user', 'userinstrole', 'usersemdata', 'roleinstdata', 'plugin' ) NULL DEFAULT NULL
        ");
	}
}