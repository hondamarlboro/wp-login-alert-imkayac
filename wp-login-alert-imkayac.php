<?php
/*
Plugin Name: WP Login Alert Notify(im.kayac.com)
Plugin URI: http://daisukeblog.com/
Description: Notify alerts to im.kayac.com if someone including you has tried to login at Login Control Panel
Version: 0.11
Author: hondamarlboro
Author URI: http://daisukeblog.com/
License: GPLv2 or later http://www.gnu.org/licenses/gpl-2.0.html

This software is a derivative work of "WP Login Alerts by DigiP ver.2013-01-09.9" and the original license information is as follows:

Plugin Name: WP Login Alerts by DigiP
Plugin URI: http://www.ticktockcomputers.com/
Description: E-mails the site owner if anyone reaches or attempts to login to the site. Also shows the usernames they tried to brute force in with.
Version: 2013-01-09.9
Author: DigiP
Author URI: http://www.ticktockcomputers.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function login_alerts_imkayac() { 

	$ip = $_SERVER['REMOTE_ADDR'];
	$hostaddress = gethostbyaddr($ip);
	$browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT'],ENT_QUOTES | ENT_HTML401,"UTF-8");
	$referred =  htmlspecialchars($_SERVER['HTTP_REFERER'],ENT_QUOTES | ENT_HTML401,"UTF-8"); // a quirky spelling mistake that stuck in php

	/* Set timezone if needed */
	date_default_timezone_set('Asia/Tokyo');

	$d1=date("Y/m/d");
	$d2=date("H");
	$d3=date("i:s");
	$d4=$d2;
	$date =("$d1 $d4:$d3 ");

	/* User attempting to login */
	if(isset($_POST['log'])) {
		$who = " by [id:".($_POST['log'])."]";
	} else {
		$who = " Page Has Been Reached but not tried to login yet.";
	}

	if(isset($_POST['log'])) {
		$subject = "[id:".($_POST['log'])."] tried to login";
	} else {
		$subject = "Login page opened";
	}

	$message = "WP Login Attempt".htmlentities($who)."\nDate: ".$date." \nIP: ".$ip." \nHostname: ".$hostaddress." \nBrowser: ".htmlentities($browser)." \nReferral: ".htmlentities($referred)." \n";

	$username = 'USERNAME';
	$password = 'SECRET_KEY';

	$data = array(
    	"message" => $message,
    	"password" => $password,
	);

	$data['sig'] = sha1($data['message'] . $data['password']);
	unset($data['password']);

	$data = http_build_query($data, "", "&");

	//header
	$header = array(
    	"Content-Type: application/x-www-form-urlencoded",
	    "Content-Length: ".strlen($data)
	);

	$context = array(
    	"http" => array(
        	"method"  => "POST",
        	"header"  => implode("rn", $header),
        	"content" => $data
    	)
	);

	$url = "http://im.kayac.com/api/post/{$username}";
	file_get_contents($url, false, stream_context_create($context));

}

add_action( 'login_enqueue_scripts', 'login_alerts_imkayac' );

function login_alerts_imkayac_url() {
    return get_bloginfo( 'url' );
}

add_filter( 'login_headerurl', 'login_alerts_imkayac_url' );

function login_alerts_imkayac_url_title() {
    return 'All login attempts are reported to the Administrator. You have been warned.';
}
add_filter( 'login_headertitle', 'login_alerts_imkayac_url_title' );

if (!empty($_POST['log'])) {
	login_alerts_imkayac();
}

?>
