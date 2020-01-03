<?php

function create_link($get, $data = array(), $id = null, $contract_adress = null)
{	
	$links = explode("/", $get);
	$x = 0;
	$array = array();
	foreach ($links as $link) {
		if ($x == 0) {
			$array = $data[$link]; 
		}else{
			$array = $array[$link];
		}
		if ($x == count($links) - 1) {
			if (is_array($array)) {
				$array = $array['/'];
			}
		}
		$x++;
	}
	if ($id) {
		$array = str_replace("{id}", $id, $array);
	}
	if ($contract_adress) {
		$array = str_replace("{contract_adress}", $contract_adress, $array);
	}
	return $array;
}

function get_coins($data = array())
{	
	$get = "coins";
	$id = (isset($data['id']))? $data['id'] : null;
	$contract_adress = (isset($data['contract_adress']))? $data['contract_adress'] : null;
	if ($id) {
		$get .= "/id";
		if ($contract_adress) {
			$contract_adress = $data['contract_adress'];
			$get .= "/contract/contract_adress"; 
		}
	}
	$get .= (isset($data['page'])? "/".$data['page'] : null);

	return create_link($get, $GLOBALS["api"], $id, $contract_adress);	
}

function get_coins_data($data ,$call_back_me = 0)
{
	$coins = array();
	$urls = (is_array($data))? ((is_array($data[0]))? array_column($data, "url"): $data) : $data;
	$urls2 = [];	
	$fields = get_contents($urls);
	$coins_list = [];
	$coins_data = [];
	if(is_array($fields['content'])){
		$zx = 0;
		foreach ($fields['content'] as $field) {
			$dat = json_decode($field, true);
			if (isset($dat['id'])) {
				$coins_list[] = [
					"coin_id" => $dat['id'],
					"name" => $dat['name'],
					"symbol" => $dat['symbol'],
					"url" => $urls[$zx],
					"market_rank" => (isset($dat["market_data"]["market_cap_rank"]))? $dat["market_data"]["market_cap_rank"] : 0,
					"img_thumb" => $dat['image']['thumb'],
					"img_small" => $dat['image']['small'],
					"img_large" => $dat['image']['large']
				];
				$coins_data[] = [
					"coin_id" => $dat['id'],
					"market_cap" => (isset($dat['market_data']['market_cap']['usd']))? $dat['market_data']['market_cap']['usd'] : 0,
					"price" => (isset($dat['market_data']['current_price']['usd']))? $dat['market_data']['current_price']['usd'] : 0,
					"volume" => (isset($dat['market_data']['total_volume']['usd']))? $dat['market_data']['total_volume']['usd'] : 0,
					"circulating_supply" => (isset($dat['market_data']['circulating_supply']))? $dat['market_data']['circulating_supply'] : 0,
					"change_24" => (isset($dat['market_data']['price_change_percentage_24h']))? $dat['market_data']['price_change_percentage_24h'] : 0
				];
			}else{
				$urls2[] = $urls[$zx];
			}
			$zx++;
		}
	}else{
		$dat = json_decode($fields['content'], true);
		if (isset($dat['id'])) {
			$coins_list[] = [
				"coin_id" => $dat['id'],
				"name" => $dat['name'],
				"symbol" => $dat['symbol'],
				"url" => $urls[$zx],
				"market_rank" => $dat["market_cap_rank"],
				"img_thumb" => $dat['image']['thumb'],
				"img_small" => $dat['image']['small'],
				"img_large" => $dat['image']['large']
			];
			$coins_data[] = [
				"coin_id" => $dat['id'],
				"market_cap" => (isset($dat['market_data']['market_cap']['usd']))? $dat['market_data']['market_cap']['usd'] : 0,
				"price" => (isset($dat['market_data']['current_price']['usd']))? $dat['market_data']['current_price']['usd'] : 0,
				"volume" => (isset($dat['market_data']['total_volume']['usd']))? $dat['market_data']['total_volume']['usd'] : 0,
				"circulating_supply" => (isset($dat['market_data']['circulating_supply']))? $dat['market_data']['circulating_supply'] : 0,
				"change_24" => (isset($dat['market_data']['price_change_percentage_24h']))? $dat['market_data']['price_change_percentage_24h'] : 0
			];
		}else{
			$urls2 = $urls;
		}
	}
	$empty_urls = [];

	if (!empty($urls2) && $call_back_me < 5) {
		$call_back_me++;
		$empty_data = get_coins_data($urls2, $call_back_me);
		$coins_list = array_merge($coins_list, $empty_data["list_data"]);
		$coins_data = array_merge($coins_data, $empty_data["coins_data"]);
		$empty_urls = $empty_data["empty_urls"];
	}

	if ($call_back_me == 5 && !empty($urls2)) {
		if (is_array($urls2)) {
			foreach ($urls2 as $url) {
				new CoinException("Get contents from {$url} Failed!");
			}
			$empty_urls = $urls2;
		}else{
			new CoinException("Get contents from {$urls2} Failed!");
			$empty_urls[] = $urls2;
		}
	}

	return [
		"list_data" => $coins_list,
		"coins_data" => $coins_data,
		"empty_urls" => $empty_urls
	];
}

