CREATE DATABASE IF NOT EXISTS alph;
USE alph;

CREATE TABLE `ACCOUNT` (
	`idaccount` int NOT NULL AUTO_INCREMENT,
	`status` bit NOT NULL,
	`email` varchar(254) NOT NULL UNIQUE,
	`username` varchar(36) NOT NULL UNIQUE,
	`password` varchar(60) NOT NULL,
	`createddate` DATETIME NOT NULL,
	`editeddate` DATETIME,
	PRIMARY KEY (`idaccount`)
);

CREATE TABLE `TERMINAL_DIRECTORY` (
	`iddir` int NOT NULL,
	`terminal` char(17) NOT NULL,
	`parent` int,
	`name` varchar(255) NOT NULL,
	`chmod` int(3) NOT NULL,
	`createddate` DATETIME NOT NULL,
	`editeddate` DATETIME,
	PRIMARY KEY (`iddir`)
);

CREATE TABLE `TERMINAL_FILE` (
	`idfile` int NOT NULL AUTO_INCREMENT,
	`terminal` char(17) NOT NULL,
	`parentdir` int,
	`name` varchar(255),
	`extension` varchar(255) NOT NULL,
	`data` TEXT,
	`chmod` int(3) NOT NULL,
	`createddate` DATETIME NOT NULL,
	`editeddate` DATETIME,
	PRIMARY KEY (`idfile`)
);

CREATE TABLE `TERMINAL` (
	`mac` char(17) NOT NULL,
	`account` int NOT NULL,
	`localnetwork` char(17),
	PRIMARY KEY (`mac`)
);

CREATE TABLE `NETWORK` (
	`mac` char(17) NOT NULL,
	`ipv4` varchar(15) NOT NULL UNIQUE,
	`ipv6` varchar(45) NOT NULL UNIQUE,
	PRIMARY KEY (`mac`)
);

CREATE TABLE `PORT` (
	`idport` int NOT NULL AUTO_INCREMENT,
	`network` char(17) NOT NULL,
	`port` int NOT NULL,
	`status` int NOT NULL,
	`ip` varchar(15) NOT NULL,
	`ipport` int NOT NULL,
	PRIMARY KEY (`idport`)
);

CREATE TABLE `PRIVATEIP` (
	`network` char(17) NOT NULL,
	`terminal` char(17) NOT NULL,
	`ip` varchar(15) NOT NULL,
	PRIMARY KEY (`network`,`terminal`)
);

CREATE TABLE `TERMINAL_USER` (
	`idterminal_user` int NOT NULL AUTO_INCREMENT,
	`terminal` char(17) NOT NULL,
	`status` bit NOT NULL,
	`username` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	PRIMARY KEY (`idterminal_user`)
);

CREATE TABLE `TERMINAL_GROUP` (
	`idterminal_group` int NOT NULL AUTO_INCREMENT,
	`terminal` char(17) NOT NULL,
	`status` bit NOT NULL,
	`groupname` varchar(255) NOT NULL,
	PRIMARY KEY (`idterminal_group`)
);

CREATE TABLE `TERMINAL_GROUP_LINK` (
	`terminal_user` int NOT NULL,
	`terminal_group` int NOT NULL,
	PRIMARY KEY (`terminal_user`,`terminal_group`)
);

CREATE TABLE `SESSION` (
	`id` varchar(32) NOT NULL,
	`access` int(10),
	`data` TEXT NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `TERMINAL_USER_HISTORY` (
	`idhistory` int NOT NULL AUTO_INCREMENT,
	`terminal_user` int NOT NULL,
	`status` bit NOT NULL,
	`command` TEXT NOT NULL,
	`timestamp` TIME NOT NULL,
	PRIMARY KEY (`idhistory`)
);

CREATE TABLE `ACCOUNT_VALIDATION` (
	`idaccount` int NOT NULL,
	`code` char(100) NOT NULL,
	PRIMARY KEY (`idaccount`)
);

CREATE TABLE `CONFIG` (
	`key` varchar(255) NOT NULL,
	`value` varchar(255) NOT NULL,
	PRIMARY KEY (`key`)
);

ALTER TABLE `TERMINAL_DIRECTORY` ADD CONSTRAINT `TERMINAL_DIRECTORY_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_DIRECTORY` ADD CONSTRAINT `TERMINAL_DIRECTORY_fk1` FOREIGN KEY (`parent`) REFERENCES `TERMINAL_DIRECTORY`(`iddir`);

ALTER TABLE `TERMINAL_FILE` ADD CONSTRAINT `TERMINAL_FILE_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_FILE` ADD CONSTRAINT `TERMINAL_FILE_fk1` FOREIGN KEY (`parentdir`) REFERENCES `TERMINAL_DIRECTORY`(`iddir`);

ALTER TABLE `TERMINAL` ADD CONSTRAINT `TERMINAL_fk0` FOREIGN KEY (`account`) REFERENCES `ACCOUNT`(`idaccount`);

ALTER TABLE `TERMINAL` ADD CONSTRAINT `TERMINAL_fk1` FOREIGN KEY (`localnetwork`) REFERENCES `NETWORK`(`mac`);

ALTER TABLE `PORT` ADD CONSTRAINT `PORT_fk0` FOREIGN KEY (`network`) REFERENCES `NETWORK`(`mac`);

ALTER TABLE `PRIVATEIP` ADD CONSTRAINT `PRIVATEIP_fk0` FOREIGN KEY (`network`) REFERENCES `NETWORK`(`mac`);

ALTER TABLE `PRIVATEIP` ADD CONSTRAINT `PRIVATEIP_fk1` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_USER` ADD CONSTRAINT `TERMINAL_USER_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_GROUP` ADD CONSTRAINT `TERMINAL_GROUP_fk0` FOREIGN KEY (`terminal`) REFERENCES `TERMINAL`(`mac`);

ALTER TABLE `TERMINAL_GROUP_LINK` ADD CONSTRAINT `TERMINAL_GROUP_LINK_fk0` FOREIGN KEY (`terminal_user`) REFERENCES `TERMINAL_USER`(`idterminal_user`);

ALTER TABLE `TERMINAL_GROUP_LINK` ADD CONSTRAINT `TERMINAL_GROUP_LINK_fk1` FOREIGN KEY (`terminal_group`) REFERENCES `TERMINAL_GROUP`(`idterminal_group`);

ALTER TABLE `TERMINAL_USER_HISTORY` ADD CONSTRAINT `TERMINAL_USER_HISTORY_fk0` FOREIGN KEY (`terminal_user`) REFERENCES `TERMINAL_USER`(`idterminal_user`);

ALTER TABLE `ACCOUNT_VALIDATION` ADD CONSTRAINT `ACCOUNT_VALIDATION_fk0` FOREIGN KEY (`idaccount`) REFERENCES `ACCOUNT`(`idaccount`);

