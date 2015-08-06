<?php

/*
0: новини http://nek.bg/index.php/bg/za-nas/novini
1: обявление http://nek.bg/index.php/bg/
*/

function nekSaobshteniq() {
  setSession(6,0);

  echo "> Проверявам за съобщения на НЕК\n";
  
  $html = loadURL("http://nek.bg/index.php/bg/za-nas/novini",0);
  if (!$html) return;
  $xpath = nek_xpath($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@class='blog']//article[@class='nek-post']");
  if (!$items) return;

  $query=array();
	foreach ($items as $item) {
    if (count($query)>15)
      break;
    $hash = md5($item->textContent);

    $title = $item->childNodes->item(0)->childNodes->item(0)->textContent;
    $title = nek_cleanText($title);

    $date = $item->childNodes->item(0)->childNodes->item(1)->textContent;
    $date = mb_ereg_replace("Публикувано на ","",$date,"im");
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $description = trim($item->childNodes->item(1)->childNodes->item(0)->C14N());
    $description = nek_cleanDesc($description);
    $description = nek_cleanText($description);


    $media = array("image" => array());
    $items1 = false;
    if ($item->childNodes->item(1)->childNodes->length>1) {
      $url = 'http://nek.bg'.$item->childNodes->item(1)->childNodes->item(1)->firstChild->getAttribute("href");
      $html1 = loadURL($url);
      $xpath1 = nek_xpath($html1);
      if ($xpath1)
        $items1 = $xpath1->query("//div[@class='nek-article']//img");
    } else {
      $url = 'http://nek.bg/index.php/bg/za-nas/novini';
      $items1 = $xpath->query(".//img",$item->childNodes->item(1)->childNodes->item(0));
    }
    if ($items1)
        foreach ($items1 as $item1) {
          $imageurl = $item1->getAttribute("src");
          $imageurl = "http://nek.bg$imageurl";
          $media["image"][]=array(loadItemImage($imageurl),$null);
        }
    if (count($media["image"])==0)
      $media=null;
    else if (count($media["image"])==1)
      $media["image"]=$media["image"][0];

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }

  echo "Възможни ".count($query)." нови съобщения\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function nekObqvleniq() {
  setSession(6,1);

  echo "> Проверявам за обявления на НЕК\n";
  
  $html = loadURL("http://nek.bg/index.php/bg/",1);
  if (!$html) return;
  $xpath = nek_xpath($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@class='blog']//article[@class='nek-post' and div[@class='nek-postmetadataheader']]");
  if (!$items) return;

  $query=array();
	foreach ($items as $item) {
    if (count($query)>15)
      break;
    $hash = md5($item->textContent);

    $date = $item->childNodes->item(0)->childNodes->item(1)->textContent;
    $date = mb_ereg_replace("Публикувано на ","",$date,"im");
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;

    $title = trim($item->childNodes->item(1)->childNodes->item(0)->textContent);
    $title = nek_cleanText($title);

    if ($item->childNodes->item(1)->firstChild->firstChild->firstChild->nodeName=='a') {
      $url = 'http://nek.bg'.$item->childNodes->item(1)->firstChild->firstChild->firstChild->getAttribute("href");
    } else {
      $url = 'http://nek.bg/index.php/bg/';
    }

    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови обявления\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function nek_xpath($html,$q) {
  if (!$html)
    return array();
  $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
  $doc = new DOMDocument("1.0", "UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);  
}

function nek_cleanDesc($description) {
  $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description),"im");
  $description = mb_ereg_replace("\s?(title|name|style|class|id|target)=[\"'].*?[\"']\s?","",$description,"im");
  $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<span>[  ]*</span>","",$description,"im");
  return $description;
}

function nek_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}

?>
