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
if ($process_status_pstack || $process_status_FriendlyStackFatcher) {$bg_color='#ff0000'; $error_message="<pre>FriendlyStack service is down, unplug and replug the control unit</pre>";} else {$bg_color='#10322d'; $error_message="";}
exec("lpstat -W not-completed all", $jobs);
if(!empty($jobs)) {

    $bg_color='#ffff00';
}
        echo "
                <html>
                <head>
                <title>status</title>
                <meta http-equiv=\"refresh\" content=\"5\">
</head>";
//echo "<body bgcolor=\"$bg_color\" style=\"background-image:url(magic.gif)\"></body>";
echo "<body bgcolor=\"$bg_color\"></body>";
?>
