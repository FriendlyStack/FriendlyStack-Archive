<?php
@ini_set('default_charset', 'UTF-8');
switch($_SERVER['REQUEST_METHOD'])
{
case 'GET': $request = &$_GET; break;
case 'POST': $request = &$_POST; break;
}

switch($request['action'])
{
case 'decryptFriendlyStack':
	system("sudo /home/pstack/bin/decryptFriendlyStack ".$request['password']);
	echo '<script>window.location.href = "/";</script>';
	break;
default:
	echo "
			<html>
			<head>
			<link rel=\"stylesheet\" type=\"text/css\" href=\"iconfont/material-icons-lock.css\">
			<link rel=\"stylesheet\" type=\"text/css\" href=\"index.css\">
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">
			<title>This FriendlyStack is Locked</title>
			</head><body>";
	echo "<main><div class=\"centered\"><p class=\"heading\">This Friendly Stack is Locked</p>";
	echo "<form method=\"post\"><input type=\"hidden\" name=\"interface\" value=\"$interface\"><input type=\"hidden\" name=\"action\" value=\"decryptFriendlyStack\">";
	echo "<input class=\"tftextinput\" type=\"password\" name=\"password\" size=\"10\" maxlength=\"40\"><input class=\"tfbutton\" type=\"submit\" value=\"Unlock\"></form>";
	echo "<i class=\"material-icons md-home md-dark-green\" valign=\"middle\">lock</i>";
	echo "</div></main></body>";
	break;
}
?>
