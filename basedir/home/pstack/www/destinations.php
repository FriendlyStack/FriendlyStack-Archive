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



//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
@ini_set('default_charset', 'UTF-8');
$sqlPassword = rtrim(file_get_contents('/home/pstack/bin/mysql.pwd',1),"\n");
if(file_exists("/tmp/restore")) {
        header('Location: busy.php');
        exit;
}
switch($_SERVER['REQUEST_METHOD'])
{
case 'GET': $request = &$_GET; break;
case 'POST': $request = &$_POST; break;
}
if (preg_match("/(add_destination|make_printer|remove_printer)/",$request['action']))
{
//$ypos = $request['ypos'];
$ypos = $_COOKIE['ypos'];
//$ypos = 0;
} else {
	$ypos=0;
}

$basepath="/home/pstack/Documents";

$con=mysqli_connect("127.0.0.1","root","$sqlPassword","pStack");
// Check connection
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
mysqli_query($con,"SET NAMES 'utf8'");
switch($request['action'])
{
case 'add_destination':
	if($request['parent'])
	{
		//$query = "INSERT INTO `pStack`.`Destinations` (`ID`, `User`, `Destination`, `Printer`) VALUES ('".(posix_getpid()."-".(microtime(true)*10000))."', '{$_SERVER['REMOTE_USER']}', '{$request['parent']}/{$request['destination']}', '0')";
		//$query = "INSERT INTO `pStack`.`Destinations` (`User`, `Destination`,`Destination_MD5`,checked) VALUES ('{$_SERVER['REMOTE_USER']}', '{$request['parent']}/{$request['destination']}','".md5($request['parent']."/".$request['destination'])."',".(time()+5).")";
		//if (mysqli_query($con,$query)) {$oldmask = umask(0); mkdir("$basepath/{$request['parent']}/{$request['destination']}",0770,true); umask($oldmask);}
		$oldmask = umask(0);
		if (mkdir("$basepath/{$request['parent']}/{$request['destination']}",0770,true)) {
			$query = "INSERT INTO `pStack`.`Destinations` (`User`, `Destination`,`Destination_MD5`,checked) VALUES ('{$_SERVER['REMOTE_USER']}', '{$request['parent']}/{$request['destination']}','".md5($request['parent']."/".$request['destination'])."',".(time()+5).")";
			mysqli_query($con,$query);
			chgrp("$basepath/{$request['parent']}/{$request['destination']}","FriendlyStack");
		}
		umask($oldmask);
	}
	else
	{
		//$query = "INSERT INTO `pStack`.`Destinations` (`ID`, `User`, `Destination`, `Printer`) VALUES ('".(posix_getpid()."-".(microtime(true)*10000))."', '{$_SERVER['REMOTE_USER']}', '{$request['destination']}', '0')";
		//$query = "INSERT INTO `pStack`.`Destinations` (`User`, `Destination`,`Destination_MD5`, checked) VALUES ('{$_SERVER['REMOTE_USER']}', '{$request['destination']}','".md5($request['destination'])."',".(time()+5).")";
		//if (mysqli_query($con,$query)) {$oldmask = umask(0); mkdir("$basepath/{$request['destination']}",0770,true); umask($oldmask);}
		$oldmask = umask(0);
		if(mkdir("$basepath/{$request['destination']}",0770,true)) {
			$query = "INSERT INTO `pStack`.`Destinations` (`User`, `Destination`,`Destination_MD5`, checked) VALUES ('{$_SERVER['REMOTE_USER']}', '{$request['destination']}','".md5($request['destination'])."',".(time()+5).")";
			mysqli_query($con,$query);
			chgrp("$basepath/{$request['destination']}","FriendlyStack");
		}
		umask($oldmask);
		//$query = "INSERT INTO `pStack`.`Destinations` (`ID`, `User`, `Destination`, `Printer`) VALUES ('".sprintf("%010d-%020d",posix_getpid(),microtime(true)*10000)."', '{$_SERVER['REMOTE_USER']}', '{$request['destination']}', '0')";
	}
	//$result = mysqli_query($con,$query);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	break;
case 'remove_destination':
	$query = "SELECT * FROM Destinations where ID='{$request['ID']}'";
	$result = mysqli_query($con,$query);
	$row = mysqli_fetch_assoc($result);
	$destination=$row['Destination'];
	$query = "delete from `pStack`.`Destinations` where `ID`='{$request['ID']}'";
	$result = mysqli_query($con,$query);
	exec("LANG=\"en_US.UTF-8\" sudo /usr/sbin/lpadmin -x ".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination)));
	exec("sudo systemctl reload smbd");
	sleep(4);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	break;	
case 'remove_printer':
	//$query = "SELECT * FROM Destinations where ID='{$request['ID']}'";
	//$result = mysqli_query($con,$query);
	//$row = mysqli_fetch_assoc($result);
	//$destination=$row['Destination'];
	exec("LANG=\"en_US.UTF-8\" sudo /usr/sbin/lpadmin -x ".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($request['Destination'])));
	exec("sudo systemctl reload smbd");
	//sleep(4);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	break;	
