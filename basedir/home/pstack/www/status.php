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



exec("sudo /usr/bin/pgrep -F /var/run/pstack.pid",$schrott,$process_status_pstack);
exec("sudo /usr/bin/pgrep -F /var/run/FriendlyStackWatcher.pid",$schrott,$process_status_FriendlyStackFatcher);
exec("lpstat -W not-completed all", $jobs);
if ($process_status_pstack || $process_status_FriendlyStackFatcher) {$bg_color='#ff0000'; $error_message="FriendlyStack service is down, unplug and replug the control unit!";}
elseif (file_exists("/tmp/FriendlyStack.error")) {$bg_color='#ff0000'; $error_message=file_get_contents('/tmp/FriendlyStack.error');}
elseif (file_exists("/tmp/FriendlyStack.scanning")) {$bg_color='#ffff00'; $error_message="FriendlyStack is scanning...";}
elseif (!empty($jobs)) {$bg_color='#ffff00'; $error_message="FriendlyStack is processing print queue...";}
else {$bg_color='#10322d'; $error_message="";}
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
echo "<div class=\"container\"><div class=\"center\"><a href=\"/index.php?action=acknowledge\" onclick=\"return confirm('$error_message');\"><img src=\"magic.png\" height=\"70\" width=\"20\"></a></div></div>";
} else {
echo "<div class=\"container\"><div class=\"center\"><img src=\"magic.png\" onclick=\"alert('$error_message');\" height=\"70\" width=\"20\"></div></div>";
}
echo "</body>";
?>
