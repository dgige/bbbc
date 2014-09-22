<?php
/*
 * Various somewhat autonomous functions
 * generaly reusable in various parts of BBBC
 */

require_once('config.php');

$messages_already_shown=0;
$successbuffer='';

// a simple message display system
// 2 kinds of messages : error and success
//   - error stop everything and display itself
//   - success display a sig 
//
// buffering system : if the message comes too early, we buffer it until the headers are sent 
function showerror($e) {
	global $error_must_stop, $message_already_shown;
	require_once('inc/header.php');
	showmessage($e, 'errormsg');
	require_once('inc/footer.php');
	die();

}
function showsuccess($s) {
	global $message_already_shown;
	if ($message_already_shown)
		showmessage($s, 'successmsg');
	else
		$sucessbuffer=$s;
}

function showbuffered() {
	if ($errorbuffer)
		showmessage($errorbuffer, 'errormsg');
	if ($successbuffer)
		showmessage($successbuffer, 'successmsg');
	$message_already_shown
}

function showmessage($m, $msgtype) {
	echo "<div class=\"$msgtype\">$m</div>";
} 

// site functions manage the BBBC local website checksums and URLs
function getsitesecret($role) {
	if ($role='admin') {
		return SITE_ADMIN_SECRET;
	}
	return SITE_USER_SECRET;
}

function gensitehash($confcreator, $confname, $role) {
	$secret=getsitesecret($role);
	return sha1($confcreator.$confname.$role.$secret);
}

function gensiteurl($confcreator, $id, $confname, $role, $guestname=NULL) {
	return (SITE_URL."redirect.php?confcreator=$confcreator&amp;id=$id&amp;confname=$confname&role=$role&amp;hash=".gensitehash($confcreator, $confname, $role).(($guestname!=NULL)?"&amp;guestname=$guestname":''));
}

// BBB functions manage the BBB remote service checksums and URLs
function bbbmeetingID($confcreator, $confname) {
	return $confcreator."-".sha1($confname);
}

function finalizebbburl($command, $qs) {
	return BBB_URL."api/".$command."?".$qs."&checksum=".sha1($command.$qs.BBB_SECRET);
}

function genbbbcreateurl($confcreator, $confname, $role) {
	return finalizebbburl('create', "name=$confname&meetingID=".bbbmeetingID($confcreator, $confname)."&attendeePW=user&moderatorPW=admin");
}

function genbbbjoinurl($confcreator, $confname, $role, $guestname) {
	return finalizebbburl('join', "fullName=$guestname&meetingID=".bbbmeetingID($confcreator, $confname)."&password=$role");
}

?>