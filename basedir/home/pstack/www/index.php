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



#ini_set('display_errors', 'On');
#error_reporting(E_ALL);

//if ($_SERVER['SERVER_NAME'] == 'pstack.local') {
	//header("Location: http://".$_SERVER['SERVER_ADDR']);
	//die();
//}

@ini_set('default_charset', 'UTF-8');

//This is required because of a bug in pathinfo() resp. basename() that causes filename starting with non ascii characters to be corrupted
setlocale(LC_ALL,'en_US.UTF-8');
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

//$basepath="/home/pstack/Documents";
$basepath=[
"Document" => "/home/pstack/Documents",
"Picture" => "/home/pstack/Multimedia",
"Video" => "/home/pstack/Multimedia",
];

$con=mysqli_connect("127.0.0.1","root","$sqlPassword","pStack");
// Check connection
if (mysqli_connect_errno()) {
#	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
mysqli_query($con,"SET NAMES 'utf8'");


$ts = gmdate("D, d M Y H:i:s") . " GMT";
header("Expires: $ts");
header("Last-Modified: $ts");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");



if ($request['action'] == 'find')
{
	find_stuff($con,$request['query']);
}
elseif ($request['action'] == 'acknowledge')
{
        system("echo -n \"Scan\" > /tmp/FriendlyStack.action");
        header('Location: status.php');
}
elseif ($request['action'] == 'Scan')
{
        system("echo -n \"Scan\" > /tmp/FriendlyStack.action");
        header('Location: scannerStatus.html');
}
elseif ($request['action'] == 'checkScanner')
{
        if(file_exists("/tmp/FriendlyStack.scanner") && !(file_exists("/tmp/FriendlyStack.error") || file_exists("/tmp/FriendlyStack.scanning") || file_exists("/tmp/FriendlyStack.action"))) echo "1"; else echo "0";
}
elseif ($request['action'] == 'delete')
{
	//UPDATE `pStack`.`Documents` SET `path`='/home/picture_flat/flat2/2015/02/###Deleted###2015-02-12 10-14-49 0000_P1040585.JPG' WHERE `ID`='250388';
	$query = "SELECT * FROM Documents where ID='".$request['ID']."'";
	$result = mysqli_query($con,$query);
	$row = mysqli_fetch_assoc($result);
	//rename($basepath.$row['relpath'],$basepath.dirname($row['relpath'])."/###Deleted###".basename($row['relpath']));
	rename($basepath[$row['Media']].$row['relpath'],$basepath[$row['Media']].dirname($row['relpath'])."/###Deleted###".basename($row['relpath']));
	$query="UPDATE `pStack`.`Documents` SET `path`='".dirname($row['path'])."/###Deleted###".basename($row['path'])."', Deleted=1 WHERE `ID`='".$request['ID']."'";
	#$result = mysqli_query($con,utf8_encode ($query));
	$result = mysqli_query($con,$query);

	find_stuff($con,$request['query']);
}
elseif ($request['action'] == 'download')
{
	$query = "SELECT * FROM Documents where ID='".$request['ID']."'";
	$result = mysqli_query($con,$query);
	$row = mysqli_fetch_assoc($result);
        if($request['media'] == 'Video') {
        //$length   = sprintf("%u", filesize("/home/pstack/Transcoded_Videos/".basename($row['path']).".mp4"));
        $length   = sprintf("%u", filesize("/home/pstack/Previews/".$request['ID'].".mp4.mp4"));
        } else {
        if($request['media'] == 'Document') {
        $length   = sprintf("%u", filesize($row['path']));
        } else {
        $length   = sprintf("%u", filesize($row['path']));
        }
        }
        if($request['media'] == 'Document') {
              if (pathinfo(utf8_decode($row['path']),PATHINFO_EXTENSION) == "pdf")
              {
                  header('Content-Type: application/pdf');
                  header('Content-Disposition: inline; filename='.utf8_decode(basename($row['path'])));
              } else {
                  header('Content-Type: application/octet-stream charset=utf-8');
                  header('Content-Disposition: attachment; filename='.utf8_decode(basename($row['path'])));
              }
        } elseif ($request['media'] == 'Picture') {
              header('Content-Type: image/jpeg');
              header('Content-Disposition: inline; filename='.utf8_decode(basename($row['path'])));
              //header('Content-Disposition: attachment; filename='.utf8_decode(basename($row['path'])));
        } elseif ($request['media'] == 'Video') {
              header('Content-Type: video/mp4');
        }
        header('Content-Length: ' . $length);
        header('Pragma: no-cache');
while (ob_get_level()) {
    ob_end_clean();
} 
        if($request['media'] == 'Video') {
        //readfile("/home/pstack/Transcoded_Videos/".basename($row['path']).".mp4");








//$file = "/home/pstack/Transcoded_Videos/".basename($row['path']).".mp4";
$file = "/home/pstack/Previews/".$request['ID'].".mp4.mp4";
$fp = @fopen($file, 'rb');

$size   = filesize($file); // File size
$length = $size;           // Content length
$start  = 0;               // Start byte
$end    = $size - 1;       // End byte

header('Content-type: video/mp4');
//header("Accept-Ranges: 0-$length");
header("Accept-Ranges: bytes");
if (isset($_SERVER['HTTP_RANGE'])) {

    $c_start = $start;
    $c_end   = $end;

    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    if ($range == '-') {
        $c_start = $size - substr($range, 1);
    }else{
        $range  = explode('-', $range);
        $c_start = $range[0];
        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    }
    $c_end = ($c_end > $end) ? $end : $c_end;
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    $start  = $c_start;
    $end    = $c_end;
    $length = $end - $start + 1;
    fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
}
header("Content-Range: bytes $start-$end/$size");
header("Content-Length: ".$length);


$buffer = 1024 * 8;
while(!feof($fp) && ($p = ftell($fp)) <= $end) {

    if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
    }
    set_time_limit(0);
    echo fread($fp, $buffer);
    flush();
}

fclose($fp);
exit();








        } else {
        if($request['media'] == 'Document') {
        readfile($row['path']);
        } else {
        readfile($row['path']);
        }
        }
        ob_end_flush();
}
elseif ($request['action'] == 'preview')
{
	//$query = "SELECT * FROM Documents where ID='".$request['ID']."'";
	//$result = mysqli_query($con,$query);
	//$row = mysqli_fetch_assoc($result);
        //$length   = sprintf("%u", filesize("/home/pstack/Previews/".$request['ID'].".png"));
        header('Content-Type: image/png');
        //header('Content-Length: ' . $length);
        header('Content-Disposition: inline');
        while (ob_get_level()) {
            ob_end_clean();
        } 
        readfile("/home/pstack/Previews/".$request['ID'].".png");
        ob_end_flush();
}
else
{
	searchform($request['query']);
}
mysqli_close($con);


