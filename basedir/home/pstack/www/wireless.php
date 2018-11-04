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
	case 'connect_wlan':
		generate_config($request['interface'],$request['ssid'],$request['password']);
		break;
	default:
		echo "
			<html>
			<head>
			<title>pStack</title>
			</head><body>
			";
		echo "</body>";
		list_ssids();
		break;
}


function list_ssids()
{
	echo "<form method=\"post\"><input type=\"hidden\" name=\"interface\" value=\"$interface\"><input type=\"hidden\" name=\"action\" value=\"connect_wlan\"><select name=\"ssid\">";
	echo shell_exec("sudo /home/pstack/bin/wifiConfig");
	echo "</select> <input type=\"password\" name=\"password\" size=\"30\" maxlength=\"40\"></form>";
        echo "<button type=\"button\" 
        onclick=\"window.open('', '_self', ''); window.close();\">Close</button>";
}

function generate_config($INTERFACE,$SSID,$PASSPHRASE)
{
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	echo "<pre>";
	system("sudo /home/pstack/bin/wifiConfig $SSID $PASSPHRASE");
}
?>

