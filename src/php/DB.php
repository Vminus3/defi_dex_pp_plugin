<?php


class DB
{
	private PDO $pdo;

	public function __construct()
	{
		try {
			$this->pdo = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
		}
	}

	/**
	 * @return PDO
	 */
	public function getPdo(): PDO
	{
		return $this->pdo;
	}

	public function newPriceFeed($tokenPair_ID, $priceAB, $priceBA, $commission, $totalLiquidity_token, $totalLiquidity_usd, $rewardPct, $apr_reward, $apr_total)
	{
		$data = [
			'token_pair_id' => $tokenPair_ID,
			'price_ab' => $priceAB,
			'price_ba' => $priceBA,
			'commission' => $commission,
			'totalLiquidity_token' => $totalLiquidity_token,
			'totalLiquidity_usd' => $totalLiquidity_usd,
			'rewardPct' => $rewardPct,
			'apr_reward' => $apr_reward,
			'apr_total' => $apr_total
		];

		try {
			$this->pdo->prepare(
				'Insert into 
                    pricefeed (
	                    tokenpair_fk,
	                    price_ab,
	                    price_ba,
	                    commission,
	                    totalLiquidity_token,
	                    totalLiquidity_usd,
	                    rewardPct,
	                    apr_reward,
	                    apr_total) 
                   VALUES (
                       :token_pair_id, 
                       :price_ab, 
                       :price_ba,
                       :commission, 
                       :totalLiquidity_token,
                       :totalLiquidity_usd,
                       :rewardPct,                       
                       :apr_reward,
                       :apr_total)')->execute($data);
		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
		}
	}

	/**
	 * @throws Exception
	 */
	public function getPairID($symbol): bool|int
	{
		try {
			$stm = $this->pdo->prepare('SELECT pair_id FROM tokenpair WHERE symbol=:symbol');
			$stm->execute(['symbol' => $symbol]);
			$row = $stm->fetch(PDO::FETCH_ASSOC);
			return $row ? $row['pair_id'] : false;
		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
			throw $exception;
		}
	}

	/**
	 * @throws Exception
	 */
	public function newPair($tokenA, $tokenB, $symbol, $displaySymbol, $api_id): bool
	{
		if ($this->getPairID($symbol)) throw new Exception(TOKEN_PAIR_ALREADY_EXIST);

		$DB_NEW_PAIR =
			'INSERT INTO 
                    tokenpair (symbol, displaySymbol, api_id, tokenA_fk, tokenB_fk) 
                VALUES (:symbol, :displaySymbol, :api_id, :tokenA_fk, :tokenB_fk)';

		try {
			$tokenA_ID = $this->newToken($tokenA);
			$tokenB_ID = $this->newToken($tokenB);

			$data = [
				'symbol' => $symbol,
				'displaySymbol' => $displaySymbol,
				'api_id' => $api_id,
				'tokenA_fk' => $tokenA_ID,
				'tokenB_fk' => $tokenB_ID
			];


			$this->pdo->prepare($DB_NEW_PAIR)->execute($data);
			return $this->pdo->lastInsertId();

		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
			// if( isset($tokenA_ID) ) $this->deleteID('token', $tokenA_ID);
			// if( isset($tokenB_ID) ) $this->deleteID('token', $tokenB_ID);
			throw $exception;
		}
	}

	public function newToken($token): bool|int|string
	{
		$data = [
			'symbol' => $token['symbol'],
			'displaySymbol' => $token['displaySymbol'],
			'api_id' => $token['id']
		];

		$DB_NEW_TOKEN = 'INSERT INTO token (symbol, displaySymbol, api_id) VALUES( :symbol, :displaySymbol, :api_id)';

		try {
			// check if token doesn't exist
			$db_token = $this->getToken(false, $token['symbol'], $token['id']);
			if ($db_token) return $db_token['token_id'];

			$this->pdo->prepare($DB_NEW_TOKEN)->execute($data);
			return $this->pdo->lastInsertId();
		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
			return false;
		}
	}

	/**
	 * @throws Exception
	 */
	public function getToken($Token_ID = false, $Symbol = false, $API_ID = false): bool|array
	{
		$data = [
			'tokenID' => $Token_ID,
			'symbol' => $Symbol,
			'api_id' => $API_ID
		];
		$DB_GET_TOKEN = 'SELECT * FROM token WHERE token_id=:tokenID || symbol=:symbol || api_id=:api_id';
		try {
			$stm = $this->pdo->prepare($DB_GET_TOKEN);
			$stm->execute($data);
			$row = $stm->fetch(PDO::FETCH_ASSOC);
			return $row ? $row : false;

		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
			return false;
		}
	}

	private function deleteID($tableName, $id): void
	{
		$data = [
			'row' => $this->getKeyFromTable($tableName),
			'id' => $id
		];
		$DB_DELETE_ID = 'DELETE FROM ' . $tableName . ' WHERE :row=:id';
		try {
			$this->pdo->prepare($DB_DELETE_ID)->execute($data);
		} catch (Exception $exception) {
			echo '<pre>';
			print_r($exception);
			echo '</pre>';
		}
	}

	private function getKeyFromTable($TableName)
	{
		$stm = $this->pdo->prepare("SHOW KEYS FROM " . $TableName . " WHERE Key_name = 'PRIMARY'");
		$stm->execute();
		return $stm->fetch(PDO::FETCH_ASSOC)['Column_name'];
	}

}