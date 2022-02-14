<?php
header('Content-Type: application/json');
require_once('config.php');
require_once('src/class.API.php');

$params = $_GET;

if (!array_key_exists(SYMBOL, $params) && !array_key_exists(ID, $params)) {
	echo "wrong params";
	return false;
}

$params = array_merge($default, $params);
$data = PoolPair::GetPairDataJSON($params);
API::Output($data);

class PoolPair
{
	static function GetPairDataJSON($params)
	{
		$curl_data = API::Curl_request(DEX_POOL_PAIRS);
		$curl_data = $curl_data['data'];
		for ($t = 0; $t < count($curl_data); $t++) {
			$data = self::GetPair($params, $curl_data[$t]);
			if ($data != false) {
				$data['date'] = date('d.m.Y');
				return $data;
			}
		}
	}

	static function GetPair($params, $PairData)
	{
		if ($params[SYMBOL] == $PairData[SYMBOL] || $params[ID] == $PairData[ID]) {
			return $PairData;
		}
		return false;
	}
}