case 'change_hostname':
	exec("sudo hostnamectl set-hostname ".$request['hostname']);
	exec("sudo systemctl restart smbd");
	exec("sudo systemctl restart nmbd");
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	break;
case 'show_separator':
	pdf_separator($con,$request['Destination']);
	break;
case 'make_printer':
	make_printer($con,$request['Destination']);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	echo "</body>";
	break;
case 'restore':
        if(!file_exists("/tmp/restore")) {
            $myfile = fopen("/tmp/restore", "w");
            fclose($myfile);
            //exec('bash -c "exec nohup setsid sudo /home/pstack/bin/doRestore > /dev/null 2>&1 &"');
            header('Location: busy.php');
        }
        break;
case 'change_password':
	if ($request['new_username'] != $_SERVER['PHP_AUTH_USER'] && $request['new_password'] == "" && $request['new_password_verify'] == "" ) {
		exec("sudo /home/pstack/bin/changeUsernamePassword ".$_SERVER['PHP_AUTH_USER']." ".$request['new_username'],$retArr, $retVal);
		if ($retVal == 0) {echo '<script>alert("Username successfully changed!");window.location.href = "destinations.php?tab=3";</script>';} else {echo '<script>alert("Username Change failed..!");</script>';}
	} else if ($request['new_username'] == $_SERVER['PHP_AUTH_USER'] && $request['new_password'] != "" && $request['new_password_verify'] != "" && $request['new_password'] == $request['new_password_verify'] ) {
		exec("sudo /home/pstack/bin/changeUsernamePassword ".$_SERVER['PHP_AUTH_USER']." ".$_SERVER['PHP_AUTH_PW']." ".$request['new_password'],$retArr, $retVal);
		if ($retVal == 0) {echo '<script>alert("Password successfully changed!");window.location.href = "destinations.php?tab=3";</script>';} else {echo '<script>alert("Password Change failed..!");</script>';}
	} else if ($request['new_username'] == $_SERVER['PHP_AUTH_USER'] && $request['new_password'] != "" && $request['new_password_verify'] != "" && $request['new_password'] != $request['new_password_verify'] ) {
		echo '<script>alert("Password Change failed New Password and New Password Verification don\'t match!");</script>';
	} else if ($request['new_username'] != $_SERVER['PHP_AUTH_USER'] && $request['new_password'] != "" && $request['new_password_verify'] != "" && $request['new_password'] == $request['new_password_verify'] ) {
		exec("sudo /home/pstack/bin/changeUsernamePassword ".$_SERVER['PHP_AUTH_USER']." ".$_SERVER['PHP_AUTH_PW']." ".$request['new_password'],$retArr, $retVal2);
		exec("sudo /home/pstack/bin/changeUsernamePassword ".$_SERVER['PHP_AUTH_USER']." ".$request['new_username'],$retArr, $retVal1);
		if ($retVal1 == 0) {echo '<script>alert("Username successfully changed!");</script>';} else {echo '<script>alert("Username Change failed..!");</script>';}
		if ($retVal2 == 0) {echo '<script>alert("Password successfully changed!");window.location.href = "destinations.php?tab=3";</script>';} else {echo '<script>alert("Password Change failed..!");</script>';}
	}
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	break;
case 'change_date_time':
	exec("sudo timedatectl set-timezone ".$request['time_zone'],$retArr, $retVal);
	exec("sudo timedatectl set-time '".$request['time']."'",$retArr, $retVal);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	echo "</body>";
	break;
case 'change_encryption_key':
	if (($request['new_luks_key'] != "") && ($request['new_luks_key'] == $request['new_luks_key_verify']) && ($request['old_luks_key'] != "")) {
		exec("sudo /home/pstack/bin/changeLuksKey ".$request['old_luks_key']." ".$request['new_luks_key'],$retArr, $retVal);
		if ($retVal == 0) {echo '<script>alert("Encryption Key successfully changed!");</script>';} else {echo '<script>alert("Encryption Key Change failed..!");</script>';}
	} else {echo '<script>alert("Encryption Key Change failed..!");</script>';}
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	break;
case 'add_backup_media':
	$query = "INSERT INTO `pStack`.`BackupMedia` (`SerialNumber`, `Name`) VALUES ('".$request['serialNumber']."', '".$request['name']."')";
	$result = mysqli_query($con,$query);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	echo "</body>";
	break;
case 'remove_backup_media':
	$query = "DELETE FROM `pStack`.`BackupMedia` WHERE `ID`={$request['ID']}";
	$result = mysqli_query($con,$query);
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	echo "</body>";
	break;
