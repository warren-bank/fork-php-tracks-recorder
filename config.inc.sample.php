<?php
	
	//RENAME TO config.inc.php

	setlocale(LC_TIME, "fr_FR");


	$_config['sql_host'] = '';
	$_config['sql_user'] = '';
	$_config['sql_pass'] = '';
	$_config['sql_db'] = '';
	
	$_config['sql_prefix'] = '';
	
	$_config['default_accuracy'] = 1000;
	
	$_config['enable_geo_reverse'] = false;
	$_config['geo_reverse_lookup_url'] = "http://193.63.75.109/reverse?format=json&zoom=18&accept-language=fr&addressdetails=0&email=sjobs@apple.com&";

?>