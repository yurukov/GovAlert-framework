<?php

/*

0: актуално http://www.mh.government.bg/bg/novini/aktualno/
1: мс http://www.mh.government.bg/bg/novini/ministerski-savet/
2: парламентарен контрол http://www.mh.government.bg/bg/novini/parlamentaren-kontrol/
3: епидемичната обстановка http://www.mh.government.bg/bg/novini/epidemichna-obstanovka/
4: покани и конкурси http://www.mh.government.bg/bg/novini/pokani-i-konkursi/
5: постановления http://www.mh.government.bg/bg/normativni-aktove/postanovleniya/
6: наредби http://www.mh.government.bg/bg/normativni-aktove/naredbi/
7: заповеди http://www.mh.government.bg/bg/normativni-aktove/zapovedi-pravilnitsi-instruktsii/
8: проекти на нормативни актове http://www.mh.government.bg/bg/normativni-aktove/proekti-na-normativni-aktove/

*/

function mh_Aktualno() {
  mhDefault("http://www.mh.government.bg/bg/novini/aktualno/",0,"новини");
}

function mh_MinisterskiSavet() {
  mhDefault("http://www.mh.government.bg/bg/novini/ministerski-savet/",1,"новини от МС");
}

function mh_ParlamentarenKontrol() {
  mhDefault("http://www.mh.government.bg/bg/novini/parlamentaren-kontrol/",2,"парламентарен контрол");
}

function mh_EpidemichnaObstanovka() {
  mhDefault("http://www.mh.government.bg/bg/novini/epidemichna-obstanovka/",3,"епидемичната обстановка");
}

function mh_Pokani() {
  mhDefault("http://www.mh.government.bg/bg/novini/pokani-i-konkursi/",4,"покани и конкурси");
}

function mh_Postanovleniya() {
  mhFile("http://www.mh.government.bg/bg/normativni-aktove/postanovleniya/",5,"постановления");
}

function mh_Naredbi() {
  mhFile("http://www.mh.government.bg/bg/normativni-aktove/naredbi/",6,"наредби");
}

function mh_Zapovedi() {
  mhFile("http://www.mh.government.bg/bg/normativni-aktove/zapovedi-pravilnitsi-instruktsii/",7,"заповеди");
}

function mh_ProektiNormativniAktove() {
  mhFile("http://www.mh.government.bg/bg/normativni-aktove/proekti-na-normativni-aktove/",8,"проекти на нормативни актове");
}



function mhDefault($originalurl, $category, $comment) {
  echo "> Проверявам за $comment в МЗ\n";
  setSession(21,$category);

  $html = loadURL($originalurl,$category);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//ul[@class='news-list']//li[@class='news']/h2/a");

  $query=array();
	foreach ($items as $item) {
    $url = "http://www.mh.government.bg".$item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash)) continue;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = mh_xpath($html1);
    if (!$xpath1) continue;

    $items1 = $xpath1->query("//ul[@class='list-inline newsdate']//time");
    if ($items1->length!=1) continue;
    $date = $items1->item(0)->getAttribute("datetime");
    if (strtotime($date)<strtotime("-1 month")) continue;

    $items1 = $xpath1->query("//div[@class='navigation-container']//h1");
    if ($items1->length!=1) continue;
    $title = $items1->item(0)->textContent;
    $title = mh_cleanText($title);

    $items1 = $xpath1->query("//div[@class='single_news']");
    if ($items1->length!=1) continue;
    $description = trim($items1->item(0)->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
    $description = mh_cleanText($description);
    $description = html_entity_decode($description);
    
    $media=null;
    $items1 = $xpath1->query("//div[@id='news_carousel']//img[@class='img-responsive']");
    if ($items1 && $items1->length>0) {
      $media=array("image" => array());
      foreach ($items1 as $item1) {
        $imgsrc = "http://www.mh.government.bg".$item1->getAttribute("src");
        $imageurl = loadItemImage($imgsrc);
        if ($imageurl)
          $media["image"][] = array($imageurl);
      }
      if (count($media["image"])==0)
        $media=null;
    }
  
    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови $comment\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mhFile($originalurl, $category, $comment) {
  echo "> Проверявам за $comment в МЗ\n";
  setSession(21,$category);

  $html = loadURL("$originalurl",$category);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//div[@id='filegroups']//div[@class='panel-body']/div[@class='document'][1]/p");

  $query=array();
	foreach ($items as $item) {
    $origdate = trim($item->childNodes->item($item->childNodes->length-2)->textContent);
    $origdate = mb_substr($origdate,-10);
    $date = mb_substr($origdate,6).'-'.mb_substr($origdate,3,2).'-'.mb_substr($origdate,0,2);
    if (strtotime($date)<strtotime("-1 month")) continue;

    if ($item->parentNode->nextSibling && $item->parentNode->nextSibling && $item->parentNode->nextSibling->nextSibling &&
      $item->parentNode->nextSibling->nextSibling->getAttribute("class")=="document") {
      $url = $originalurl."?from_date=$origdate&to_date=$origdate";
    } else {
      $url = "http://www.mh.government.bg".$item->childNodes->item(1)->getAttribute("href");
    }
    $hash = md5($url);
    if (!checkHash($hash)) continue;

    $title = $item->childNodes->item(1)->textContent;
    $title = mh_cleanText($title);
    $query[]=array($title,null,$date,$url,$hash);
  }
  echo "Възможни ".count($query)." нови $comment\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}


/*
-----------------------------------------------------------------
*/

function mh_xpath($html) {
  if (!$html)
    return null;
  $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
  $doc = new DOMDocument("1.0", "UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);  
}

function mh_xpathDoc($html,$q) {
  $xpath = mh_xpath($html);  
  if (!$xpath) 
    return array();
  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}

function mh_cleanText($text) {
	$text = html_entity_decode($text);
  $text = text_cleanSpaces($text);
	return $text;
}

?>