case 'changeBootEntry':
        if(file_exists("/sys/firmware/efi")) {
            exec("sudo efibootmgr -n ".$request['bootEntry'],$retArr, $retVal);
        }
        list_destinations($con,$request['parent'],$request['action'],$request['tab']);
        echo "</body>";
        break;
case 'programFSCU':
        exec("sudo /home/pstack/bin/programFSCU.sh",$retArr, $retVal);
        list_destinations($con,$request['parent'],$request['action'],$request['tab']);
        echo "</body>";
        break;
default:
	list_destinations($con,$request['parent'],$request['action'],$request['tab']);
	echo "</body>";
	break;
}

$result->free();
mysqli_close($con);

function print_form($parent = NULL)
{
	echo "<form action=\"destinations.php\" method=\"post\" name=\"destination_form\">
		<input id=\"destination\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"destination\" type=\"text\" value=\"\" size=\"20\" maxlength=\"99\">
		<input name=\"action\" type=\"hidden\" value=\"add_destination\">
	        <input type=\"hidden\" name=\"tab\" value=\"1\">
		<input name=\"parent\" type=\"hidden\" value=\"".$parent."\">
		<input name=\"find\" type=\"hidden\" value=\"find\">
		</form>";

}

function list_destinations($con,$parent,$action,$tab)
{
	$free =(1*(disk_free_space("/home/pstack")/disk_total_space("/home/pstack")));
	$printers = shell_exec("lpstat -v");
	echo " <html> <head> <title>Friendly Stack</title><link rel=\"stylesheet\" type=\"text/css\" href=\"iconfont/material-icons.css\">  
		<link rel=\"stylesheet\" type=\"text/css\" href=\"tabs.css\">
		<link rel=\"stylesheet\" type=\"text/css\" href=\"destinations.css\">
                <link rel=\"stylesheet\" href=\"/font-awesome-4.7.0/css/font-awesome.min.css\">
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">
                <script src=\"//code.jquery.com/jquery-1.10.2.js\"></script>
		</head> ";
//echo "<body OnLoad=\"document.destination_form.destination.focus();\">";

print "<body>";

echo '<script>
//$(document).ready(function(){

//window.scroll(0,300);

//});
	function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
		var angleInRadians = (angleInDegrees-90) * Math.PI / 180.0;

		return {
		x: centerX + (radius * Math.cos(angleInRadians)),
			y: centerY + (radius * Math.sin(angleInRadians))
};
}

function describeArc(x, y, radius, startAngle, endAngle){

	var start = polarToCartesian(x, y, radius, endAngle);
	var end = polarToCartesian(x, y, radius, startAngle);

	var largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";

	var d = [
		"M", start.x, start.y, 
		"A", radius, radius, 0, largeArcFlag, 0, end.x, end.y
	].join(" ");

	return d;       
}

window.onload = function() {
	document.getElementById("arc2").setAttribute("d", describeArc(150, 150, 100, 0,'.((1-$free) * 359.99999).'));
	document.getElementById("arc1").setAttribute("d", describeArc(150, 150, 100, '.((1-$free) * 359.99999).',360));
};';
if (preg_match("/(add_destination|make_printer|remove_printer)/",$action) || $parent)
{
echo '/*To reload document without jQuery*/
/*document.addEventListener("DOMContentLoaded", function(event) { 
        //window.scroll(0,sessionStorage.ypos);
});*/

$( document ).ready(function() {
        //window.scroll(0,sessionStorage.ypos);
        window.scroll(0,sessionStorage.ypos);
});';
}
$myChecked[$tab] = "checked"; 

		echo "</script><main><div id=\"home\"><a href=\"/\"><i class=\"material-icons md-dark-green md-home\">home</i></a></div>";
echo '<input id="tab1" type="radio" name="tabs" class="tabs" '.$myChecked[1].'>
    <label for="tab1">Destinations</label>
        
  <input id="tab2" type="radio" name="tabs" class="tabs" '.$myChecked[2].'>
  <label for="tab2">Capacity</label>
    
  <input id="tab3" type="radio" name="tabs" class="tabs" '.$myChecked[3].'>
  <label for="tab3">Settings</label>
    
  <input id="tab4" type="radio" name="tabs" class="tabs" '.$myChecked[4].'>
  <label for="tab4">Wifi</label>
  
          
  <section id="content2">';
	//echo disk_free_space("/")." ".disk_total_space("/");
	//printf( "%.1f%% Disk Space Available",$free);
	exec("sudo /usr/bin/pgrep -F /var/run/pstack.pid",$schrott,$process_status);
	if ($process_status) {echo " pstack is dead!";}
        //echo "<p>$free</p>";
	echo "<p class=\"heading\">Available Space on FriendlyStack:</p>";
if ($free < 0.1) {$tone=200;} else {$tone=16;}
echo '<svg id="freeSpace" width="300" height="300" class="shadow">
    <path id="arc1" fill="none" stroke="rgb('.$tone.', 50, 45)" stroke-width="40" />
    <path id="arc2" fill="none" stroke="rgba(0, 0, 0, 0.06)" stroke-width="40" />
