<?php


//FriendlyStack, a system for managing physical and electronic documents as well as photos and videos
//Copyright (C) 2018  Dimitrios F. Kallivroussis, Friendly River LLC
//
//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU Affero General Public License as
//published by the Free Software Foundation, either version 3 of the
//License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU Affero General Public License for more details.
//
//You should have received a copy of the GNU Affero General Public License
//along with this program.  If not, see <http://www.gnu.org/licenses/>.


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
