<?php

/*
links:
0: новини http://www.nek.bg/cgi?d=101
*/

function nekSaobshteniq() {
  setSession(6,0);

  echo "> Проверявам за съобщения на НЕК\n";
  
  $html = loadURL("http://www.nek.bg/cgi?d=101",0);
  if (!$html) return;
  $html = mb_convert_encoding($html, "utf8","cp1251");
  $items = nek_xpathDoc($html,"//div[@class='subpage']//li");

  $query=array();
	foreach ($items as $item) {
    if (count($query)>15)
      break;
    $hash = md5($item->textContent);

    $date = $item->childNodes->item(0)->textContent;
    $date = mb_substr($date,9,4)."-".mb_substr($date,5,2)."-".mb_substr($date,1,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $url = $item->childNodes->item(2)->getAttribute("href");
    $title = $item->childNodes->item(2)->textContent;
    $title = mb_ereg_replace("П Р Е С С Ъ О Б Щ Е Н И Е","Прессъобщение",$title,"im");
    $title = mb_ereg_replace("О Б Я В Л Е Н И Е","Обявление",$title,"im");
    $title = "Съобщение: ".nek_cleanText($title);
    $query[]=array($title,null,$date,"http://www.nek.bg$url",$hash);
  }

  echo "Възможни ".count($query)." нови съобщения\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function nek_xpathDoc($html,$q) {
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


function nek_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}

?>
