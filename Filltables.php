<?php

include_once "globals.php";

include_once "CoinException.php";

include_once "Info.php";

include_once "function.php";

include_once "CoinDB.php";

$db = CoinDB::Connect();

if ($db->isNull()) {

	set_time_limit(5400);
	
	$_data = json_decode(get_contents(get_coins(["page" => "list"], $api))['content'], true);

	$get_coins_data = [];
	
	$y = 0;

	$page_data_count = $GLOBALS['page_data_count'];

	foreach ($_data as $field) {
		$field['url'] = get_coins(array('id' => $field['id']), $api);
		$get_coins_data[intval($y/$page_data_count)][] = $field;
		$y++;
	} 

	$db->transaction();
	$xy=0;

	$count_coins_data = count($get_coins_data);

	foreach ($get_coins_data as $coin_data) {

		$all_data = get_coins_data($coin_data);

		$all_data_list = $all_data["list_data"];

		$all_data_coins = $all_data["coins_data"];

		$sql_list = create_sql([
			"table" => "coins_list",
			"method" => "insert",
			"column" => array("coin_id", "name", "symbol", "url", "market_rank", "img_thumb", "img_small", "img_large"),
			"count" =>  count($all_data_list)
		]);

		$sql_data = create_sql([
			"table" => "coins_data",
			"method" => "insert",
			"column" => array('coin_id', "market_cap", "price", "volume", "circulating_supply", "change_24"),
			"count" => count($all_data_coins)
		]);

		if (!empty($all_data_list)) {
			try {
				$db->query($sql_list, $all_data_list, true);	
			    $db->query($sql_data, $all_data_coins, true);
			} catch (Exception $e) {
				new CoinException("Error ::: when inserting Data ::: ".$e->getMessage());
			}
		}

		if ($xy != $count_coins_data - 1 && is_int($xy/5)) {
			usleep(1);
		}
		$xy++;
	}

	$db->query("UPDATE coins_tables SET is_null = ?, update_time = ?", [
		"is_null" 		=> 	0,
		"update_time" 	=> 	time()
	]);

	$db->commit();
}