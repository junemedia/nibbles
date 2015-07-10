<?php

mysql_pconnect ("localhost", "root", "8tre938G");
mysql_select_db ("chewonthat");

$root_url = "http://www.mexicanrecipe4living.com";
$table_content = 'MexicanR4L_content';
$table_img = 'stg_img';
$xml_file_name = 'MR4L_XML_Feed.xml';
$title = 'MexicanRecipe4Living';
$desc = 'MexicanRecipe4Living';


// ADJUST value for $catid 
// all articles are contained in "Recipe Collections" category which is catid = 74
// all recipes are contained in all categories EXCEPT "Recipe Collections" category
$catid = '74';  // articles category id for mexicanrecipe4living

$get_content = "SELECT * FROM $table_content WHERE catid='$catid' ORDER BY id DESC LIMIT 5";
$result = mysql_query($get_content);
echo mysql_error();
$xml_feed = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>
<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\"
	xmlns:image=\"http://purl.org/rss/1.0/modules/image/\"
	xmlns:dc=\"http://purl.org/dc/elements/1.1/\">
<channel>
<atom:link href=\"$root_url/MR4L_XML_Feed.xml\" rel=\"self\" type=\"application/rss+xml\" />
	<title>$title</title>
  <link>$root_url</link>
  <description>$desc</description>";
while ($row = mysql_fetch_object($result)) {
	//$date = substr($row->created,0,10);
	//$date = str_replace('-','',$date);
	$body = strip_tags($row->introtext);
	$body = str_replace("’","'",$body);
	$body = str_replace("  "," ",$body);
	$body = trim($body);
	$id = $row->id;
	
	
	$date = str_replace(" ","T",$row->created);
	
	//2002-10-02T10:00:00-05:00
	//$get_img = "SELECT path FROM $table_img WHERE id='$id'";
	//http://stg.fitandfabliving.com/images/stories/beach%20hair.jpg
	//http://stg.fitandfabliving.com/images/stories/fruit/strawberry.jpg
	$img_url = "http://www.mexicanrecipe4living.com/images/stories/marg.jpg";
	
	$xml_feed .= "
	<item>
		   	<title>$row->title</title>
		   	<link><![CDATA[$root_url/index.php?option=com_content&id=$id]]></link>
		   	<guid><![CDATA[$root_url/index.php?option=com_content&id=$id]]></guid>
		   	<description><![CDATA[$body]]></description>
		   	<dc:date>{$date}-06:00</dc:date>
	</item>";
}

$xml_feed .= "
</channel>
</rss>";


// open some file to write to
$handle = fopen($xml_file_name, 'w');

fwrite($handle, $xml_feed);

// close the file handler
fclose($handle);

?>
