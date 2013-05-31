<?php
class AddExceptionalApplications extends DBMigration
{
    function up()
    {
        $query = "ALTER TABLE `stg_profil`
                  ADD COLUMN `application_exceptional_wise` TINYINT(1) NOT NULL DEFAULT 0,
                  ADD COLUMN `application_exceptional_sose` TINYINT(1) NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        $query = "ALTER TABLE `stg_profil`
                  DROP COLUMN `application_exceptional_wise`,
                  DROP COLUMN `application_exceptional_sose`";
        DBManager::get()->exec($query);
    }
}
