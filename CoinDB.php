<?php

class CoinDB
{
	private static $_connect = null;
	private $_pdo,
			$_query,
			$_error = false,
			$_results,
			$_count = 0,
			$_archiv = false;
	public const ARCH_TRUE = true;

	public const ARCH_FALSE = false;

	public function __construct($archive = false)
	{
		$this->_archiv = $archive;
		try {
			$server = $GLOBALS['coin_db']['servername'];
			$dbname = $GLOBALS['coin_db']['dbname'];
			$username = $GLOBALS['coin_db']['username'];
			$password = $GLOBALS['coin_db']['password'];
			$options = [
			    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			    PDO::ATTR_CASE => PDO::CASE_NATURAL
			];
			$this->_pdo = new PDO("mysql:host={$server};dbname={$dbname};charset=utf8",$username,$password, $options);
		} catch (PDOException $e) {
			new CoinException("Database Connection Error ::: ".$e->getMessage());
			die("Database Connection failed: ".$e->getMessage());
		}
	}
	public static function Connect($archive = false)
	{
		if (!isset(self::$_connect)) {
			self::$_connect = new CoinDB($archive);
		}
		return self::$_connect;
	}
	public function query($sql, $data = array(), $multi = false)
	{
		if ($this->_query = $this->_pdo->prepare($sql)) {
			if (!$multi) {
				$x = 1;
				if (count($data)) {
					foreach ($data as $fields) {
						if (!is_array($fields)) {
							$this->_query->bindValue($x, $fields);
							$x++;
						}
					}
				}
				if ($this->_query->execute()) {
				
					switch ($this->what_Method($sql)) {
						case 'select':
							$this->_results = $this->_query->fetchAll(PDO::FETCH_ASSOC);
							$this->_count = $this->_query->rowCount();
							break;
						case 'show':
							$this->_results = $this->_query->fetchAll(PDO::FETCH_ASSOC);
							break;
						default:
							$this->_count = $this->_query->rowCount();
							break;
					}
					$this->_error = false;
				}else{
					$this->_error = true;
				}
			}else{
				$insert_values = array();
				foreach ($data as $field) {
					$fields = array();
					foreach ($field as $value) {
						$fields[] = $value;
					}
					$insert_values = array_merge($insert_values, $fields);
				}
				if (!$this->_query->execute($insert_values)) {
					$this->_error = true;
				}
			}
		}
		return $this;
	}
	public function inActiveProsess()
	{	
		$prosess = $this->query("SHOW PROCESSLIST")
					   ->result();
		$active = array_filter($prosess, function($field){
              return ($field['db']== $GLOBALS['coin_db']['dbname'] && $field['Command']=='Sleep');
          });
		return (empty($active))? false : true;
	}
	public function updateSleep()
	{
		sleep(60);
		if ($this->inActiveProsess()) {
			$this->updateSleep();
		}
	}
	public function isUpdateable()
	{	
		$delete_all = ($this->_archiv)? false : true;
		$updateable = false;
		$last_date = $this->query("SELECT MAX(coins_data.date) AS max_date 
			FROM coins_list INNER JOIN coins_data ON coins_list.coin_id = coins_data.coin_id
			LIMIT 1")->first('max_date');
		$now = date("m-d", time());
		$lastdate = date("m-d", strtotime($last_date));

		if ($now == $lastdate) {
			$updateable = true;
		}

		return [
			"updateable" => $updateable,
			"lastdate"   => $last_date,
			"delete_all" => $delete_all
		];
	}

	public function updatePop($count = 300)
	{	
		$progress = $this->isUpdateable();
		$check = null;
		if ($progress["delete_all"]) {
			$check = true;
		}else{
			$check = $progress["updateable"];
		}

		if ($check) {
			set_time_limit(1800);
			$sql = "SELECT url FROM coins_list ORDER BY market_rank = 0, market_rank LIMIT {$count}";
			$urls = $this->query($sql)->result('url');

			$last_range = (isset($GLOBALS['last_data_range_with_hours']))? $GLOBALS['last_data_range_with_hours'] : 2;
			$date = date("Y-m-d H:i:s", strtotime($progress["lastdate"]) - $last_range*60*60);
			
			$page_data_count = $GLOBALS['page_data_count'];
			if ($count/$page_data_count > 1) {
				$get_urls = [];
				$ax=0;
				foreach ($urls as $url) {
					$get_urls[intval($ax/$page_data_count)][] = $url;
					$ax++;
				}
			}
			$sql2 = "UPDATE coins_data as a
					INNER JOIN coins_list as b
					ON a.coin_id = b.coin_id
					SET a.market_cap = ?, a.price = ?, a.volume = ?,
					a.circulating_supply = ?, a.change_24 = ?
					WHERE a.coin_id = ? AND a.date > '{$date}';";
			
			$this->transaction();
			$row_count = 0;
			foreach ($get_urls as $get_url) {
				$all_data = get_coins_data($get_url);
				foreach ($all_data["coins_data"] as $coin_data) {
					$this->clearCount();
					try{
						$this->query($sql2, [
							"market_cap" => $coin_data["market_cap"],
							"price" 	=> $coin_data["price"],
							"volume" 	=> $coin_data["volume"],
							"circulating_supply" => $coin_data["circulating_supply"],
							"change_24" => $coin_data["change_24"],
							"coin_id" 	=> $coin_data["coin_id"]
						]);
					}catch(Exception $e){
						new CoinException($e->getMessage());
					}
					$row_count += $this->count();
				}
			}
			if ($row_count > 0) {
				new Info("Success! {$row_count} rows updated");
			}
			$this->commit();
		}
	}

	public function update()
	{	

		$last_date_range = (isset($GLOBALS['last_data_range_with_hours']))? $GLOBALS['last_data_range_with_hours'] : 2;

		$progress = $this->isUpdateable();
		
		$check = null;
		
		$delete_all_data = false;
		
		if ($progress["delete_all"]) {
			$check = true;
			$delete_all_data = true;
		}else{
			$check = $progress["updateable"];
		}

		set_time_limit(5400);

		$sql = create_sql([
			"table" 	=> 	"coins_list",
			"method" 	=> 	"select",
			"column" 	=> 	"url"
		]);

		$urls = $this->query($sql)->result('url');

		$get_urls = [];
		$all_coins = [];
		$page_data_count = $GLOBALS['page_data_count'];
		
		$y=0;
		foreach ($urls as $url) {
			$get_urls[intval($y/$page_data_count)][] = $url;
			$y++;
		}

		$this->transaction();

		$xy=0;
		$count_urls = count($get_urls);
		$rows_count = 0;
		$empty_urls = [];
		foreach ($get_urls as $get_url) {

			$all_data = get_coins_data($get_url);

			$all_coins = $all_data["coins_data"];

			$empty_urls = array_merge($empty_urls, $all_data["empty_urls"]);

			$sql_data = create_sql([
				"table" => "coins_data",
				"method" => "insert",
				"column" => array('coin_id', "market_cap", "price", "volume", "circulating_supply", "change_24"),
				"count" => count($all_coins)
			]);

			if (!empty($all_coins)) {
				try{
				$this->query($sql_data, $all_coins, true);
				}catch(Exception $e){
					new CoinException($e->getMessage());
				}
			}

			if ($xy != $count_urls - 1) {
				usleep(1);
			}
			$xy++;
		}

		$insertedrowCount = $this->count();

		$this->copyLastData($empty_urls, $progress["lastdate"]);

		if ($insertedrowCount != $this->count()) {
			$insertedrowCount += $this->count();
		}

		new Info("Success! ".$insertedrowCount." row inserted to coins_data table");

		if ($check) {
			if ($delete_all_data) {
				try{
					$this->query("DELETE b FROM coins_data AS b
						INNER JOIN coins_list AS a
						ON b.coin_id = a.coin_id 
						WHERE b.date <= ?",[
						"date" 		=> $progress["lastdate"]
					]);
				}catch(Exception $e){
					new CoinException($e->getMessage());
				}

				if ($this->count() > 0) {
					new Info("Success! ".$this->count()." row deleted from coins_data table");
				}
			}else{

				$last_range = (isset($GLOBALS['last_data_range_with_hours']))? $GLOBALS['last_data_range_with_hours'] : 2;
				$appox_date = date('Y-m-d H:i:s', (strtotime($progress["lastdate"]) - $last_range*60*60));
				try{
					$this->query("DELETE b FROM coins_data AS b
							INNER JOIN coins_list AS a
							ON b.coin_id = a.coin_id 
							WHERE b.date <= ? AND b.date > ?",[
						"date" 		=> $progress["lastdate"],
						"bigger" 	=> $appox_date	
					]);
				}catch(Exception $e){
					new CoinException($e->getMessage());
				}
				if ($this->count() > 0) {
					new Info("Success! ".$this->count()." row deleted from coins_data table");
				}
			}
			
		}

