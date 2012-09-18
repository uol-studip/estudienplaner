CREATE TABLE `stg_ansprech_zuord` (
  `ansprech_zuord_id` int(11) NOT NULL auto_increment,
  `stg_ansprechpartner_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  `position` smallint(5) NOT NULL,
  PRIMARY KEY  (`ansprech_zuord_id`),
  UNIQUE KEY `stg_ansprech_zuord_unique` (`stg_ansprechpartner_id`,`stg_profil_id`)
);

CREATE TABLE `stg_ansprechpartner` (
  `ansprechpartner_id` int(10) unsigned NOT NULL auto_increment,
  `range_id` char(32) NOT NULL,
  `ansprechpartner_typ_id` int(11) default NULL,
  `range_typ` varchar(45) default NULL,
  `freitext_name` varchar(200) default NULL,
  `freitext_mail` varchar(100) default NULL,
  `freitext_telefon` varchar(100) default NULL,
  `freitext_homepage` varchar(200) default NULL,
  `zusatzinfo` text,
  PRIMARY KEY  (`ansprechpartner_id`)
);

CREATE TABLE `stg_ansprechpartner_typ` (
  `ansprechpartner_typ_id` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  `stg_bereichs_id` int(11) default NULL,
  PRIMARY KEY  (`ansprechpartner_typ_id`)
);

CREATE TABLE `stg_aufbaustudiengang` (
  `stg_range_id` int(11) NOT NULL,
  `aufbau_stg_profil_id` int(11) NOT NULL,
  `range_typ` varchar(45) default NULL,
  PRIMARY KEY  (`stg_range_id`,`aufbau_stg_profil_id`)
);

CREATE TABLE `stg_bereiche` (
  `bereichs_id` int(11) NOT NULL auto_increment,
  `bereich_name` varchar(200) default NULL,
  `sichtbar_fsb` tinyint(1) default NULL,
  `sichtbar_pamt` tinyint(1) default NULL,
  `sichtbar_iamt` tinyint(1) default NULL,
  `sichtbar_stuko` tinyint(1) default NULL,
  `sichtbar_stab` tinyint(1) default NULL,
  `oeffentlich` tinyint(1) default NULL,
  PRIMARY KEY  (`bereichs_id`)
);

CREATE TABLE `stg_bewerben` (
  `stg_profil_id` int(11) NOT NULL,
  `startzeit_wise` timestamp NULL default NULL,
  `endzeit_wise` timestamp NULL default NULL,
  `startzeit_sose` timestamp NULL default NULL,
  `endzeit_sose` timestamp NULL default NULL,
  `begin_wise` tinyint(1) default NULL,
  `begin_sose` tinyint(1) default NULL,
  `de_eu` enum('de','eu') NOT NULL default 'de',
  `erst_hoeher` enum('erst','hoeher') NOT NULL default 'erst',
  PRIMARY KEY  USING BTREE (`stg_profil_id`,`de_eu`,`erst_hoeher`)
);

CREATE TABLE `stg_doku_typ_bereich_zuord` (
  `stg_doku_typ_id` int(11) NOT NULL,
  `stg_bereichs_id` int(11) NOT NULL,
  PRIMARY KEY  (`stg_doku_typ_id`,`stg_bereichs_id`)
);

CREATE TABLE `stg_doku_zuord` (
  `doku_zuord_id` int(11) NOT NULL auto_increment,
  `doku_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  `position` smallint(5) default NULL,
  PRIMARY KEY  (`doku_zuord_id`)
);

CREATE TABLE `stg_dokument_tags` (
  `doku_id` int(11) NOT NULL,
  `tag` varchar(64) NOT NULL
);

CREATE TABLE `stg_dokument_typ` (
  `doku_typ_id` int(11) NOT NULL auto_increment,
  `name` varchar(200) default NULL,
  PRIMARY KEY  (`doku_typ_id`)
);

CREATE TABLE `stg_dokumente` (
  `doku_id` int(11) NOT NULL auto_increment,
  `user_id` char(32) default NULL,
  `name` varchar(400) default NULL,
  `quick_link` varchar(255) default NULL,
  `filename` varchar(400) default NULL,
  `mkdate` timestamp NULL default NULL,
  `chdate` timestamp NULL default NULL,
  `filesize` int(11) default NULL,
  `doku_typ_id` int(11) default NULL,
  `sichtbar` tinyint(1) default NULL,
  `jahr` smallint(4) default NULL,
  `version` varchar(128) default NULL,
  PRIMARY KEY  (`doku_id`)
);

CREATE TABLE `stg_fach_kombination` (
  `fach_kombi_id` int(11) NOT NULL AUTO_INCREMENT,
  `stg_profil_id` int(11) NOT NULL,
  `kombi_stg_profil_id` int(11) NOT NULL,
  `beschreibung` text,
  PRIMARY KEY  (`fach_kombi_id`)
);

