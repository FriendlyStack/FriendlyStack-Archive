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



header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo <<<EOT
<html>
<head>
<script>
function onMyFrameLoad() {
top.location = "/destinations.php?tab=2";
};
</script>
<link href="/fontawesome-free-5.11.2-web/css/all.css" rel="stylesheet">
<style>
div.test {
  font-size:90px;
	  color: rgb(16, 50, 45); text-shadow: 6px 6px 10px rgba(0, 0, 0, 0.19); vertical-align: middle;
}

</style>
</head>
<body>
<iframe id="status" marginwidth="0" maginheight="0" width="20" height="70" scrolling="no" frameborder=0 src="busy1.php" align="left" onload="onMyFrameLoad()");"></iframe>

<div class="test">
<i class="fas fa-spinner fa-spin fa-3x"></i>
<span class="sr-only">Initializing...</span>
</div>
EOT;
echo ' </body> </html>';
?>
