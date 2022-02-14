<?php
header('Content-Type: application/json');
require_once('config.php');
require_once('src/php/class.API.php');

$params = $_GET;

if (!array_key_exists(SYMBOL, $params)) {
	echo "wrong params";
	return false;
}

$raw_data = PriceFeed::GetRawData($params[SYMBOL]);
$preformatted_data = PriceFeed::EvaluatePriceFeed($raw_data);

$data_feed = array();
foreach ($preformatted_data as $preformatted_day) {
	$data_feed[] = PriceFeed::getHighLowFromDate($preformatted_day);
}

//print_r($data_feed);
API::Output($data_feed);

class PriceFeed
{
	static function GetRawData($Token_pair): array
	{
		$raw_data = array();
		$next_id = false;
		$rounds = 0;
		while ($rounds < NUMBER_OF_CURL_REQUEST) {
			$url = str_replace(TOKENPAIR, $Token_pair, ORACLE_PRICE_FEED);
			if ($next_id) {
				$url = $url . NEXT . $next_id;
			}
			$curl_data = API::Curl_request($url);
			$raw_data = array_merge($raw_data, $curl_data['data']);
			//$price_feed_Array = array_merge($price_feed_Array, self::EvaluatePriceFeed($curl_data['data']));
			if (!key_exists('page', $curl_data)) break;
			$next_id = $curl_data['page']['next'];
			$rounds++;
		}
		return $raw_data;
	}

	static function EvaluatePriceFeed($curl_data): array
	{
		$number = 1;
		$data = array();
		foreach ($curl_data as $dataset) {

			$raw_date = date(RAWDATEFORMAT, $dataset['block'][TIME]);
			$date = date(DATEFORMAT, $dataset['block'][TIME]);
			$time = date(TIMEFORMAT, $dataset['block'][TIME]);
			$price = $dataset['aggregated']['amount'];

			$data[$raw_date][] = ['date' => $date, TIME => $time, PRICE => $price];
			$number++;
		}
		return $data;
	}

	static function getHighLowFromDate($container): array
	{
		$date = $container[0]['date'];
		$low = false;
		$high = false;
		$start = null;
		$start_time = false;
		$end_time = null;
		$end = null;
		foreach ($container as $dataset) {
			if ($dataset[PRICE] < $low || !$low) $low = $dataset[PRICE];
			if ($dataset[PRICE] > $high || !$high) $high = $dataset[PRICE];

			if (!$start_time || date($dataset[TIME]) < date($start_time)) {
				$start_time = $dataset[TIME];
				$start = $dataset[PRICE];
			}

			if (!$end_time || date($dataset[TIME]) > date($end_time)) {
				$end_time = $dataset[TIME];
				$end = $dataset[PRICE];
			}
		}

		/*$container['low'] = $low;
		$container['high'] = $high;
		$container['start'] = $start;
		$container['end'] = $end;
		$container['date'] = $container[0]['date'];*/

		return ['low' => $low,
			'high' => $high,
			'start' => $start,
			'end' => $end,
			'date' => $date];
	}
}