<text x="150" y="150" fill="rgb('.$tone.', 50, 45)" text-anchor="middle" font-family="Verdana" font-size="18" font-weight="bold" dominant-baseline="central">'.sprintf("%2.0f%%",(100*$free)).'</text>
  </svg></div>';
        $result=mysqli_query($con,"SELECT count(ID) as Count,Media from Documents group by Media order by Media asc");
	while ($row=mysqli_fetch_assoc($result)) {
		//echo "<div class=\"destination\"> <i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i>{$row["Media"]}s {$row["Count"]}</div>";
		echo "<div class=\"destination\"><i class=\"fa fa-file-text\" style=\"font-size:22px;color: rgb(16, 50, 45); text-shadow: 6px 6px 10px rgba(0, 0, 0, 0.19); vertical-align: middle;\"></i><span style=\"font-size:14px;line-height: 170%;\">  {$row["Media"]}s {$row["Count"]}</span></div>";
	}
  //system("udevadm info --name=/dev/backup | grep SERIAL_SHORT");
  echo "<p class=\"heading\">Manage Backup Media:</p>";
if(file_exists("/dev/backup")) {
if(!preg_match("/Serial Number:\s+(.*)$/i",exec("sudo smartctl -i /dev/backup | grep Serial"),$sn))
{
	if(!preg_match("/Serial Number:\s+(.*)$/i",exec("sudo smartctl -i -d scsi /dev/backup | grep Serial"),$sn))
	{
	        preg_match("/SERIAL_SHORT=(\w+)$/",exec("udevadm info --name=/dev/backup | grep SERIAL_SHORT"),$sn);
	}
}
}
if (isset($sn[1])) {
  echo "<div class=\"form_heading\"><p class=\"form_heading\">Currently Connected USB Drive:</p></div>";
        echo "<form action=\"destinations.php\" method=\"post\" id=\"add-backup-media\" name=\"destination_form\">";
	echo "<div class=\"destination\"> <a href=\"#\" onclick=\"document.getElementById('add-backup-media').submit();\"><i class=\"material-icons md-dark-green md-destination\">add_box</i></a>";
        echo "<input type=\"text\" name=\"name\"> (S/N: {$sn[1]})";
        if(preg_match('/FriendlyStack_BackupMedia/', `sudo ntfslabel /dev/backup1`)) {
	echo "<div class=\"destination\"><a href=\"destinations.php?action=restore\"><i class=\"material-icons md-dark-green md-destination\">storage</i></a>Restore (all data on disk will be overwritten)";
        }
        echo "</div>";
	echo "<input type=\"hidden\" name=\"action\" value=\"add_backup_media\"><input type=\"hidden\" name=\"serialNumber\" value=\"".$sn[1]."\">";
	echo "<input type=\"hidden\" name=\"tab\" value=\"2\"></form>";
}
        $result=mysqli_query($con,"SELECT * FROM BackupMedia");
  echo "<div class=\"form_heading\"><p class=\"form_heading\">Registered Backup Media:</p></div>";
	while ($row=mysqli_fetch_assoc($result)) {
		echo "<div class=\"destination\"> <a href=\"".htmlentities("destinations.php?tab=2&action=remove_backup_media&ID={$row["ID"]}")."\" onclick=\"sessionStorage.ypos = window.pageYOffset;\"><i class=\"material-icons md-dark-green md-destination\">remove_circle</i></a>{$row["Name"]} (S/N: {$row["SerialNumber"]})</div>";
	}
	        //$query = "SELECT * FROM Destinations where ID='{$request['ID']}'";
	        //$result = mysqli_query($con,$query);
		//$row = mysqli_fetch_assoc($result);
  echo '</section>';
  echo '<section id="content1">';
	if(!($parent))
	{
	        echo "<p class=\"heading\">Destinations and Cover Sheets:</p>";
		print_form();
	}
	else
	{
	//	echo "<pre><a href=\"/\">[Home]</a><br><br>";
		echo "<a href=\"".htmlentities("destinations.php?tab=1")."\">Top</A>\n\n";
	}
