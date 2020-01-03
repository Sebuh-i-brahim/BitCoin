<?php

$GLOBALS["api"] = array(
	"ping" => "https://api.coingecko.com/api/v3/ping",
	"simple" => array(
		"price" => "https://api.coingecko.com/api/v3/simple/price",
		"token_price/id" => "https://api.coingecko.com/api/v3/simple/token_price/{id}",
		"supported_vs_currencies" => "https://api.coingecko.com/api/v3/simple/supported_vs_currencies"
	),
	"coins" => array(
		"/" => null,
		"list" => "https://api.coingecko.com/api/v3/coins/list",
		"markets" => "https://api.coingecko.com/api/v3/coins/markets",
		"id" => array( 
			"/" => "https://api.coingecko.com/api/v3/coins/{id}",
			"tickers" => "https://api.coingecko.com/api/v3/coins/{id}/tickers",
			"history" => "https://api.coingecko.com/api/v3/coins/{id}/history",
			"market_chart" => array(
				"/" => "https://api.coingecko.com/api/v3/coins/{id}/market_chart",
				"range" => "https://api.coingecko.com/api/v3/coins/{id}/market_chart/range"
			),
			"status_updates" => "https://api.coingecko.com/api/v3/coins/{id}/status_updates",
			"contract" => array(
				"contract_adress" => array(
					"/" => "https://api.coingecko.com/api/v3/coins/{id}/contract/{contract_adress}",
					"market_chart" => array(
						"/" => "https://api.coingecko.com/api/v3/coins/{id}/contract/{contract_adress}/market_chart",
						"range" => "https://api.coingecko.com/api/v3/coins/{id}/contract/{contract_adress}/market_chart/range"
					)
				)
			)
		),
	),
	"exchanges" => array(
		"/" => "https://api.coingecko.com/api/v3/exchanges",
		"list" => "https://api.coingecko.com/api/v3/exchanges/list",
		"id" => array(
			"/" => "https://api.coingecko.com/api/v3/exchanges/{id}",
			"tickers" => "https://api.coingecko.com/api/v3/exchanges/{id}/tickers",
			"status_updates" => "https://api.coingecko.com/api/v3/exchanges/{id}/status_updates",
			"volume_chart" => "https://api.coingecko.com/api/v3/exchanges/{id}/volume_chart"
		)
	),
	"finance_platforms" => "https://api.coingecko.com/api/v3/finance_platforms",
	"finance_products" => "https://api.coingecko.com/api/v3/finance_products",
	"derivatives" => "https://api.coingecko.com/api/v3/derivatives",
	"status_updates" => "https://api.coingecko.com/api/v3/status_updates",
	"events" => array(
		"/" => "https://api.coingecko.com/api/v3/events",
		"countries" => "https://api.coingecko.com/api/v3/events/countries",
		"types" => "https://api.coingecko.com/api/v3/events/types"
	),
	"exchange_rates" => "https://api.coingecko.com/api/v3/exchange_rates",
	"global" => "https://api.coingecko.com/api/v3/global",
);