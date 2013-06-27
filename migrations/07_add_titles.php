<?php
class AddTitles extends DBMigration
{
    function up()
    {
        DBManager::get()->exec("ALTER TABLE `stg_profil` ADD COLUMN `title` VARCHAR(255) NOT NULL DEFAULT ''");

        if (DBManager::get()->query("SHOW TABLES LIKE 'stg_studycourse_titles'")->fetchColumn()) {
            DBManager::get()->exec(
                "UPDATE `stg_profil` AS p
                 JOIN `stg_studycourse_titles` AS t ON (p.`abschluss_id` = t.`abschluss_id` AND p.`fach_id` = t.`studiengang_id`)
                 SET p.title = t.title"
            );

            DBManager::get()->exec("DROP TABLE stg_studycourse_titles");
        }

        SORM::expireTableSchemes();
    }
}