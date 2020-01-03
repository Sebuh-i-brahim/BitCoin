<?php

include_once "api.php";

date_default_timezone_set('Asia/Baku');

$GLOBALS['coin_db'] = [
	"servername" 	=> 	"localhost",
	"username" 		=> 	"root",
	"password" 		=> 	"",
	"dbname" 		=> 	"coins"
];

$GLOBALS['page'] = "list";

// Link getirme ve database - e yazma 

$GLOBALS['page_data_count'] = 100;

// Sehifeleme

$GLOBALS['pagination'] = 100;

// x - gunden sonrani silme 

$GLOBALS['deleted_data_day_before'] = 7;

// axirinci yuklenen datanin max_last_date ve max_last_date - last_data_range_with_hours araligi

$GLOBALS['last_data_range_with_hours'] = 2;

// Exception caching

$GLOBALS['error_cache'] = true;

// Exception - larin yazildigi file

$GLOBALS["error_file_path"] = "error.txt";

// Info caching

$GLOBALS['info_cache'] = true;

// Infolarin yazildigi file 

$GLOBALS["info_file_path"] = "info.txt";


