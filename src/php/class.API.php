<?php

class API
{

	static function Output($data)
	{
		echo json_encode($data);
	}


	static function Curl_request($url)
	{
		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_URL, $url);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($curl_session);
		curl_close($curl_session);
		return json_decode($result, true);
	}


}