$basedir='/home/pstack/Documents/';
$directories = array();
$di = new RecursiveDirectoryIterator($basedir);
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
    if($file->isDir() && preg_match('/.*\/\.$/',$filename)) {
	 $destination = substr($filename,strlen($basedir),strlen($filename)-strlen($basedir)-2);
	 if (strlen($destination)) $directories[$destination] = md5($destination);
    }
}
ksort($directories,SORT_NATURAL | SORT_FLAG_CASE);
foreach (array_keys($directories) as $entry) {
if (strpos($printers, "pstack:" . $directories[$entry]) !== false) {
	//echo "<div class=\"destination\"><a name=\"$entry\" href=\"".htmlentities("destinations.php?parent={$entry}")."\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?action=show_separator&Destination=$entry")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"".htmlentities("destinations.php?action=remove_printer&Destination=$entry")."\"><i class=\"material-icons md-dark-green md-destination\">print</i></A><span class=\"destination\"> ".$entry."</span></div>\n";
	//echo "<div class=\"destination\"><a name=\"$entry\" href=\"".htmlentities("destinations.php?parent={$entry}")."\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?action=show_separator&Destination=$entry")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"".htmlentities("destinations.php?action=remove_printer&Destination=$entry")."\" onclick=\"document.cookie='ypos='+window.pageYOffset;\"><i class=\"material-icons md-dark-green md-destination\">print</i></A><span class=\"destination\"> ".$entry."</span></div>\n";
	echo "<div class=\"destination\"><a name=\"$entry\" href=\"".htmlentities("destinations.php?tab=1&parent={$entry}")."\" onclick=\"sessionStorage.ypos = window.pageYOffset;\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?tab=1&action=show_separator&Destination=".urlencode($entry))."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"".htmlentities("destinations.php?tab=1&action=remove_printer&Destination=$entry")."\" onclick=\"sessionStorage.ypos = window.pageYOffset;\"><i class=\"material-icons md-dark-green md-destination\">print</i></A><span class=\"destination\"> ".$entry."</span></div>\n";
	//echo "<div class=\"destination\"><a name=\"$entry\" href=\"".htmlentities("destinations.php?parent={$entry}")."\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?action=show_separator&Destination=$entry")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"#\" onclick=\"javascript:location.href='".htmlentities("destinations.php?action=remove_printer&Destination=$entry")."'+'&ypos='+window.pageYOffset\"\"><i class=\"material-icons md-dark-green md-destination\">print</i></A><span class=\"destination\"> ".$entry."</span></div>\n";
} else {
	//echo "<div class=\"destination\"><a name=\"{$row['ID']}\" href=\"".htmlentities("destinations.php?parent={$entry}")."\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?action=show_separator&Destination=$entry")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"".htmlentities("destinations.php?action=make_printer&Destination=$entry")."\" onclick=\"document.cookie='ypos'+window.pageYOffset; alert(window.pageYOffset);\"><i class=\"material-icons md-dark md-inactive md-destination\">print</i></A><span class=\"destination md-destination\"> ".$entry."</span></div>\n";
	//echo "<div class=\"destination\"><a name=\"{$row['ID']}\" href=\"".htmlentities("destinations.php?parent={$entry}")."\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?action=show_separator&Destination=$entry")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"".htmlentities("destinations.php?action=make_printer&Destination=$entry")."\" onclick=\"document.cookie='ypos='+window.pageYOffset;\"><i class=\"material-icons md-dark md-inactive md-destination\">print</i></A><span class=\"destination md-destination\"> ".$entry."</span></div>\n";
	echo "<div class=\"destination\"><a name=\"{$row['ID']}\" href=\"".htmlentities("destinations.php?tab=1&parent={$entry}")."\" onclick=\"sessionStorage.ypos = window.pageYOffset;\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?tab=1&action=show_separator&Destination=".urlencode($entry))."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"".htmlentities("destinations.php?tab=1&action=make_printer&Destination=$entry")."\" onclick=\"sessionStorage.ypos = window.pageYOffset;\"><i class=\"material-icons md-dark md-inactive md-destination\">print</i></A><span class=\"destination md-destination\"> ".$entry."</span></div>\n";
	//echo "<div class=\"destination\"><a name=\"{$row['ID']}\" href=\"".htmlentities("destinations.php?parent={$entry}")."\"><i class=\"material-icons md-dark-green md-destination\">create_new_folder</i></a> <a href=\"".htmlentities("destinations.php?action=show_separator&Destination=$entry")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a> <a href=\"#\" onclick=\"javascript:location.href='".htmlentities("destinations.php?action=make_printer&Destination=$entry")."'+'&ypos='+window.pageYOffset\"\"><i class=\"material-icons md-dark md-inactive md-destination\">print</i></A><span class=\"destination md-destination\"> ".$entry."</span></div>\n";
}
		//if($entry == $parent) {echo "</pre>";print_form($parent);echo "<pre>";}
		if($entry == $parent) {print_form($parent);}
	}
