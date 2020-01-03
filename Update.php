<?php

include_once "globals.php";

include_once "CoinException.php";

include_once "Info.php";

include_once "function.php";

include_once "CoinDB.php";

// ARCH_TRUE or ARCH_FALSE

$db = CoinDB::Connect(CoinDB::ARCH_TRUE);

//$time_pre = microtime(true);
if ($db->inActiveProsess()) {
	new Info("Top Rank is in updating so sleeping afew minutes...");
	$db->updateSleep();
}

$db->update();

//print_r(microtime(true) - $time_pre);