function searchform($web_query)
{
        //exec("/usr/bin/pgrep -F /var/run/pstack.pid",$schrott,$process_status);
        //if ($process_status) {$bg_color='#ff0000'; $error_message="<pre>FriendlyStack service is down, unplug and replug the control unit</pre>";} else {$bg_color='#527a7a'; $error_message="";}
        //$bg_color='#527a7a';
        $bg_color='#ffffff';
	echo "
		<html>
		<head>

		<title>Friendly Stack</title>
		<!-----Including CSS for different screen sizes----->
		<link rel=\"stylesheet\" type=\"text/css\" href=\"responsiveform.css\">
		<link rel=\"stylesheet\" type=\"text/css\" href=\"main.css\">
		<link rel=\"stylesheet\" type=\"text/css\" href=\"iconfont/material-icons.css\">
		<link rel=\"stylesheet\" media=\"screen and (min-width: 601px)\" href=\"responsiveform1.css\" />
		<link rel=\"stylesheet\" media=\"screen and (max-width: 600px) and (min-width: 351px) and handheld\" href=\"responsiveform2.css\" />
		<link rel=\"stylesheet\" media=\"screen and (max-width: 350px) and handheld\" href=\"responsiveform3.css\" />
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">

<style>
.clear { clear: both; }

div.polaroido {
  width:30%;
  display: flex;
  flex-direction: column;
  background-color: white;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
  margin-bottom: 0px;
  margin-top: 0px;
overflow:auto;
}

div.polaroid {
  width:50%;
  display: flex;
  flex-direction: column;
  background-color: white;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
  margin-bottom: 20px;
  margin-top: 20px;
overflow:auto;
}


@media only screen and (max-width: 999px){
  div.polaroid {
    width:80%;
    display: flex;
    flex-direction: column;
    background-color: white;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    margin-bottom: 20px;
    margin-top: 20px;
  overflow:auto;
  }
  div.polaroido {
    width:80%;
    display: flex;
    flex-direction: column;
    background-color: white;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    margin-bottom: 20px;
    margin-top: 20px;
  overflow:auto;
  }
}

@media only screen and (max-width: 800px){
  div.polaroid {
    width:92%;
    display: flex;
    flex-direction: column;
    background-color: white;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    margin-bottom: 20px;
    margin-top: 20px;
  overflow:auto;
  }
  div.polaroido {
    width:92%;
    display: flex;
    flex-direction: column;
    background-color: white;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    margin-bottom: 20px;
    margin-top: 20px;
  overflow:auto;
  }
}

div.container {
  text-align: left;
  background-color: #10322d;
  color: white;
  font-family: helvetica;
  padding: 10px 20px;
}
#bottommenu {
  position: fixed;
  top: 0;
  width: 100%;
  height: 80;
  background: #10322d;  
  -webkit-transition: top 1s;
  transition: top 1s;
}
div.menu {
  padding: 14px 0px;
}
</style>

