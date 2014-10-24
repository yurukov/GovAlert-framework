<?php

/*
links:
0: новини http://www.tso.bg/default.aspx/novini/bg
1: съобщения http://www.tso.bg/default.aspx/saobshtenija/bg
*/

function tsoNovini() {
  echo "> Проверявам за новини в ЕСО\n";
  setSession(7,0);

  $html = loadURL("http://www.tso.bg/default.aspx/novini/bg",0);
  if (!$html) return;
  $xpath = tso_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//table[@id='ctl7_myDataList']//td");
  if (!$items) return;

  $query=array();
	foreach ($items as $item) {
    $title = $item->childNodes->item(1)->textContent;
    $title = "Новина: ".tso_cleanText($title);

    $description = $item->childNodes->item(4)->C14N();
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
    $description = tso_cleanText($description);

    $urlItems = $xpath->query(".//a",$item);
    if ($urlItems->length>0) {
      $url = $urlItems->item(0)->getAttribute("href");
      $url = mb_strpos($url,"http")!=0?"http://www.tso.bg$url":$url;
      $hash = md5($url);
    } else {
      $url = "http://www.tso.bg/default.aspx/novini/bg";
      $hash = md5($item->textContent);
    }

    $query[]=array($title,$description,"now",$url,$hash);
  }
  echo "Възможни ".count($query)." нови новини\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function tsoSaobshteniq() {
  echo "> Проверявам за съобщения в ЕСО\n";
  setSession(7,1);

  $html = loadURL("http://www.tso.bg/default.aspx/saobshtenija/bg",1);
  if (!$html) return;
  $xpath = tso_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//table[@id='ctl7_myDataList']//td");
  if (!$items) return;

  $query=array();
	foreach ($items as $item) {
    $title = $item->childNodes->item(1)->textContent;
    $title = "Съобщение: ".tso_cleanText($title);

    $description = $item->childNodes->item(4)->C14N();
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
    $description = tso_cleanText($description);

    $urlItems = $xpath->query(".//a",$item);
    if ($urlItems->length>0) {
      $url = $urlItems->item(0)->getAttribute("href");
      $url = mb_strpos($url,"http")!=0?"http://www.tso.bg$url":$url;
      $hash = md5($url);
    } else {
      $url = "http://www.tso.bg/default.aspx/saobshtenija/bg";
      $hash = md5($item->textContent);
    }

    $query[]=array($title,$description,"now",$url,$hash);
  }
  echo "Възможни ".count($query)." нови съобщения\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function tso_xpathDoc($html) {
  if (!$html)
    return false;
  $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
  $doc = new DOMDocument("1.0", "UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);  
}


function tso_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}

?>
