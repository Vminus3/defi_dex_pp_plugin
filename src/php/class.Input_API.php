<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/API/config.php');
require_once('DB.php');
require_once('class.API.php');


$I_API = new Input_API();
$Price_feed = $I_API->getPriceFeed();


/**
 * @property DB $DB
 */
class Input_API
{
	public function __construct()
	{
		$this->DB = new DB();
	}

	function getPriceFeed()
	{
		$curl_data = API::Curl_request(DEX_POOL_PAIRS);
		$curl_data = $curl_data['data'];
		//echo '<pre>'; print_r($curl_data); echo '</pre>';
		for ($t = 0; $t < count($curl_data); $t++) {
			$pair = &$curl_data[$t];
			$tokenA = &$pair['tokenA'];
			$tokenB = &$pair['tokenB'];
			$priceRation = &$pair['priceRatio'];
			$commission = &$pair['commission'];
			$totalLiquidity = &$pair['totalLiquidity'];
			$rewardPct = &$pair['rewardPct'];
			$apr = &$pair['apr'];

			try {
				$pair_ID = $this->DB->getPairID($pair['symbol']);
				if (!$pair_ID) {
					$pair_ID = $this->DB->newPair($tokenA, $tokenB, $pair['symbol'], $pair['displaySymbol'], $pair['id']);
				}

				$this->DB->newPriceFeed(
					$pair_ID,
					$priceRation['ab'],
					$priceRation['ba'],
					$commission,
					$totalLiquidity['token'],
					$totalLiquidity['usd'],
					$rewardPct,
					$apr['reward'],
					$apr['total']
				);


			} catch (Exception $e) {
				echo "Error: " . $e->getMessage();
			}
		}
	}
}