<script src=\"jquery.min.js\"></script>
<script>
$(document).ready(function() {
$.ajaxSetup({cache: false}); // fixes older IE caching bug
setInterval(function(){
    $(\"#status\").attr(\"src\", \"status.php?\"+new Date().getTime());
},500); //reload every 2000ms
});
</script>

</head>
<header id=\"header\" class=\"header header--fixed hide-from-print\" role=\"banner\">
<iframe id=\"status\" name=\"status\" marginwidth=\"0\" marginheight=\"0\" width=\"20\" height=\"70\" scrolling=\"no\" frameborder=0 src=\"status.php\" align=\"left\">$error_message</iframe><nobr><div class=\"menu\"><form action=\"/\" method=\"get\"><input id=\"query\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\" name=\"query\" type=\"text\" size=\"20\" maxlength=\"99\" value=\"".htmlentities($web_query)."\" class=\"tftextinput\"><input type=\"submit\" name=\"action\" value=\"find\" class=\"tfbutton\">&nbsp;&nbsp;&nbsp;<a href=\"/destinations.php?tab=1\"><i class=\"material-icons md-24 md-light\" valign=\"middle\">settings</i></a></nobr>
<input name=\"action\" type=\"hidden\" value=\"find\">
</form>
</div>
<iframe src=\"scannerStatus.html\" marginwidth=\"0\" marginheight=\"0\" scrolling=\"no\" frameborder=0 width=\"100%\" height=\"38\" allowtransparency=\"true\" style=\"background: #FFFFFF;\"></iframe>
</header>
<script src=\"headroom.js\"></script>
<script>
(function() {
    var header = document.querySelector(\"#header\");

    if(window.location.hash) {
      header.classList.add(\"headroom--unpinned\");
    }

    var headroom = new Headroom(header, {
        tolerance: {
          down : 10,
          up : 20
        },
        offset : 205
    });
    headroom.init();

}());
</script>

<script>
var tags = [ \"today\", \"yesterday\", \"tweek\", \"lweek\", \"rechnung\", \"kontoauszug\", \"honorar\" ];
$( \"#query\" ).autocomplete({
	source: function( request, response ) {
		var matcher = new RegExp( \"^\" + $.ui.autocomplete.escapeRegex( request.term ), \"i\" );
		response( $.grep( tags, function( item ){
			return matcher.test( item );
}) );
}
});

var lastScrollTop = 0;

window.addEventListener(\"scroll\", function(){  
   var st = window.pageYOffset || document.documentElement.scrollTop;  
   if (st > lastScrollTop){
       document.getElementById(\"bottommenu\").style.top = \"-100%\";
   } else {
      document.getElementById(\"bottommenu\").style.top = \"0\";
   }
   lastScrollTop = st;
}, false);

</script>
<body bgcolor=\"$bg_color\"><br><br><br><br><br>";

}

function build_query($web_query)
{
	$months = array(
		"january" => 1,
		"januar" => 1,
		"jan" => 1,
		"february" => 2,
		"februar" => 2,
		"feb" => 2,
		"march" => 3,
		"märz" => 3,
		"mar" => 3,
		"april" => 4,
		"apr" => 4,
		"may" => 5,
		"mai" => 5,
		"june" => 6,
		"juni" => 6,
		"jun" => 6,
		"july" => 7,
		"juli" => 7,
		"jul" => 7,
		"august" => 8,
		"aug" => 8,
		"september" => 9,
		"sep" => 9,
		"october" => 10,
		"oktober" => 10,
		"oct" => 10,
		"okt" => 10,
		"november" => 11,
		"november" => 11,
		"nov" => 11,
		"dezember" => 12,
		"december" => 12,
		"dec" => 12,
		"dez" => 12,
	);
	$query = "SELECT HIGH_PRIORITY * FROM Documents where";
	//$web_query = str_replace(array("”", "“","„"), '"', $web_query);
	$web_query = str_replace(array("\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x9e"), '"', $web_query);
	preg_match_all('`(-*"[^"]*")`', $web_query, $quoted_elements);
	//$elements=explode(' ',preg_replace('`".*"`','',$web_query));
	$elements=preg_split('/\s/',preg_replace('!\s+!',' ',preg_replace('`-*".*"`','',$web_query)));
	//$elements=explode (' ',$web_query);
	//foreach (explode (' ',$web_query) as $key => $item) {
	//foreach ( $quoted_elements[0] as $key => $item) {
	foreach ( array_merge($quoted_elements[0],$elements) as $key => $item) {
		if ($key > 0) {$and=" and";} else {$and="";}
			if (preg_match("/^\-.*/i",$item)) {$not="not";$item=preg_replace("/^\-/","",$item,1);} else {$not="";}
				if ((preg_match("/^Y\:\d{4}$/i",$item)) || ((preg_match("/^\d{4}$/i",$item)) && ($item > 1900) && ($item<2036)))  {$item=preg_replace("/^Y\:/i","",$item,1); $query = $query . "$and $not year(ContentDate)=$item";}
				//elseif ((preg_match("/^M\:\d{1,2}$/i",$item)) || ((preg_match("/^\d{1,2}$/i",$item)) && ($item >= 1) && ($item <= 12))) {$item=preg_replace("/^M\:/i","",$item,1); $item=sprintf("%02d",$item); $query = $query . "$and $not month(ContentDate)=$item";}
				elseif (preg_match("/^jan(?:uar(?:y)?)?$|^feb(?:ruar(?:y)?)?$|^mar(?:ch)?$|^märz$|^apr(?:il)?$|^ma(?:y|i)?$|^jun(?:e|i)?$|^jul(?:y|i)?$|^aug(?:ust)?$|^sep(?:tember)?$|^okt(?:ober)?$|^oct(?:ober)?$|^nov(?:ember)?$|^dec(?:ember)?$|^dez(?:ember)?$/i",$item)) {$query = $query . "$and $not month(ContentDate)=".$months[strtolower($item)];}
				elseif (preg_match("/^D\:\d{1,2}/i",$item)) {$item=preg_replace("/^D\:/i","",$item,1); $item=sprintf("%02d",$item); $query = $query . "$and $not day(ContentDate)=$item";}
				elseif (preg_match("/^\d{1,2}$/i",$item)) {$item=sprintf("%02d",$item); $query = $query . "$and $not day(ContentDate)=$item";}
					//elseif (preg_match("/^video$/i",$item)) {$query = $query . "$and $not path like '%mp4'";}
				elseif (preg_match("/^video$|^movie$|^film$/i",$item)) {$query = $query . "$and $not Media='Video'";}
					//elseif (preg_match("/^document$/i",$item)) {$query = $query . "$and $not path like '%pdf'";}
				elseif (preg_match("/^document$|^dokument$/i",$item)) {$query = $query . "$and $not Media='Document'";}
					//elseif (preg_match("/^picture$/i",$item)) {$query = $query . "$and $not path like '%jpg'";}
				elseif (preg_match("/^picture(?:s)?$|^image(?:s)?$|^bild(?:er)?$|^photo(?:s)?$|^foto(?:s)?$/i",$item)) {$query = $query . "$and $not Media='Picture'";}
				elseif (preg_match("/^today$|^heute$/i",$item)) {$query = $query . "$and $not date(ContentDate)='".date("Y-m-d") ."'";}
				elseif (preg_match("/^winter$/i",$item)) {$query = $query . "$and $not ((month(ContentDate) BETWEEN 1 AND 2) or (month(ContentDate)=12))";}
				elseif (preg_match("/^summer$|^sommer$/i",$item)) {$query = $query . "$and $not (month(ContentDate) BETWEEN 6 AND 8)";}
				elseif (preg_match("/^spring$|^frühling$|^frühjahr$/i",$item)) {$query = $query . "$and $not (month(ContentDate) BETWEEN 3 AND 5)";}
				elseif (preg_match("/^autumn$|^herbst$/i",$item)) {$query = $query . "$and $not (month(ContentDate) BETWEEN 9 AND 11)";}
				elseif (preg_match("/^yesterday$|^gestern$/i",$item)) {$query = $query . "$and $not date(ContentDate)='".date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")))."'";}
				elseif (preg_match("/^tweek$/i",$item)) {$query = $query . "$and $not yearweek(ContentDate,3)=".date("oW");}
				elseif (preg_match("/^lweek$/i",$item)) {if(date("W") == 1) {$query = $query . "$and $not yearweek(ContentDate,3)=".(date("o")-1)."53";} else $query = $query . "$and $not yearweek(ContentDate,3)=".date("o").(date("W")-1);}
				elseif (preg_match("/^tmonth$/i",$item)) {$query = $query . "$and $not (year(ContentDate)=".date("Y")." and month(ContentDate)=".date("m").")";}
				elseif (preg_match("/^lmonth$/i",$item)) {if(date("m") == 1) {$query = $query . "$and $not (year(ContentDate)=".(date("Y")-1)." and month(ContentDate)=12)";} else $query = $query . "$and $not (year(ContentDate)=".date("Y")." and month(ContentDate)=".(date("m")-1).")";}
				elseif (preg_match("/^(\d{1,2})\.(\d{1,2})\.(\d{4})/i",$item,$matches)) {$item=preg_replace("/^DD\:/i","",$item,1); $item=sprintf("%02d-%02d-%d",$matches[3],$matches[2],$matches[1]); $query = $query . "$and $not date(ContentDate)='$item'";}
				elseif (preg_match("/^\"(.*)\"$/i",$item,$matches)){$query = $query . "$and $not (content like '%".$matches[1]."%' or relpath like '%$matches[1]%')";}
				elseif ($item == '') {}
				else
					//{$query = $query . "$and content $not like '%$item%'";}
				{$query = $query . "$and $not (content like '%$item%' or relpath like '%$item%')";}
	}
	#$query = $query . " and Deleted=0 order by ContentDate desc";
	$query = $query . " and not (Path like '%###Deleted###%') order by ContentDate desc";
	//echo "$query\n";
	//print_r(array_merge($quoted_elements[0],$elements));
	return($query);


}

function build_aggregation_query($web_query)
{
		$query = "SELECT HIGH_PRIORITY count(ID) as rows, year(ContentDate) as year, month(ContentDate) as month FROM Documents where";
		foreach (explode (' ',$web_query) as $key => $item) {
			if ($key > 0) {$and=" and";} else {$and="";}
				if (preg_match("/^\-.*/i",$item)) {$not="not";$item=preg_replace("/^\-/","",$item,1);} else {$not="";}
					if (preg_match("/^Y\:\d{4}/i",$item)) {$item=preg_replace("/^Y\:/i","",$item,1); $query = $query . "$and $not year(ContentDate)=$item";}
					elseif (preg_match("/^M\:\d{1,2}/i",$item)) {$item=preg_replace("/^M\:/i","",$item,1); $item=sprintf("%02d",$item); $query = $query . "$and $not month(ContentDate)=$item";}
					elseif (preg_match("/^D\:\d{1,2}/i",$item)) {$item=preg_replace("/^D\:/i","",$item,1); $item=sprintf("%02d",$item); $query = $query . "$and $not day(ContentDate)=$item";}
					elseif (preg_match("/^video$/i",$item)) {$query = $query . "$and $not path like '%mp4'";}
					elseif (preg_match("/^document$/i",$item)) {$query = $query . "$and $not path like '%pdf'";}
					elseif (preg_match("/^picture$/i",$item)) {$query = $query . "$and $not path like '%jpg'";}
					elseif (preg_match("/^today$/i",$item)) {$query = $query . "$and $not date(ContentDate)='".date("Y-m-d") ."'";}
					elseif (preg_match("/^yesterday$/i",$item)) {$query = $query . "$and $not date(ContentDate)='".date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")))."'";}
					elseif (preg_match("/^tweek$/i",$item)) {$query = $query . "$and $not yearweek(ContentDate,3)=".date("YW");}
					elseif (preg_match("/^lweek$/i",$item)) {if(date("W") == 1) {$query = $query . "$and $not yearweek(ContentDate,3)=".(date("Y")-1)."53";} else $query = $query . "$and $not yearweek(ContentDate,3)=".date("Y").(date("W")-1);}
					elseif (preg_match("/^(\d{1,2})\.(\d{1,2})\.(\d{4})/i",$item,$matches)) {$item=preg_replace("/^DD\:/i","",$item,1); $item=sprintf("%02d-%02d-%d",$matches[3],$matches[2],$matches[1]); $query = $query . "$and $not date(ContentDate)='$item'";} else
					{$query = $query . "$and content $not like '%$item%'";}
		}
		$query = $query . " group by year, month order by year, month";
		return($query);
}

function list_results($result,$web_query)
{

		while($row = mysqli_fetch_array($result)) {
			if ($row['Media']=='Video')
			{
				preg_match("/^.*\/(.*)$/i",$row['path'],$matches);
				//echo "<center><a href=\"".htmlentities(preg_replace("/\#/","%23","/Videos/$matches[1].mp4"))."\" target=\"_blank\">";
				echo '<center><a href="/?action=download&ID='.$row['ID'].'&media='.$row['Media'].'&query='.htmlentities($web_query).'" target="_blank">';
				$class="Video";
			}
			elseif ($row['Media']=='Document')
			{
				//echo "<center><a href=\"".htmlentities(preg_replace("/\#/","%23",preg_replace("/^\/home\/pstack/","",$row['path'],1)))."\" target=\"_blank\">";
				//echo '<center><a href="/?action=download&ID='.$row['ID'].'&query='.htmlentities($web_query).'&media='.$row['Media'].' target="_blank">';
				echo '<center><a href="/?action=download&ID='.$row['ID'].'&media='.$row['Media'].'&query='.htmlentities($web_query).'">';
				$class="Document";
			}
			elseif ($row['Media']=='Picture')
			{
				//echo '<center><a href="/?action=download&ID='.$row['ID'].'&query='.htmlentities($web_query).'&media='.$row['Media'].' target="_blank">';
				echo '<center><a href="/?action=download&ID='.$row['ID'].'&media='.$row['Media'].'&query='.htmlentities($web_query).'" target="_blank">';
				/*if (preg_match("/^\/home\/pstack\/Multimedia/",$row['path'])) {
					echo "<center><a href=\"".htmlentities(preg_replace("/\#/","%23",preg_replace("/^\/home\/pstack\/Multimedia/","/Pictures",$row['path'],1)))."\">";} elseif (preg_match("/^\/home\/pstack/",$row['path'])) {
				echo "<center><a href=\"".htmlentities(preg_replace("/\#/","%23",preg_replace("/^\/home\/pstack/","",$row['path'],1)))."\" target=\"_blank\">";
				}*/
				$class="Picture";
			}
			if ($row['Media'] == "Document")
			{
			echo '<div class="polaroid"><img src="/?action=preview&ID='.$row['ID'].'" class="'.$class.'" style="clear: left;"></a><div class="container">'.$row['relpath'].'</div></div><a href="/?action=delete&ID='.$row['ID'].'&query='.htmlentities($web_query).'" onclick="return confirm(\'Are you sure you want to delete this item?\');"><i class="material-icons md-36 md-dark-green">delete</i></a></center><br>';
			} elseif ($row['Media'] == "Picture") {
			echo '<img src="/?action=preview&ID='.$row['ID'].'" class="'.$class.'"></a><br><a href="/?action=delete&ID='.$row['ID'].'&query='.htmlentities($web_query).'" onclick="return confirm(\'Are you sure you want to delete this item?\');"><i class="material-icons md-36 md-dark-green">delete</i></a></center><br>';
			} else {
			echo '<div class="polaroido"><img src="/?action=preview&ID='.$row['ID'].'" class="'.$class.'" style="clear: left;"></a><div class="container">'.$row['relpath'].'</div></div><br><a href="/?action=delete&ID='.$row['ID'].'&query='.htmlentities($web_query).'" onclick="return confirm(\'Are you sure you want to delete this item?\');"><i class="material-icons md-36 md-dark-green">delete</i></a></center><br>';
			}
		}

}

function find_stuff($con,$web_query)
{
	searchform($web_query);
	$result = mysqli_query($con,build_query($web_query));
	//echo "rows: ".mysqli_num_rows($result)."<br>";
	if (mysqli_num_rows($result) > 100000000)
	{
		$result = mysqli_query($con,build_aggregation_query($web_query));
		$year=0;
		while($row = mysqli_fetch_array($result))
		{
			if ($year != $row['year']){$year=$row['year'];echo $row['year']."<br>";}
				echo $row['month']." Documents:".$row['rows']."<br>";
		}
		echo $query;
	}
	else
	{
		list_results($result,$web_query);
	}
}
?> 


