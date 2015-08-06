<?php

/*

0: новини http://constcourt.bg/news
1: съобщения по дела http://constcourt.bg/caseannouncements

*/

function constcourtNovini() {
  echo "> Проверявам за новини в Конституционен съд\n";
  setSession(8,0);

  $html = loadURL("http://constcourt.bg/news",0);
  if (!$html) return;
  $items = constcourt_xpathDoc($html,"//div[@class='is-post is-post-excerpt']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->textContent);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $title = $item->childNodes->item(1)->firstChild->firstChild->textContent;
    $title = constcourt_cleanText($title);
    $title = "Новина: ".$title;
    
    $url = $item->childNodes->item(1)->firstChild->firstChild->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,0,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови новини\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function constcourtSaobchteniq() {
  echo "> Проверявам за съобщения в Конституционен съд\n";
  setSession(8,1);

  $html = loadURL("http://constcourt.bg/caseannouncements",1);
  if (!$html) return;
  $items = constcourt_xpathDoc($html,"//div[@class='is-post is-post-excerpt']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->textContent);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $title = $item->childNodes->item(1)->firstChild->firstChild->textContent;
    $title = constcourt_cleanText($title);
    $title = "Съобщение по дело: ".$title;
    
    $url = $item->childNodes->item(1)->firstChild->firstChild->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови съобщения\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}


/*
-----------------------------------------------------------------
*/

function constcourt_xpathDoc($html,$q) {
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


function constcourt_cleanText($text) {
  $text = text_cleanSpaces($text);
  $text=mb_ereg_replace("Конституционният? съд","КС",$text,"im");
	$text = html_entity_decode($text);
	return $text;
}

?>
