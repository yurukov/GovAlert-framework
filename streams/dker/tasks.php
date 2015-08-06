<?php

/*
links:
0: новини http://www.dker.bg/
1: събития http://www.dker.bg/kalendarbg.php
*/

function dker_Novini() {
  setSession(22,0);

  echo "> Проверявам за новини в КФН\n";
  
  $html = loadURL("http://www.fsc.bg/Novini-bg",0);
  if (!$html) return;
  $items = dker_xpathDoc($html,"//div[@class='s_news_listing']//h3/a");

  $query=array();
	foreach ($items as $item) {
    if (count($query)>10)
      break;
    $url = "http://www.fsc.bg".$item->getAttribute("href");
    $hash = md5($url);

    $title = $item->textContent;
    $title = dker_cleanText($title);
    $query[]=array($title,null,'now',$url,$hash);
  }

  echo "Възможни ".count($query)." нови новини\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function dker_xpathDoc($html,$q) {
  if (!$html)
    return array();
  $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
  $doc = new DOMDocument("1.0", "UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  $xpath = new DOMXpath($doc);  

  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}


function dker_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}

?>