CREATE TABLE `stg_fach_master_kombi` (
  `kombi_id` int(11) NOT NULL auto_increment,
  `studiengang_id` char(32) NOT NULL,
  `abschluss_id` char(32) NOT NULL,
  PRIMARY KEY  (`kombi_id`)
);

CREATE TABLE `stg_fsb_rollen` (
  `user_id` char(32) NOT NULL,
  `studiengang_id` char(32) NOT NULL,
  `lehreinheit_id` char(32) default NULL,
  `rollen_typ` enum('FSB','StuKo') default NULL,
  PRIMARY KEY  (`user_id`,`studiengang_id`)
);

CREATE TABLE `stg_profil` (
  `profil_id` int(11) NOT NULL auto_increment,
  `fach_id` char(32) default NULL,
  `abschluss_id` char(32) default NULL,
  `sichtbar` tinyint(1) default NULL,
  `studiendauer` tinyint(3) default NULL,
  `studienplaetze` smallint(5) default NULL,
  `zulassungsvoraussetzung_wise` enum('ja','nein','voraussichtlich ja','voraussichtlich nein') default NULL,
  `ausland` text,
  `zulassungsvoraussetzung_sose` enum('ja','nein','voraussichtlich ja','voraussichtlich nein') default NULL,
  `sem_tree_id` char(32) default NULL,
  `besonderezulassungsvoraussetzung_sose` enum('ja','nein','voraussichtlich ja','voraussichtlich nein') NOT NULL,
  `besonderezulassungsvoraussetzung_wise` enum('ja','nein','voraussichtlich ja','voraussichtlich nein') NOT NULL,
  `zielvereinbarung` text NOT NULL,
  `einleitungstext` text NOT NULL,
  `status` enum('discontinued','current','planned') NOT NULL DEFAULT 'current',
  `lehrsprache` enum('de','de/en','en') DEFAULT 'de',
  PRIMARY KEY  (`profil_id`)
);

CREATE TABLE `stg_profil_information` (
  `information_id` int(11) NOT NULL auto_increment,
  `stg_profil_id` int(11) NOT NULL,
  `info_form` enum('kurz','lang') default NULL,
  `sprache` enum('deutsch','englisch') default NULL,
  `einleitung` text,
  `profil` text,
  `inhalte` text,
  `lernformen` text,
  `gruende` text,
  `berufsfelder` text,
  `weitere_infos` text,
  `aktuelles` text,
  `einschreibungsverfahren` text,
  `bewerbungsverfahren` text,
  `besonderezugangsvoraussetzungen` text,
  `schwerpunkte` text,
  `sprachkenntnisse` text,
  `sichtbar` tinyint(1) default NULL,
  `vollstaendig` tinyint(1) default NULL,
  PRIMARY KEY  (`information_id`)
);

CREATE TABLE `stg_typ` (
  `stg_typ_id` int(11) NOT NULL auto_increment,
  `typ_name` varchar(100) default NULL COMMENT 'Typen: FB, ZWB, Weiterbildener, OnlineStg, Kostenpflichtig',
  PRIMARY KEY  (`stg_typ_id`)
);

CREATE TABLE `stg_typ_zuordnung` (
  `stg_typ_id` int(11) NOT NULL,
  `stg_profil_id` int(11) NOT NULL,
  PRIMARY KEY  (`stg_profil_id`,`stg_typ_id`)
);

CREATE TABLE `stg_verlauf_typ` (
  `verlauf_typ_id` int(11) NOT NULL auto_increment,
  `farbcode` varchar(16) default NULL,
  `typ_name` varchar(200) default NULL,
  PRIMARY KEY  (`verlauf_typ_id`)
);

CREATE TABLE `stg_verlaufsplan` (
  `verlaufsplan_id` int(11) NOT NULL auto_increment,
  `stg_profil_id` int(11) NOT NULL,
  `version` smallint(4) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `untertitel` varchar(255) default NULL,
  `notiz` text,
  `fach_kombi_id` int(11) default NULL,
  `sichtbar_fach1` tinyint(1) default NULL,
  `sichtbar_fach2` tinyint(1) default NULL,
  `user_id` char(32) default NULL,
  PRIMARY KEY  (`verlaufsplan_id`)
);

CREATE TABLE `stg_verlaufsplan_eintraege` (
  `stg_verlaufsplan_id` int(11) NOT NULL,
  `fachsem` tinyint(3) NOT NULL,
  `position` tinyint(3) NOT NULL,
  `position_hoehe` tinyint(3) default NULL,
  `sem_tree_id` char(32) default NULL,
  `verlauf_typ_id` int(11) default NULL,
  `modul_notiz` text,
  `kp` int(11) NOT NULL,
  PRIMARY KEY (`stg_verlaufsplan_id`,`fachsem`,`position`,`sem_tree_id`)
);

CREATE TABLE `stg_verlaufsplan_kp` (
  `verlaufsplan_id` int(11) NOT NULL,
  `fachsem` tinyint(3) NOT NULL,
  `kp` int(11) default NULL,
  PRIMARY KEY  (`verlaufsplan_id`,`fachsem`)
);
