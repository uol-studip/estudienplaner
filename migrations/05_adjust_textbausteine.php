<?php
class AdjustTextbausteine extends DBMigration
{
    function up()
    {
        $query = "ALTER TABLE `stg_textbausteine`
                  ADD COLUMN `language` ENUM('de', 'en') NOT NULL DEFAULT 'de' AFTER `code`";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE `stg_textcombinations` (
                    `profil_id` INT(11) NOT NULL,
                    `code` CHAR(8) NOT NULL DEFAULT '',
                    `position` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    `textbaustein_id` CHAR(32) NOT NULL DEFAULT '',
                    `semester` ENUM('always', 'w', 's') NOT NULL DEFAULT 'always',
                    `restriction` ENUM('always', 'f', 'b') NOT NULL DEFAULT 'always',
                    PRIMARY KEY (`profil_id`, `code`, `position`)
                  );";
        DBManager::get()->exec($query);
    }
}