echo "<p style=\"padding: 10px 0px 0px 0px; font: 15px sans-serif;\">Universal Cover Sheets:</p>";
echo "<div class=\"destination\"><a href=\"".htmlentities("destinations.php?action=show_separator")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a><span class=\"destination\">Generic Separator</span></div>
	<div class=\"destination\"><a href=\"".htmlentities("destinations.php?action=show_separator&Destination=.single")."\" target=\"_blank\"><i class=\"material-icons md-dark-green md-destination\">insert_drive_file</i></a><span class=\"destination\">Single Page Separator</span></div>";
   echo '</section>';
   echo '<section id="content3">';
	echo "<p class=\"heading\">Personalize FriendlyStack:</p>";
	echo "<table id=\"settings\"><form action=\"destinations.php\" method=\"post\" name=\"destination_form\"><tr><td class=\"form_heading\" colspan=2>Change Hostname</td></tr><tr><td>Hostname:</td><td class=\"absorbing-column\"><input id=\"hostname\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"hostname\" type=\"text\" value=\"".shell_exec("hostname")."\" size=\"20\" maxlength=\"99\"></td></tr><td></td><td><input name=\"action\" type=\"submit\" value=\"change_hostname\"></td><input type=\"hidden\" name=\"tab\" value=\"3\"></form></tr><tr height=\"10px\"><td colspan=2></td></tr>";
	echo "<tr><td colspan=2 class=\"form_heading\">Change Username &amp; Password</td></tr><tr><form action=\"destinations.php\" method=\"post\" name=\"destination_form\"><td>Username:</td><td><input id=\"new_username\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"new_username\" value=\"".$_SERVER['PHP_AUTH_USER']."\" size=\"20\" maxlength=\"99\"></td></tr><tr><td>New Password:</td><td><input id=\"new_password\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"new_password\" type=\"password\" value=\"\" size=\"20\" maxlength=\"99\"></td></tr><tr><td>Verify Password:</td><td><input id=\"new_password_verify\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"new_password_verify\" type=\"password\" value=\"\" size=\"20\" maxlength=\"99\"></td></tr><tr><td></td><td><input name=\"action\" type=\"submit\" value=\"change_password\"></td></tr><input type=\"hidden\" name=\"tab\" value=\"3\"></form><tr height=\"10px\"><td colspan=2></td></tr><tr><td colspan=2 class=\"form_heading\">Change Time Zone</td></tr>";
	preg_match("/Time zone: ([^\s]+)/",shell_exec("timedatectl"),$matches);
$localTimeZone = $matches[1];
$d = new DateTime('now',new DateTimeZone($localTimeZone));
echo "<tr><form action=\"destinations.php\" method=\"post\" name=\"destination_form\"><td>Date and Time:</td><td><input id=\"time\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"time\" value=\"".$d->format('Y-m-d H:i:s')."\" size=\"20\" maxlength=\"99\"></td></tr><tr><td>Time Zone:</td><td><select id=\"time_zone\" name=\"time_zone\">\n";
exec("timedatectl list-timezones --no-pager",$timeZones,$rc);
foreach($timeZones as $timeZone) {
if ($timeZone == $localTimeZone) {
   echo "<option selected>".$timeZone . "</option>";
} else {
   echo "<option>".$timeZone . "</option>";
}
}
   echo "</select></td></tr><tr><td></td><td><input name=\"action\" type=\"submit\" value=\"change_date_time\"></td></tr><input type=\"hidden\" name=\"tab\" value=\"3\"></form>";
   echo "<tr height=\"10px\"><td colspan=2></td></tr><tr><td colspan=2 class=\"form_heading\">Change Encryption Key</td></tr><tr><form action=\"destinations.php\" method=\"post\" name=\"destination_form\"><td>Old Key:</td><td><input id=\"old_luks_key\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" type=\"password\" name=\"old_luks_key\" value=\"\" size=\"20\" maxlength=\"99\"></td></tr><tr><td>New Key:</td><td><input id=\"new_luks_key\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"new_luks_key\" type=\"password\" value=\"\" size=\"20\" maxlength=\"99\"></td></tr><tr><td>Verify Key:</td><td><input id=\"new_luks_key_verify\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"new_luks_key_verify\" type=\"password\" value=\"\" size=\"20\" maxlength=\"99\"></td></tr><tr><td></td><td><input name=\"action\" type=\"submit\" value=\"change_encryption_key\"></td></tr><input type=\"hidden\" name=\"tab\" value=\"3\"></form></table>";
   echo '</section>';
   echo '<section id="content4">';
	echo "<p class=\"heading\">Connect FriendlyStack to WiFi:</p>";
	echo "<a href=\"wireless.php\" target=\"_blank\"><i class=\"material-icons md-48 md-dark-green\">wifi</i></a>\n\n";
	echo "<p class=\"heading\">FriendlyStack Root Certificate:</p>";
	echo "<div class=\"destination\"><a href=\"FriendlyStack.crt\"><i class=\"material-icons md-dark-green md-48\">vpn_key</i></a></div>";
        if(file_exists("/sys/firmware/efi")) {
            echo "<p class=\"heading\">Change UEFI Boot Device:</p>";
            exec("efibootmgr",$bootEntries);
            echo "<pre>";
            foreach($bootEntries as $bootEntry) {
                if(preg_match("/Boot(\d{4}).*/",$bootEntry,$result)) {
                    echo "<a href=\"destinations.php?action=changeBootEntry&bootEntry=$result[1]&tab=4\">$bootEntry</a>\n";
                }
            }
            echo "</pre>";
        }
        echo "<p class=\"heading\">Program FriendlyStack Control Unit (FSCU):</p>";
        echo "<a href=\"destinations.php?action=programFSCU&tab=4\"><i class=\"material-icons md-48 md-dark-green\">build</i></a>\n";
