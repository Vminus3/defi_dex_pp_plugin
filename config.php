<?php

const SYMBOL = 'symbol';
const ID = 'id';
const PRICE = 'price';
const TIME = 'time';

const TOKENPAIR = '{TOKENPAIR}';
const RAWDATEFORMAT = 'Ymd';
const DATEFORMAT = 'd.m.Y';
const TIMEFORMAT = 'H:i:s';
const NEXT = '&next=';

const RESULTS_PER_PAGE = 200;
const NUMBER_OF_CURL_REQUEST = 100;

const DEX_POOL_PAIRS = 'https://ocean.defichain.com/v0/mainnet/poolpairs';
const ORACLE_PRICE_FEED = 'https://ocean.defichain.com/v0/mainnet/prices/' . TOKENPAIR . '/feed?size=' . RESULTS_PER_PAGE;


// Exception
const TOKEN_PAIR_ALREADY_EXIST = 'token cannot be created because it already exists';

$default = array(
	'symbol' => '',
	'id' => ''
);

const ROOTPATH = __DIR__;

// DB connection
const DB_SERVER = 'localhost'; // Hostname
const DB_USER = 'root'; // username
const DB_PASS = ''; // password
const DB_NAME = 'defichain'; // Name der database



