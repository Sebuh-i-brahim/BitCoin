<?php

include_once "globals.php";

include_once "CoinException.php";

include_once "Info.php";

include_once "function.php";

include_once "CoinDB.php";

$time_pre = microtime(true);

$page = $GLOBALS['page']; 

$api = $GLOBALS["api"];

$pgnt = (isset($_GET['pgnt']))? ((is_numeric($_GET['pgnt']))? $_GET['pgnt'] : 1) : 1;

$db = CoinDB::Connect();


// var_dump([$db->inActiveProsess()]);

// die();


$pagination = $GLOBALS['pagination'];

$hour = $GLOBALS["last_data_range_with_hours"];

$last_date = $db->query("SELECT MAX(coins_data.date) AS max_date 
					FROM coins_list INNER JOIN coins_data ON coins_list.coin_id = coins_data.coin_id
					LIMIT 1")->first('max_date');

$date = date('Y-m-d H:i:s', (strtotime($last_date) - $hour*60*60));

$count = $db->query(
	"SELECT COUNT('coin_id') AS count FROM coins_data WHERE `date` > ?", 
	[ "date" => $date ]
)->first("count");

$pgnt = pgnt($pgnt, $count/$pagination, true); 

$sql = "SELECT * FROM coins_list AS a
		INNER JOIN coins_data AS b
		ON a.coin_id = b.coin_id 
		WHERE b.date > ?
		ORDER BY a.market_rank = 0, a.market_rank
		LIMIT ";

$begin = ($pgnt - 1) * 100;

if ($pgnt != 1) {
	$sql .= "{$begin},{$pagination};"; 	
}else{
	$sql .= "{$pagination};"; 
}

$all_coins = $db->query($sql,[
	"date"  => $date,
])->result();

$time_post = microtime(true);

var_dump([ "command_time" => ($time_post - $time_pre)]);

var_dump($all_coins);	
	
require_once "view.php";