echo '</section></main></body>';


}

function list_destinations_old($con,$parent)
{
	$printers = shell_exec("lpstat -v");
	echo "<body OnLoad=\"document.destination_form.destination.focus();\">";
	if(!($parent))
	{
		//echo "<pre><a href=\"/\">[Home]</a><br><br>";
		echo "<pre><a href=\"/\">[Home]</a><br><br></pre>";
		print_form();
		echo "<pre>";
	}
	else
	{
		echo "<pre><a href=\"/\">[Home]</a><br><br>";
		echo "<a href=\"".htmlentities("destinations.php")."\">Top</A>\n\n";
	}
	//$query = "SELECT * FROM Destinations where user='".$_SERVER['REMOTE_USER']."' order by Destination asc";
	$query = "SELECT * FROM Destinations order by Destination asc";
	$result = mysqli_query($con,$query);
	while($row = mysqli_fetch_assoc($result)) {
		//echo "<a href=\"".htmlentities("destinations.php?parent={$row['Destination']}")."\">+</a><a href=\"destinations.php?action=show_separator&ID={$row['ID']}\">P</a>{$row['ID']} ".utf8_decode($row['Destination'])."\n";
		//echo "<a href=\"".htmlentities("destinations.php?parent={$row['Destination']}")."\"><img src=\"add_category.png\"></a> <a href=\"".htmlentities("destinations.php?action=remove_destination&ID={$row['ID']}")."\" onclick=\"return confirm('Are you sure you want to delete this item?');\"><img src=\"delete_category.png\"></a> <a href=\"".htmlentities("destinations.php?action=show_separator&ID={$row['ID']}")."\"><img src=\"cover_sheet.png\"></a> <a href=\"".htmlentities("destinations.php?action=make_printer&ID={$row['ID']}")."\"><img src=\"printer_small.png\"></A> ".$row['Destination']."\n";
if (strpos($printers, $row['Destination_MD5']) !== false) {
	echo "<a name=\"{$row['ID']}\" href=\"".htmlentities("destinations.php?parent={$row['Destination']}")."\"><img src=\"add_category.png\" class=\"icon\"></a> <a href=\"".htmlentities("destinations.php?action=show_separator&ID={$row['ID']}")."\" target=\"_blank\"><img src=\"cover_sheet.png\" class=\"icon\"></a> <a href=\"".htmlentities("destinations.php?action=remove_printer&ID={$row['ID']}")."\"><img src=\"printer_small_remove.png\" class=\"icon\"></A> ".$row['Destination']."\n";
} else {
	echo "<a name=\"{$row['ID']}\" href=\"".htmlentities("destinations.php?parent={$row['Destination']}")."\"><img src=\"add_category.png\" class=\"icon\"></a> <a href=\"".htmlentities("destinations.php?action=show_separator&ID={$row['ID']}")."\" target=\"_blank\"><img src=\"cover_sheet.png\" class=\"icon\"></a> <a href=\"".htmlentities("destinations.php?action=make_printer&ID={$row['ID']}")."\"><img src=\"printer_small.png\" class=\"icon\"></A> ".$row['Destination']."\n";
}
		if($row['Destination']==$parent) {echo "</pre>";print_form($parent);echo "<pre>";}
	}
	echo "\n\n<a href=\"".htmlentities("destinations.php?action=show_separator")."\" target=\"_blank\">Generic Separator</a>\n\n<a href=\"".htmlentities("destinations.php?action=show_separator&ID=single")."\" target=\"_blank\">Single Page Separator</a>";
	echo "\n\n<a href=\"wireless.php\" target=\"_blank\"><img src=\"wifi.png\" class=\"icon\"></a></pre>";

}

