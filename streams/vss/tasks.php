<?php

/*

0: дневен ред http://www.vss.justice.bg/page/view/1554
1: протоколи http://www.vss.justice.bg/page/view/2046
2: съобщения http://www.vss.justice.bg/page/view/1524
3: кратки протоколи http://www.vss.justice.bg/page/view/1484
4: новини http://www.vss.justice.bg/page/view/2574
5: регистри http://www.vss.justice.bg/page/view/2615

*/

function vssDnevenRed() {
  echo "> Проверявам за дневен ред на ВСС\n";
  setSession(9,0);

  $baseurl="http://www.vss.justice.bg";

  $html = loadURL($baseurl."/page/view/1554",0);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='big_right_column']//ul[@class='styled_ul']/li/a[text()='".date("Y")."']");
  if ($items->length!=1) {
    reportError("Грешка при четенето на страницата за дневен ред на ВСС.");
    return;
  }
  $yearurl=$items->item(0)->getAttribute("href");
  if (!$baseurl) {
    reportError("Грешка при четенето на страницата за дневен ред на ВСС.");
    return;
  }

  $html = loadURL($baseurl.$yearurl,1);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='big_right_column']//ul[@class='styled_ul']/li[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->textContent);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = $baseurl.$item->childNodes->item(1)->getAttribute("href");
    $hash = md5($url);

    $title = $item->childNodes->item(1)->textContent;
    $title = vss_cleanText($title);
    $title = mb_convert_encoding($title,"ISO-8859-1","UTF-8");
    $title = mb_ereg_replace("№ ","№",$title,"im");
    $title = "Публикуван е дневният ред за заседание $title на ".trim($item->childNodes->item(4)->textContent);

    $query[]=array($title,null,$date,$url,$hash);
  }
  echo "Възможни ".count($query)." нов запис за дневен ред\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function vssProtokol() {
  echo "> Проверявам за протоколи на ВСС\n";
  setSession(9,1);

  $baseurl="http://www.vss.justice.bg";

  $html = loadURL($baseurl."/page/view/2046",2);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='big_right_column']//ul[@class='styled_ul']/li/a[text()='".date("Y")."']");
  if ($items->length!=1) {
    reportError("Грешка при четенето на страницата за протоколи на ВСС.");
    return;
  }
  $yearurl=$items->item(0)->getAttribute("href");
  if (!$baseurl) {
    reportError("Грешка при четенето на страницата за протоколи на ВСС.");
    return;
  }

  $html = loadURL($baseurl.$yearurl,3);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='with_left_menu_content make_it_arial']//a");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->textContent);
    $date = mb_substr($date,-4)."-".mb_substr($date,-7,2)."-".mb_substr($date,-9,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;

    $title = $item->textContent;
    $title = vss_cleanText($title);
    $title = mb_ereg_replace("/"," от ",$title,"im");
    $title = "Публикуван е пълният протокол от заседание №".$title;

    $url = $baseurl.$item->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,$date,$url,$hash);
  }
  echo "Възможни ".count($query)." нови протоколи\n";
  $query=array_reverse($query);
  $itemids = saveItems($query);
  queueTweets($itemids);
}


