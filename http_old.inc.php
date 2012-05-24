<?php

// S4X8 HTTP Library 1.1
// Marcos Vives Del Sol - 23/V/2012
// Licensed under a CC-BY-SA license

function my_http_parse_headers($headers) {
	$retVal = array();
	$fields = explode("\r\n", $headers);
	for ($i = 1; $i < count($fields); $i++) {
		if ($fields[$i] === "") break;

		$fieldName = strtolower(substr($fields[$i], 0, strpos($fields[$i], ":")));
		$fieldValue = substr($fields[$i], strpos($fields[$i], ":") + 2);
		
		if (isset($retVal[$fieldName])) {
			if (is_array($retVal[$fieldName])) {
				$retVal[$fieldName][] = $fieldValue;
			} else {
				$retVal[$fieldName] = array($retVal[$fieldName], $fieldValue);
			};
		} else {
			$retVal[$fieldName] = $fieldValue;
		};
	};
	return $retVal;
};

function _http_connection($url, $cookies = "", $mode, $additional = "") {
	$parsedUrl = parse_url($url);
	if (!isset($parsedUrl["host"])) return false;
	
	$host = $parsedUrl["host"];
	if (isset($parsedUrl["path"])) {
		$path = $parsedUrl["path"];
	} else {
		$path = "/";
	};
	if (isset($parsedUrl["query"])) $path = $path . "?" . $parsedUrl["query"];

	$f = fsockopen($host, 80);
	if (!$f) return false;
	
	fwrite($f, "$mode $path HTTP/1.0\r\n");
	fwrite($f, "Host: $host\r\n");
	fwrite($f, "Connection: close\r\n");
	fwrite($f, "Cache-Control: max-age=0\r\n");
	fwrite($f, "Cookie: $cookies\r\n");
	fwrite($f, "Accept: */*\r\n");
	fwrite($f, "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.1 (KHTML, like Gecko)  Iron/13.0.800.0 Chrome/13.0.800.0 Safari/535.1\r\n");
	fwrite($f, $additional);
	fwrite($f, "\r\n");

	$data = "";
	while (!feof($f)) {
		$data .= fread($f, 128);
	};
	return $data;
};

function http_get($url, $cookies = "") {
	return _http_connection($url, $cookies, "GET");
};

function http_post($url, $postParams, $cookies = "") {
	return _http_connection($url, $cookies, "POST",
		"Content-Type: application/x-www-form-urlencoded\r\n" . 
		"Content-Length: " . strlen($postParams) . "\r\n" . 
		"\r\n" . 
		"$postParams \r\n" . 
		"\r\n"
	);
};

function http_parse_cookies($headers) {
	$cookieArray = array();
	if (!isset($headers['set-cookie'])) return $cookieArray;
	$rawCookies = $headers['set-cookie'];
	if (!is_array($rawCookies)) $headers = array($rawCookies);

	foreach ($rawCookies as $curCookie) {
		$curCookie = explode("; ", $curCookie);
		$curCookie = explode("=", $curCookie[0]);

		$cookieName = $curCookie[0];
		$cookieValue = $curCookie[1];

		$cookieArray[$cookieName] = $cookieValue;
	};
	return $cookieArray;
};


function http_generate_cookies($cookieArray) {
	$rawCookies = "";
	foreach ($cookieArray as $cookieName => $cookieValue) {
		$rawCookies .= "$cookieName=$cookieValue; ";
	};
	return $rawCookies;
};

function http_parse_content($content) {
	return substr($content, strpos($content, "\r\n\r\n") + 4);
};

?>
