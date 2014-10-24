<?php
/*

0 http://www.comdos.bg/

*/

function comdosResheniq() {
  echo "> Проверявам за решения на КомДос\n";
  setSession(5,0);

  $html = loadURL("http://www.comdos.bg/",0);
  if (!$html) return;
  $items = comdos_xpathDoc($html,"//div[@class='contentWrapper']//a");

  $query=array();
	foreach ($items as $item) {
    $text = $item->textContent;
    $text = comdos_cleanText($text);

    $url = "http://www.comdos.bg".$item->getAttribute("href");

    $hash = md5($url);

    $datepos = mb_strpos($text," от ")+4;
    $date = mb_substr($text,$datepos+6,4)."-".mb_substr($text,$datepos+3,2)."-".mb_substr($text,$datepos,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;

    $title = "Решение №".mb_substr($text,13,$datepos-17)."/".mb_substr($text,$datepos,5)." за ".mb_substr($text,$datepos+16);
    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови решения\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function comdos_xpathDoc($html,$q) {
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

function comdos_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}
?>
