<?php

/*
links:
0: новини http://www.nap.bg/page?id=223
*/

function napAktualno() {
  setSession(23,0);

  echo "> Проверявам за съобщения на НАП\n";
  
  $html = loadURL("http://www.nap.bg/page?id=223",0);
  if (!$html) return;
  $items = nap_xpathDoc($html,"//div[@id='col2']//li");

  $query=array();
	foreach ($items as $item) {
    

    $date = $item->childNodes->item(2)->textContent;
    $date = text_bgMonth($date);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-2 week"))
      continue;

    $url = $item->childNodes->item(1)->getAttribute("onclick");
    $url = "http://www.nap.bg".substr($url,12,strpos($url,"'",12)-12);
    $hash = md5($url);

    $title = $item->childNodes->item(1)->textContent;
    $title = nap_cleanText($title);
    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови съобщения\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function nap_xpathDoc($html,$q) {
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


function nap_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}

?>