function make_printer($con,$destination)
{
	if ($destination)
	{
		//list($a,$b)=explode('-',$ID);
		//$query = "SELECT * FROM Destinations where ID='$ID'";
		//$result = mysqli_query($con,$query);
		//$row = mysqli_fetch_assoc($result);
		$user=$_SERVER['REMOTE_USER'];
		//$destination=$row['Destination'];
		//$destination_md5=$row['Destination_MD5'];
		$destination_md5=md5($destination);
		//echo exec("LANG=\"en_US.UTF-8\" sudo /usr/sbin/lpadmin -p \"".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination))."\" -v \"pstack:".$destination_md5."\" -E -m drv:///sample.drv/generic.ppd");
		//echo exec("LANG=\"en_US.UTF-8\" sudo /usr/sbin/lpadmin -p \"".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination))."\" -v \"pstack:".$destination_md5."\" -E -P /etc/cups/ppd/generic.ppd");
		//exec("LANG=\"en_US.UTF-8\" sudo /usr/sbin/lpadmin -p \"".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination))."\" -v \"pstack:".$destination_md5."\" -E");
		exec("LANG=\"en_US.UTF-8\" sudo /usr/sbin/lpadmin -p \"".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination))."\" -v \"pstack:".$destination_md5."\" -E -m drv:///sample.drv/generic.ppd");
		exec("sudo systemctl reload smbd");
		//echo exec("LANG=\"en_US.UTF-8\" /usr/bin/rpcclient localhost -U\"www-data%\" -c 'setdriver ".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination))." \"KONICA MINOLTA PS Color Laser Class Driver\"'");
		//exec("LANG=\"en_US.UTF-8\" /usr/bin/rpcclient localhost -U\"www-data%\" -c 'setdriver ".preg_replace("/[\s\.\/\,\!\|]/","_",clean_string($destination))." \"KONICA MINOLTA mc4650 PS PPD\"'");
		$destination_md5=md5($destination);
		$query = "SELECT * FROM `pStack`.`Destinations` WHERE `Destination_md5` = '$destination_md5'";
		if (mysqli_num_rows(mysqli_query($con,$query)) == 0) {

		$query = "INSERT INTO `pStack`.`Destinations` (`User`, `Destination`,`Destination_MD5`, checked) VALUES ('{$_SERVER['REMOTE_USER']}', '$destination','$destination_md5',".(time()+5).")";
		mysqli_query($con,$query);
		}
	}
}

function pdf_separator($con,$destination)
{

	//require('code128.php');
	require("qrcode.class.php");
	require('fpdf.php');
	if ($destination)
	{
		if ($destination==".single")
		{
			$user='All';
			$destination='Single Page Scan';
			$destination_md5="single";
		}
		else
		{
		//$query = "SELECT * FROM Destinations where ID='$ID'";
		//$result = mysqli_query($con,$query);
		//$row = mysqli_fetch_assoc($result);
		$user=$_SERVER['REMOTE_USER'];
		//$destination=$row['Destination'];
		//$destination_md5=$row['Destination_MD5'];
		$destination_md5=md5($destination);
		$query = "SELECT * FROM `pStack`.`Destinations` WHERE `Destination_md5` = '$destination_md5'";
		if (mysqli_num_rows(mysqli_query($con,$query)) == 0) {

		$query = "INSERT INTO `pStack`.`Destinations` (`User`, `Destination`,`Destination_MD5`, checked) VALUES ('{$_SERVER['REMOTE_USER']}', '$destination','$destination_md5',".(time()+5).")";
		mysqli_query($con,$query);
		}
		}
	} else
	{
		$user='All';
		$destination='Generic Page Separator';
		//$a=123456789;
		//$b=987654321;
		$destination_md5=0;
	}
	$pdf = new FPDF();
	//$pdf=new PDF_Code128();
	$pdf->AddPage('P','A4');
	$pdf->SetFont('Arial','',14);
	$pdf->SetLeftMargin(20);
	$pdf->SetRightMargin(20);
	$pdf->Ln(8);
	$pdf->Cell(0,0,'Friendly Stack',0,1,'L');
	$pdf->Cell(0,0,'Friendly Stack',0,1,'C');
	$pdf->Cell(0,0,'Friendly Stack',0,1,'R');
	$pdf->Ln(5);
	//$pdf->Cell(0,0,$a,0,1,'L');
	//$pdf->Cell(0,0,$b,0,1,'R');
	$pdf->Ln(5);
	//$pdf->Cell(0,0,$a,0,1,'L');
	//$pdf->Cell(0,0,$b,0,1,'R');
	$pdf->Ln(20);
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(0,0,'Document Separator',0,1,'C');
	$pdf->Ln(15);
	$pdf->SetFont('Arial','',14);
	$pdf->MultiCell(0,8,"Category: ".utf8_decode($destination)."\nUser: $user",'LTRB','C');
	//$code="pStack:$a-$b";
	$code="pStack:".$destination_md5;
	//$pdf->Code128(20,120,$code,170,60);
	$qrcode = new QRcode($code);
	$qrcode->displayFPDF($pdf, 50, 130, 110);
	//$pdf->SetXY(20,165);
	//$pdf->Write(5,'Code: "'.$code.'"');
	$separator = $pdf->Output('','S');
	header('Content-Description: File Transfer');
	header('Content-Type: application/pdf');
	header('Content-Disposition: inline; filename=separator.pdf');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . strlen($separator));
	ob_clean();
	flush();
	echo $separator;
}
function clean_string($str){
	return str_replace(array("ä", "ö", "ü", "Ä", "Ö", "Ü", "é", "á", "ó"), array("ae", "oe", "ue", "Ae", "Oe", "Ue", "e", "a", "o"), $str);
}
?>

