<?php

// S4X8 EOLib 1.0
// Marcos Vives Del Sol - 23/V/2012
// Licensed under a CC-BY-SA license

require("http_old.inc.php");
require("simple_html_dom.php");

function EOLdoLogin($user, $pass, &$retCookie) {
	$user = rawurlencode($user);
	$pass = rawurlencode($pass);
	$postParams = "username=$user&password=$pass&login=Login&autologin=false";

	$content = http_post("http://www.elotrolado.net/ucp.php?do=login", $postParams);
	$headers = my_http_parse_headers($content);
	if (!isset($headers["set-cookie"])) return false;

	$cookies = http_parse_cookies($headers);
	if (
		(!isset($cookies["phpbb3_eol_u"])) ||
		(!isset($cookies["phpbb3_eol_k"])) ||
		(!isset($cookies["phpbb3_eol_sid"]))
	) return false;

	$retCookie = http_generate_cookies($cookies);
	return true;
};

function EOLreadToken($cookie, $url) {
	$content = http_get($url, $cookie);
	
	$begin = strpos($content, "<input type=\"hidden\" name=\"form_token\" value=\"");
	if ($begin === false) return false;
	$begin += 46;

	$end = strpos($content, "\"", $begin);
	if ($end === false) return false;
	$end -= $begin;
	
	$token = "form_token=" . substr($content, $begin, $end);

	$begin = strpos($content, "<input type=\"hidden\" name=\"creation_time\" value=\"");
	if ($begin === false) return false;
	$begin += 49;

	$end = strpos($content, "\"", $begin);
	if ($end === false) return false;
	$end -= $begin;
	
	$token .= "&creation_time=" . substr($content, $begin, $end);
	
	return $token;
	
};

function EOLreadSignatureToken($cookie) {
	return EOLreadToken($cookie, "http://www.elotrolado.net/ucp.php?i=profile&mode=signature");
};
	
function EOLsendSignature($token, $cookie, $sig) {
	$response = http_post("http://www.elotrolado.net/ucp.php?i=profile&mode=signature", $token . "&submit=Enviar&signature=" . rawurlencode($sig), $cookie);
	return strpos($response, "actualizado") !== false;
};

function EOLsig($user, $pass, $sig) {
	$cookie = "";
	$sid = "";
	if (!EOLdoLogin($user, $pass, $cookie, $sid)) return false;
	$token = EOLreadSignatureToken($cookie);
	if (!$token) return false;
	sleep(1);m.ñ
	return EOLsendSignature($token, $cookie, $sig);
};

function EOLreadPostToken($cookie, $forum) {
	return EOLreadToken($cookie, "http://www.elotrolado.net/posting.php?mode=post&f=$forum");
};
	
function EOLsendPost($token, $cookie, $forum, $title, $content, $showSig = true) {
	if ($showSig) {
		$showSig = "on";
	} else {
		$showSig = "off";
	};

	$postContent  = $token . "&post=Enviar&attach_sig=$showSig&";
	$postContent .= "subject=" . rawurlencode($title) . "&message=" . rawurlencode($content);

	$response = http_post("http://www.elotrolado.net/posting.php?mode=post&f=$forum", $postContent, $cookie);
	echo $response;
};

function EOLpost($user, $pass, $forum, $title, $content) {
	$cookie = "";
	if (!EOLdoLogin($user, $pass, $cookie)) return false;
	$token = EOLreadPostToken($cookie, $forum);
	if (!$token) return false;
	sleep(1);
	return EOLsendPost($token, $cookie, $forum, $title, $content);
};

function EOLreadReplyToken($cookie, $forum, $thread) {
	return EOLreadToken($cookie, "http://www.elotrolado.net/posting.php?mode=reply&f=$forum&t=$thread");
};

function EOLsendReply($token, $cookie, $forum, $thread, $title, $content, $showSig = true) {
	global $eolIP;

	if ($showSig) {
		$showSig = "on";
	} else {
		$showSig = "off";
	};

	$postContent  = $token . "&post=Enviar&attach_sig=$showSig&";
	$postContent .= "subject=" . rawurlencode($title) . "&message=" . rawurlencode($content);

	$response = http_post("http://www.elotrolado.net/posting.php?mode=reply&f=$forum&t=$thread", $postContent, $cookie);
};

function EOLreply($user, $pass, $forum, $thread, $title, $content) {
	$cookie = "";
	if (!EOLdoLogin($user, $pass, $cookie)) return false;
	$token = EOLreadReplyToken($cookie, $forum, $thread);
	if (!$token) return false;
	sleep(1);
	return EOLsendReply($token, $cookie, $forum, $thread, $title, $content);
};

function EOLreadThreads($forum, $page) {
	$threads = array();

	$pageHtml = file_get_html("http://www.elotrolado.net/foro__" . $forum . "_s" . ($page * 50));
	if ($pageHtml === false) return $threads;
	$pageHtml = $pageHtml->find(".row");
	
	foreach ($pageHtml as $thread) {
		$threadTitle = $thread->find(".topictitle")[0]->plaintext;
		$threadAnswers = intval($thread->find(".posts")[0]->plaintext);
		$threadViews = intval($thread->find(".views")[0]->plaintext);
		
		$threadType = "normal";
		$threadIconStyle = $thread->find("dl")[0]->style;
		if (strstr($threadIconStyle, "announce")) {
			$threadType = "annoucement";
		};
		if (strstr($threadIconStyle, "sticky")) {
			$threadType = "sticky";
		};

		$threadId = intval(explode("_", $thread->find("a")[0]->href)[2]);
		
		$threadLocked = strstr($threadIconStyle, "locked");
		
		$threads[] = array(
			"id" => $threadId,
			"title" => $threadTitle,
			"answers" => $threadAnswers,
			"type" => $threadType,
			"locked" => $threadLocked
		);
	};
	
	return $threads;
};

?>
