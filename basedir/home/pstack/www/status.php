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

//This is required because of a bug in pathinfo() resp. basename() that causes filename starting with non ascii characters to be corrupted
setlocale(LC_ALL,'en_US.UTF-8');
switch($_SERVER['REQUEST_METHOD'])
{
case 'GET': $request = &$_GET; break;
case 'POST': $request = &$_POST; break;
}


$ts = gmdate("D, d M Y H:i:s") . " GMT";
header("Expires: $ts");
header("Last-Modified: $ts");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");





exec("sudo /usr/bin/pgrep -F /var/run/pstack.pid",$schrott,$process_status_pstack);
exec("sudo /usr/bin/pgrep -F /var/run/FriendlyStackWatcher.pid",$schrott,$process_status_FriendlyStackFatcher);
exec("lpstat -W not-completed all", $jobs);
if ($process_status_pstack || $process_status_FriendlyStackFatcher) {$bg_color='#ff0000'; $error_message="FriendlyStack service is down, unplug and replug the control unit!";}
elseif (file_exists("/tmp/FriendlyStack.error")) {$bg_color='#ff0000'; $error_message=file_get_contents('/tmp/FriendlyStack.error');}
elseif (file_exists("/tmp/FriendlyStack.busy")) {$bg_color='#ffff00'; $error_message=file_get_contents('/tmp/FriendlyStack.busy');}
elseif (file_exists("/tmp/FriendlyStack.backup")) {$bg_color='#ffff00'; $error_message="Backup in progress...";}
elseif (!empty($jobs)) {$bg_color='#ffff00'; $error_message="FriendlyStack is processing print queue...";}
else {$bg_color='#00cc00'; $error_message="Ready";}
if ($request['action'] == 'status')
{ 
  if ($bg_color == '#10322d') {echo "0";}
  elseif ($bg_color == '#ffff00') {echo "1";}
  elseif ($bg_color == '#ff0000') {echo "2";}
} else {
echo "
                <html>
                <head>
                <link rel=\"stylesheet\" type=\"text/css\" href=\"iconfont/material-icons.css\">
                <title>status</title>
<style>
.container { 
  height: 70px;
  position: relative;
}

.center {
  margin: 0;
  position: absolute;
  top: 50%;
  left: 50%;
  -ms-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
}
</style>
</head>";
//echo "<body bgcolor=\"$bg_color\" style=\"background-image:url(magic.gif)\"></body>";
echo "<body bgcolor=\"$bg_color\">";
//if ($process_status_pstack || $process_status_FriendlyStackFatcher) echo "<div class=\"container\"><div class=\"center\"><a href=\"/status.php?tab=1\" onclick=\"return confirm('$error_message');\"><i class=\"material-icons md-24 md-light\" valign=\"middle\">error</i></a></div></div>";
//if ($process_status_pstack || $process_status_FriendlyStackFatcher) echo "<div class=\"container\"><div class=\"center\"><a href=\"/status.php?tab=1\" onclick=\"return confirm('$error_message');\"><img src=\"magic.png\" height=\"70\" width=\"20\"></a></div></div>";
if ($bg_color == '#ff0000') {
//echo "<div class=\"container\"><div class=\"center\"><img onclick=\"if(confirm('$error_message')) {location.href='index.php?action=acknowledge';}\" src=\"magic.png\" height=\"70\" width=\"20\"></div></div>";
echo "<div class=\"container\"><div class=\"center\"><a href=\"index.php?action=acknowledge\" onclick=\"return confirm('$error_message');\"><img src=\"magic.png\" height=\"70\" width=\"20\"></a></div></div>";
} else {
echo "<div class=\"container\"><div class=\"center\"><img src=\"magic.png\" onclick=\"alert('$error_message');\" height=\"70\" width=\"20\"></div></div>";
}
echo "</body>";
}
?>