function get_contents($url = null)
{	
	if (!$url) {
		return false;
	}

    $options = array(
        CURLOPT_CUSTOMREQUEST  => "GET",      
        CURLOPT_POST           => false,       
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0', 
        CURLOPT_RETURNTRANSFER => true,     
        CURLOPT_HEADER         => 0,
        CURLOPT_HTTPHEADER     => array("Content-Type: application/json"),
        CURLOPT_FOLLOWLOCATION => true,    
        CURLOPT_ENCODING       => "",      
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_VERBOSE        => 1,          
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_CONNECTTIMEOUT => 120,
		CURLOPT_TIMEOUT        => 120,
		CURLOPT_TCP_FASTOPEN   => 1,
		CURLOPT_FAILONERROR    => true
    );
    if (is_array($url)) {
    	$curl = [];
    	$result = [];
    	$error = [];
    	$http = [];
    	$error_http = [];
    	$mch = curl_multi_init();
    	$z = 0;
    	foreach ($url as $one_url) {
    		$curl[$z] = curl_init();
		    curl_setopt_array($curl[$z], $options);
		    curl_setopt($curl[$z], CURLOPT_URL, $one_url);
		    curl_multi_add_handle($mch, $curl[$z]);
		    $z++;
    	}
    	$active = null;
		do {
		  $mrc = curl_multi_exec($mch, $active);
		  usleep(1);
		} while($active > 0 && $mrc === CURLM_OK);

	    $zl = 0;
		foreach($curl as $c) {
			$result[] = curl_multi_getcontent($c);
		    curl_multi_remove_handle($mch, $c);
		    $zl++;
		}

		curl_multi_close($mch);

		return [
			"content" 	=> 	$result,
		];
    }else{
    	$ch      = curl_init();
	    curl_setopt_array($ch, $options);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    $content = curl_exec( $ch );
	    $error = curl_error($ch);
	    $errno = curl_errno($ch);
	    if ($error != "") {
	    	new CoinException("Error: {$error} ErrNo = {$errno}");
	    }
	    curl_close( $ch );

	    return [
	    	'content' => $content
	    ];
    }
}

function pgnt($pgnt, $numb, $is_redirect = false, $redirect = null)
{	
	$pgnt = intval(abs($pgnt));
	if ($pgnt > $numb) {
		if ($is_redirect) {
			header("HTTP/1.0 404 Not Found");
			if ($redirect) {
				include $redirect;
			}else{
				$not_found = <<< ABC
				<div style="width: 98vw; height: auto">
					<h1 style="width: 500px; height: auto; margin: 30vh auto; text-align: center; color: #B8BAC0; font-style: bold; font-weight: 700; font-size: 4rem;"><b style="font-size: 4.5rem; font-weight: 900; color: #ADAFB2;">404</b><br>NOT FOUND!</h1>
				</div> 
				ABC;
				echo $not_found;
			}
			exit();
		}
		return $numb;
	}
	return $pgnt;
}
function create_sql($sql_data = array())
{
	$sql = "";
	$table = (isset($sql_data['table']))? $sql_data['table'] : null;
	$col = (isset($sql_data['column']))? $sql_data['column'] : "*";
	$where = (isset($sql_data['where']))? $sql_data['where']: null;
	$where = sql_where($where);
	$join = (isset($sql_data['join']))? $sql_data['join']: null;
	switch ($sql_data['method']) {
		case 'insert':
			$sql .= "INSERT INTO {$sql_data['table']} (`".implode("`,`", $col)."`) VALUES ";
			for ($i=0; $i < $sql_data['count']; $i++) {
				$sql .= "(";
				$col_count = count($sql_data['column']);
				for ($k=0; $k < $col_count; $k++) { 
					if ($k == $col_count - 1) {
						$sql .= "?";
					}
					else{
						$sql .= "?,";
					}
				}
				if ($i == $sql_data['count'] - 1) {
					$sql .= ")";
				}else{
					$sql .= "),";
				}
			}
			break;
		case 'select':
			$col = (is_array($col))? implode(", ", $col) : $col;
			$sql .= "SELECT {$col} FROM {$table} ";

			$sql .= $where;

			break;

		default:
			
			break;
	}
	return $sql;
}

function sql_where($where = null)
{	
	$whr = "";
	if ($where) {
		$whr .= "WHERE ";
		if (is_array($where)) {
			foreach ($where as $val) {
				$whr .= $val." ";
			}
		}else{
			$whr .= $where;
		}
	}
	return $whr;
}

function urltoID($urls = array())
{
	$return = [];
	foreach ($urls as $url) {
		$return[] = substr($url, 39);
	}
	return $return;
}