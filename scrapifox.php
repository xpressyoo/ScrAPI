<?php

/*
Author: Florian Bersier (xpressyoo)
Date:   September 2011
URL:    https://github.com/xpressyoo/ScrAPI

===========================================

This work is licensed under the Creative Commons Attribution 2.0 UK: England & Wales License. To view a copy of this license, visit http://creativecommons.org/licenses/by/2.0/uk/ or send a letter to Creative Commons, 444 Castro Street, Suite 900, Mountain View, California, 94041, USA.

*/

session_start(); ?>
<html>
<head>
<style type="text/css">
body{margin:4% 0 0 3%}
ul li{list-style:none;display:inline-block;margin:0;padding:0;font-family:Arial,sans-serif;font-size:16px}
li:first-child{margin-right:20px;border-right:1px solid #333;width:250px;color:#666}
li{list-style:none;display:inline-block;padding:5px 10px;line-height:30px;vertical-align:top}
h4{background-color:lightyellow;padding:3px 7px}p{font-size:14px;color:#999}
</style>
</head>
<body>
<?php

$errors=0;
$base = $_SERVER['HTTP_REFERER'];
$ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];
$language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$date = date('j/m/Y');
$time = date('G:i');
$etime = date('B');

if(isset($_POST['submit']))//Retrieve the URL from an INPUT field
{

//GET URL
$url = "https://addons.mozilla.org/en-US/firefox/addon/".$_POST['url'];

//REVIEWS AND PAGES
$file_string = file_get_contents($url.'/reviews/');
preg_match('#<b>(.*?)</b>#i', $file_string, $nb);
$nbreviews = str_replace(",", "",$nb[1]);
$pages0 = round(($nbreviews/20));
$pages1 = round(($nbreviews/20),1);
$pages = $pages0 - $pages1;

if ($pages >= 0){$pages = $pages0;}
else{$pages = $pages0 + 1;}


//USERS
$file_string2 = file_get_contents($url);
preg_match('#<b>(.*?)</b>#i', $file_string2, $users);
$nbusers = str_replace(",", "",$users[1]);

//RATIO
$ratio = $nbreviews/$nbusers;

//INITIALIZATION
$add = 0;

//LOOP REVIEWS SCRAPING

for ($i = 1; $i <= $pages; $i++) {
$oldSetting = libxml_use_internal_errors( true ); 
libxml_clear_errors(); 
 
$html = new DOMDocument(); 
$html->loadHtmlFile($url.'/reviews/?page='.$i); 
 
$xpath = new DOMXPath( $html ); 
$links = $xpath->query("//div[contains(@class, 'review')and 
               not(contains(@class,'reply'))]"); //Do not include the comments written by the addon's developer

$return = array();

foreach ( $links as $item ) {
	$newDom = new DOMDocument;
	$newDom->appendChild($newDom->importNode($item,true));
 
	$xpath = new DOMXPath( $newDom ); 
	$review = str_replace("\"","",trim($xpath->query("//p[@class='review-body']")->item(0)->nodeValue));
	//$review = "\"".$review."\",";
	$return[] = array($review,);
} 

// REVIEWS ARRAY
$return = print_r($return,true);
$return = htmlspecialchars($return);
$return = str_replace("[0]", "", $return);
$return = str_replace("Array", "", $return);
$return = str_replace("(", "", $return);
$return = str_replace(")", "", $return);
$return = str_replace("=", "", $return);
$return = str_replace("&gt;", "", $return);
$return = str_replace("[", "", $return);
$return = str_replace("]", "", $return);
$vowels = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", " ");
$return = str_replace($vowels, "", $return);
$strlen = strlen($return);
$add += $strlen;


libxml_clear_errors(); 
libxml_use_internal_errors( $oldSetting ); 
}

//DISPLAY RESULTS

$final = round($add/$nbreviews,6);

echo "<h4>".$url. "</h4><ul><li>Number of Reviews<br />Number of Pages<br />Number of Users<br />Ratio Reviews/Users<br />Total number of strings<br />Avg number of string per review</li><li>". $nbreviews."<br />". $pages . "<br />".$nbusers."<br />".$ratio."<br />". $add . "<br />". $final . "</li></ul><br /><p>Data retrieved on ".$date." at ".$time." from ".$browser."</p>";

}

?>
</body>
</html>
