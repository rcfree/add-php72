alter table `dictionary_item`
add column `weight` int default null;
update `version` set `version`='2.1.0';