function vssSaobshtenia() {
  echo "> Проверявам за съобщения на ВСС\n";
  setSession(9,2);

  $baseurl = "http://www.vss.justice.bg";
  $html = loadURL($baseurl."/page/view/1524",4);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='right_content_holder']//li[@class='row w100_float_none']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->textContent);
    $date = vss_cleanText($date);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = $baseurl.$item->childNodes->item(1)->getAttribute("href");
    $hash = md5($url.$title);
    if (!checkHash($hash))
      continue;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = vss_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@class='right_content_holder']//div[@class='with_left_menu_content']");
    if ($items1->length!=1) continue;

    $title = $items1->item(0)->textContent;
    $title = vss_cleanText($title);
    $title = mb_convert_encoding($title,"ISO-8859-1","UTF-8");
    $title = mb_ereg_replace("Висшият? съдебен съвет \(ВСС\)|Висшият? съдебен съвет /ВСС/|Висшият? съдебен съвет|Висшият? Съдебен Съвет|ВИСШИЯТ? СЪДЕБЕН СЪВЕТ","ВСС",$title);
    $title = mb_ereg_replace("\?","",$title);
    $title = mb_substr($title,0,160);
  

    $description = trim($items1->item(0)->C14N());
    $description = mb_convert_encoding($description,"ISO-8859-1","UTF-8");
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|\?","",$description);
    $description = text_cleanSpaces($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());

    $imgs = $xpath1->query(".//img",$items1->item(0));
    foreach ($imgs as $img) {
      $imgsrc = $img->getAttribute("src");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc=$baseurl.$imgsrc;
      $imageurl = loadItemImage($imgsrc);
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови съобщения\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function vssKratkiProtokoli() {
  echo "> Проверявам за кратки протоколи на ВСС\n";
  setSession(9,3);

  $baseurl="http://www.vss.justice.bg";

  $html = loadURL($baseurl."/page/view/1484",5);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='big_right_column']//ul[@class='styled_ul']/li/a[text()='".date("Y")."']");
  if ($items->length!=1) {
    reportError("Грешка при четенето на страницата за кратки протоколи на ВСС.");
    return;
  }
  $yearurl=$items->item(0)->getAttribute("href");
  if (!$baseurl) {
    reportError("Грешка при четенето на страницата за кратки протоколи на ВСС.");
    return;
  }
  $html = loadURL($baseurl.$yearurl,6);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='big_right_column']//ul[@class='styled_ul']/li[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->textContent);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = $baseurl.$item->childNodes->item(1)->getAttribute("href");
    $hash = md5($url);

    $title = $item->childNodes->item(1)->textContent;
    $title = vss_cleanText($title);
    $title = mb_convert_encoding($title,"ISO-8859-1","UTF-8");
    $title = mb_ereg_replace("№ ","№",$title,"im");
    $title = "Публикуван е краткият протокол от заседание $title на ".trim($item->childNodes->item(4)->textContent);

    $query[]=array($title,null,$date,$url,$hash);
  }
  echo "Възможни ".count($query)." нови кратки протоколи\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function vssNovini() {
  echo "> Проверявам за новини на ВСС\n";
  setSession(9,4);

  $baseurl = "http://www.vss.justice.bg";
  $html = loadURL($baseurl."/page/view/2574",7);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='right_content_holder']//li[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->textContent);
    $date = vss_cleanText($date);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = $baseurl.$item->childNodes->item(1)->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = $item->childNodes->item(1)->textContent;
    $title = vss_cleanText($title);
    $title = mb_convert_encoding($title,"ISO-8859-1","UTF-8");
    $title = mb_ereg_replace("Висшият? съдебен съвет \(ВСС\)|Висшият? съдебен съвет /ВСС/|Висшият? съдебен съвет|Висшият? Съдебен Съвет|ВИСШИЯТ? СЪДЕБЕН СЪВЕТ","ВСС",$title);
    $title = mb_ereg_replace("\?","",$title);

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = vss_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@class='right_content_holder']//div[@class='text_part']");
    if ($items1->length!=1) continue;

    $description = trim($items1->item(0)->C14N());
    $description = mb_convert_encoding($description,"ISO-8859-1","UTF-8");
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|\?","",$description);
    $description = text_cleanSpaces($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());

    $imgs = $xpath1->query(".//img",$items1->item(0));
    foreach ($imgs as $img) {
      $imgsrc = $img->getAttribute("src");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc=$baseurl.$imgsrc;
      $imageurl = loadItemImage($imgsrc);
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function vssRegistri() {
  echo "> Проверявам за регистри на ВСС\n";
  setSession(9,5);

  $baseurl = "http://www.vss.justice.bg";
  $html = loadURL($baseurl."/page/view/2615",8);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//div[@class='right_content_holder']//div[@class='with_left_menu_content']//a");

  $query=array();
	foreach ($items as $item) {
    $url = $baseurl.$item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = $item->textContent;
    $title = vss_cleanText($title);
    $title = mb_convert_encoding($title,"ISO-8859-1","UTF-8");
    $title = "Обновен регистър".mb_substr($title,8);

    $query[]=array($title,null,'now',$url,$hash);
  }
  echo "Възможни ".count($query)." нови регистри\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function vss_xpath($html) {
  if (!$html)
    return null;
  $doc = new DOMDocument("1.0", "UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);  
}

function vss_xpathDoc($html,$q) {
  $xpath =  vss_xpath($html);  
  if ($xpath==null)
    return array();
  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}


function vss_cleanText($text) {
	$text = html_entity_decode($text);
  $text = text_cleanSpaces($text);
	return $text;
}

?>
