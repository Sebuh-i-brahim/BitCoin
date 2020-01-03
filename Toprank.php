
<?php

include_once "globals.php";

include_once "CoinException.php";

include_once "Info.php";

include_once "function.php";

include_once "CoinDB.php";

//$time_pre = microtime(true);

// ARCH_TRUE or ARCH_FALSE

$db = CoinDB::Connect(CoinDB::ARCH_TRUE);

if ($db->inActiveProsess()) {
	new Info("Daily Updating in progress so exit from TopRank's Updating");
	exit();
}

$db->updatePop();

// var_dump([
// 	"command_time" => microtime(true) - $time_pre
// ]);