		$this->finishUpdate($delete_all_data);

		$this->commit();
	}
	public function isNull($table_name = "coins_list")
	{
		$this->query("SELECT `is_null` FROM coins_tables WHERE table_name = ?",[
			"table_name" => "coins_list"
		]);
		return intval($this->first("is_null")); 
	}

	public function finishUpdate($delete_all = false)
	{
		if (!$delete_all) {
			$now = time();
			$this->deleteLastWeek($now)->query("UPDATE coins_tables SET update_time = ? ",[
				"update_time" => $now,
			]);
		}
	}

	private function deleteLastWeek($now)
	{	
		$table_name = "coins_data";
		$before = (isset($GLOBALS['deleted_data_day_before']))? $GLOBALS['deleted_data_day_before'] : 7;
		$date = date('Y-m-d', ($now - $before*24*60*60));
		if($this->query("SELECT coins_data.coin_id FROM coins_list INNER JOIN coins_data ON coins_list.coin_id = coins_data.coin_id 
			WHERE coins_data.date < ? LIMIT 1", ["date" => $date])->count() > 0){

			$sql = "DELETE b FROM coins_data AS b
					INNER JOIN coins_list AS a
					ON b.coin_id = a.coin_id 
					WHERE b.date < ?";

			try{
				$this->query( $sql, [
					"date" => $date
				]);
			}catch(Exception $e){
				new CoinException($e->getMessage());
			}

			if ($this->count() > 0) {
				new Info("Success! Deleted last week of ".$this->count()." rows From coins_data table");
			}
		}
		return $this;
	}
	public function copyLastData($empty_urls = array() , $lastdate = null)
	{	
		$last_range = (isset($GLOBALS['last_data_range_with_hours']))? $GLOBALS['last_data_range_with_hours'] : 2;

		if (!empty($empty_urls) && $lastdate) {
			$ids = urltoID($empty_urls);
			$date = date("Y-m-d H:i:s", strtotime($lastdate) - $last_range*60*60);
			$sql1 = "INSERT INTO coins_data (coin_id, market_cap, price, volume, circulating_supply, change_24)
					SELECT coin_id, market_cap, price, volume, circulating_supply, change_24 FROM coins_data
					WHERE (coin_id IN(";

			$count = count($empty_urls);
			for ($i=0; $i < $count; $i++) { 
				if ($i == $count - 1) {
					$sql1 .= "?)) AND date > '{$date}'";
				}else{
					$sql1 .= "?,";
				}
			}

			$this->query($sql1, $ids);
		}
		return $this;
	}
	public function transaction()
	{
		$this->_pdo->beginTransaction();
	}

	public function commit()
	{
		$this->_pdo->commit();
	}

	public function rollback()
	{
		$this->_pdo->rollback();
	}

	public function error()
	{
		return $this->_error;
	}

	public function result($column = null)
	{	
		if ($column) {
			return array_column($this->_results, $column);
		}
		return $this->_results; 
	}

	public function first($column = null)
	{
		return (empty($column))? $this->_results[0] : $this->_results[0][$column];
	}

	public function count()
	{
		return $this->_count;
	}

	public function clearCount()
	{
		$this->_count = 0;
	}
	public function lastID()
	{
		return $this->_pdo->lastInsertId();
	}

	public function db_null()
	{
		return null;
	}
	public function what_Method($sql)
	{
		if (strpos($sql, "INSERT") !== false) {
			return "insert";
		}
		if (strpos($sql, "SELECT") !== false) {
			return "select";
		}
		if (strpos($sql, "DELETE") !== false) {
			return "delete";
		}
		if (strpos($sql, "UPDATE") !== false) {
			return "update";
		}
		if (strpos($sql, "SHOW") !== false) {
			return "show";
		}
		return false;
	}
}