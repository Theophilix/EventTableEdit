DROP TABLE IF EXISTS `#__eventtableedit_details`;
CREATE TABLE IF NOT EXISTS `#__eventtableedit_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NULL,
  `alias` varchar(255) NULL,
  `user_id` int(11) NULL DEFAULT '0',
  `access` tinyint(3) NULL DEFAULT '1',
  `checked_out` int(10) NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NULL DEFAULT '*',
  `show_filter` tinyint(1) NULL DEFAULT '0',
  `show_first_row` tinyint(1) NULL DEFAULT '0',
  `show_print_view` tinyint(1) NULL DEFAULT '0',
  `show_pagination` tinyint(1) NULL DEFAULT '0',
  `bbcode` tinyint(1) NULL DEFAULT '0',
  `bbcode_img` tinyint(1) NULL DEFAULT '0',
  `pretext` mediumtext NULL,
  `aftertext` mediumtext NULL,
  `metakey` text NULL,
  `metadesc` text NULL,
  `metadata` text NULL,
  `edit_own_rows` tinyint(1) NULL DEFAULT '0',
  `dateformat` varchar(25) NULL DEFAULT '%d.%m.%Y',
  `timeformat` varchar(25) NULL DEFAULT '%H:%M',
  `cellspacing` tinyint(3) NULL DEFAULT '0',
  `cellpadding` tinyint(3) NULL DEFAULT '2',
  `tablecolor1` varchar(15) NULL DEFAULT 'CCCCCC',
  `tablecolor2` varchar(15) NULL DEFAULT 'FFFFFF',
  `float_separator` char(1) NULL DEFAULT ',',
  `link_target` varchar(15) NULL DEFAULT '_blank',
  `cellbreak` int(11) NULL DEFAULT '0',
  `pagebreak` int(11) NULL DEFAULT '100',
  `asset_id` int(10) NULL DEFAULT '0',
  `lft` int(11) NULL DEFAULT '0',
  `rgt` int(11) NULL DEFAULT '0',
  `published` tinyint(1) NULL DEFAULT '0',
  `normalorappointment` tinyint(1) NULL DEFAULT '0',
  `addtitle` tinyint(1) NULL DEFAULT '0',
  `location` varchar(255) NULL,
  `summary` text NULL,
  `email` varchar(255) NULL,
  `adminemailsubject` varchar(500) NULL,
  `useremailsubject` varchar(500) NULL,
  `useremailtext` text NULL,
  `adminemailtext` text NULL,
  `displayname` varchar(255) NULL,
  `icsfilename` varchar(255) NULL,
  `sorting` tinyint(1) NULL DEFAULT '0',
  `switcher` tinyint(1) NULL DEFAULT '0',
  `row` int(11) NULL,
  `col` int(11) NULL,
  `hours` int(11) NULL,
  `showdayname` tinyint(4) NULL,
  `rowsort` tinyint(4) NULL DEFAULT '0',
  `rowdelete` tinyint(4) NULL DEFAULT '0',
  `showusernametoadmin` tinyint(4) NULL,
  `showusernametouser` tinyint(4) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

ALTER TABLE `#__eventtableedit_details`
ADD `automate_sort` tinyint(4) NULL,
ADD `automate_sort_column` varchar(255) NULL AFTER `automate_sort`,
COMMENT='';

ALTER TABLE `#__eventtableedit_details`
ADD `scroll_table` varchar(255) COLLATE 'utf8_general_ci' NULL,
ADD `scroll_table_height` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `scroll_table`,
COMMENT='';

ALTER TABLE `#__eventtableedit_details`
ADD `add_option_list` tinyint(1) NULL,
ADD `corresptable` text COLLATE 'utf8_general_ci' NULL AFTER `add_option_list`,
ADD `show_selected_option_to_user` tinyint(1) NULL AFTER `corresptable`,
ADD `show_selected_option_to_admin` tinyint(1) NULL AFTER `show_selected_option_to_user`;

ALTER TABLE `#__eventtableedit_details`
ADD `standardlayout` varchar(255) NULL;

DROP TABLE IF EXISTS `#__eventtableedit_heads`;
CREATE TABLE `#__eventtableedit_heads` (
	`id` int(11) NOT NULL auto_increment,
	`table_id` int(11) NULL,
	`name` varchar(255) NULL,
	`datatype` varchar(25) NULL,
	`ordering` int(11) NULL,
	`defaultSorting` varchar(30) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

ALTER TABLE `#__eventtableedit_heads`
CHANGE `name` `name` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `table_id`,
CHANGE `datatype` `datatype` varchar(25) COLLATE 'utf8_general_ci' NULL AFTER `name`,
CHANGE `ordering` `ordering` int(11) NULL AFTER `datatype`,
CHANGE `defaultSorting` `defaultSorting` varchar(30) COLLATE 'utf8_general_ci' NULL AFTER `ordering`;


DROP TABLE IF EXISTS `#__eventtableedit_dropdowns`;
CREATE TABLE `#__eventtableedit_dropdowns` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) NULL,
	`published` tinyint(1) NULL DEFAULT 1,
	`checked_out` int(10) NULL,
	`checked_out_time` datetime NULL,
	`ordering` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__eventtableedit_dropdown`;
CREATE TABLE `#__eventtableedit_dropdown` (
	`id` int(11) NOT NULL auto_increment,
	`dropdown_id` int(11) NULL,
	`name` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
