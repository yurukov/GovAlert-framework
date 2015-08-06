<?php

/*

0: Месечен преглед на напредъка по оперативните програми http://eufunds.bg/bg/page/766
1: новини http://eufunds.bg/bg/pubs/0
2: околна среда - новини http://ope.moew.government.bg/bg/news/current
3: регионална политика - новини http://www.bgregio.eu/novini.aspx
4: конкурентноспособност - новини http://www.opcompetitiveness.bg/index.php?texver=0
5: човешко развитие - новини http://ophrd.government.bg/view_doc.php/NEWS
6: административен капацитет - новини http://www.opac.government.bg/bg/topical/news
7: транспорт - новини http://www.optransport.bg/page.php?c=67

*/

function euf_MonthReports() {
  echo "> Проверявам за месечен преглед на EUFunds\n";
  setSession(25,0);

  $html = loadURL("http://eufunds.bg/bg/page/766",0);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@id='content']//div[@class='document']/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = "http://eufunds.bg".$item->getAttribute("href");
    $hash = md5($url);

    $title = $item->textContent;
    $title = euf_cleanText($title);
    $title = mb_ereg_replace(" Оперативните"," оперативните",$title,"im");
    $title = mb_ereg_replace(" ?201[0-9]( г.)?","",$title,"im");
    $title = $title." /cc @TomislavDonchev";

    $query[]=array($title,null,'now',$url,$hash);
    if (count($query)>10)
      break;
  }
  echo "Възможни ".count($query)." нови месечни прегледи на EUFunds\n";
print_r($query); exit;
  $itemids = saveItems($query);
  queueTweets($itemids,'govalerteu','govbulgaria');
}


function euf_Novini() {
  echo "> Проверявам за новини на EUFunds\n";
  setSession(25,1);

  $html = loadURL("http://eufunds.bg/bg/pubs/0",1);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@class='news']/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = "http://eufunds.bg".$item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@class='news news_item']");
    if ($items1->length==0) continue;

    $items2 = $xpath1->query("./span[@class='calendar']",$items1->item(0));
    if ($items2->length==0) continue;
    $date = trim($items2->item(0)->textContent);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $items2 = $xpath1->query("./h4",$items1->item(0));
    if ($items2->length==0) continue;
    $title = $items2->item(0)->textContent;
    $title = euf_cleanText($title);

    $description = trim($items1->item(0)->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath1->query(".//img",$items1->item(0));
    foreach ($imgs as $img) {
      $imgsrc = $img->getAttribute("src");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc="http://eufunds.bg".$imgsrc;
      $imageurl = loadItemImage($imgsrc,null,array("ignoreCached"=>true));
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function euf_OkolnaSreda() {
  echo "> Проверявам за новини за околна среда на EUFunds\n";
  setSession(25,2);

  $html = loadURL("http://ope.moew.government.bg/bg/news/current",2);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@id='main']//div[@class='entry']/p/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = "http://ope.moew.government.bg".$item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = $item->textContent;
    $title = euf_cleanText($title);

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@id='content']");
    if ($items1->length==0) continue;

    $items2 = $xpath1->query("./div[@class='date']",$items1->item(0));
    if ($items2->length==0) continue;
    $date = trim($items2->item(0)->textContent);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $items2 = $xpath1->query("./div[@class='entry']",$items1->item(0));
    if ($items2->length==0) continue;
    $description = trim($items2->item(0)->C14N());
    $description = mb_convert_encoding($description,"ISO-8859-1","UTF-8");
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath1->query(".//ul[@id='slideshow-news']//a[@rel='lightbox']",$items1->item(0));
    foreach ($imgs as $img) {
      $imgsrc = $img->getAttribute("href");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc="http://ope.moew.government.bg/".$imgsrc;
      $imageurl = loadItemImage($imgsrc);
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини за околна среда на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function euf_RegionalnaPolitika() {
  echo "> Проверявам за новини за регионална политика на EUFunds\n";
  setSession(25,3);

  $html = loadURL("http://www.bgregio.eu/novini.aspx",3);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@id='content-inner']//div[@class='link']/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = "http://www.bgregio.eu".$item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@class='news-detail']");
    if ($items1->length==0) continue;

    $items2 = $xpath1->query(".//span[@class='date']",$items1->item(0));
    if ($items2->length==0) continue;
    $date = trim($items2->item(0)->textContent);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $items2 = $xpath1->query("./div[@class='title']",$items1->item(0));
    if ($items2->length==0) continue;
    $title = $items2->item(0)->childNodes->item(2)->textContent;
    $title = euf_cleanText($title);

    $description = trim($items1->item(0)->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }
  echo "Възможни ".count($query)." нови новини за регионална политика на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function euf_Konkurentnosposobnost() {
  echo "> Проверявам за новини за конкурентноспособност на EUFunds\n";
  setSession(25,4);

  $html = loadURL("http://www.opcompetitiveness.bg/index.php?texver=0",4);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@id='pageText']//div[@style='float:right;']/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = "http://www.opcompetitiveness.bg/".$item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@id='pageText']");
    if ($items1->length==0) continue;

    $items2 = $xpath1->query("./span[@class='docsDate' and text()]",$items1->item(0));
    if ($items2->length==0) continue;
    $date = trim($items2->item(0)->textContent);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      break;

    $items2 = $xpath1->query("./span[@class='newsTitle']",$items1->item(0));
    if ($items2->length==0) continue;
    $title = $items2->item(0)->textContent;
    $title = euf_cleanText($title);

    $description = trim($items1->item(0)->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath1->query(".//a[img]",$items1->item(0));
    foreach ($imgs as $img) {
      $imgsrc = $img->getAttribute("href");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc="http://www.opcompetitiveness.bg/".$imgsrc;
      $imageurl = loadItemImage($imgsrc);
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини за конкурентноспособност на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function euf_ChoveshkoRazvitie() {
  echo "> Проверявам за новини за човешко развитие на EUFunds\n";
  setSession(25,5);

  $html = loadURL("http://ophrd.government.bg/view_doc.php/NEWS",5);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@id='main_txt']//h2/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = $item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@id='main_wide']");
    if ($items1->length==0) continue;

    $items2 = $xpath1->query(".//div[@id='newsdate']",$items1->item(0));
    if ($items2->length==0) continue;
    $date = trim($items2->item(0)->textContent);
    $date = euf_cleanText($date);
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      break;

    $items2 = $xpath1->query("./div[@id='main_blue']/h1",$items1->item(0));
    if ($items2->length==0) continue;
    $title = $items2->item(0)->textContent;
    $title = euf_cleanText($title);

    $items2 = $xpath1->query("./div[@id='main_txt']",$items1->item(0));
    if ($items2->length==0) continue;
    $description = trim($items2->item(0)->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath1->query(".//img",$items1->item(0));
    foreach ($imgs as $img) {
      $imgsrc = $img->getAttribute("src");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc="http://ophrd.government.bg/".$imgsrc;
      $imageurl = loadItemImage($imgsrc);
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини за човешко развитие на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}


function euf_AdminKapacitet() {
  echo "> Проверявам за новини за административен капацитет на EUFunds\n";
  setSession(25,6);

  $html = loadURL("http://www.opac.government.bg/bg/topical/news",6);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@id='inner_content']//div[@class='news_body']/a");
  
  $query=array();
	foreach ($items as $item) {
    $url = $item->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = $item->textContent;
    $title = euf_cleanText($title);

    $date = trim($item->previousSibling->previousSibling->textContent);
    $date = mb_substr($date,0,11).mb_substr($date,13);
    if (strtotime($date)<strtotime("-1 week"))
      break;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@id='news_view_body']");
    if ($items1->length==0) continue;

    $items2 = $xpath1->query(".//div[@style='text-align: justify;']",$items1->item(0));
    if ($items2->length==0) continue;
    $description="";
  	foreach ($items2 as $item2)
      $description .= trim($item2->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath1->query(".//div[@style='text-align: justify;']/a[img] | .//div[@style='text-align: justify;']/img",$items1->item(0));
    foreach ($imgs as $img) {
      if ($img->nodeName=="img")
        $imgsrc = $img->getAttribute("src");
      else
        $imgsrc = $img->getAttribute("href");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc="http://www.opac.government.bg/".$imgsrc;
      $imageurl = loadItemImage($imgsrc);
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини за административен капацитет на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}


function euf_Transport() {
  echo "> Проверявам за новини за транспорт на EUFunds\n";
  setSession(25,7);

  $html = loadURL("http://www.optransport.bg/page.php?c=67",7);
  if (!$html) return;
  $items = euf_xpathDoc($html,"//div[@class='content']");
  
  $query=array();
	foreach ($items as $item) {
    $url = "http://www.optransport.bg/".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = $item->childNodes->item(1)->textContent;
    $title = mb_convert_encoding($title,"ISO-8859-1","UTF-8");
    $title = euf_cleanText($title);

    $date = trim($item->childNodes->item(2)->textContent);
    $date = mb_convert_encoding($date,"ISO-8859-1","UTF-8");
    $date = text_bgMonth($date);
    if (mb_strlen($date)<10)
      $date = '0'.$date;    
    $date = mb_substr($date,6)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      break;

    $html1 = loadURL($url);
    if (!$html1) continue;
    $xpath1 = euf_xpath($html1);
    if (!$xpath1) continue;
    $items1 = $xpath1->query("//div[@class='content']");
    if ($items1->length==0) continue;

    $description = trim($items1->item(0)->C14N());
    $description = mb_convert_encoding($description,"ISO-8859-1","UTF-8");
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img|on[a-z]+)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace("\?","",$description);
    $description = mb_ereg_replace(">[  ]*<","><",$description);
    $description = mb_ereg_replace("[  ]*>",">",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<br>"," ",$description);
    $description = euf_cleanText($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath1->query("./a[img] | ./img",$items1->item(0));
    foreach ($imgs as $img) {
      if ($img->nodeName=="img")
        $imgsrc = $img->getAttribute("src");
      else
        $imgsrc = $img->getAttribute("href");
      if (mb_substr($imgsrc,0,4)!="http")
        $imgsrc="http://www.optransport.bg/".$imgsrc;
      $imageurl = loadItemImage($imgsrc,null,array("ignoreCached"=>true));
      if ($imageurl)
        $media["image"][] = array($imageurl);
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини за транспорт на EUFunds\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}


/*
-----------------------------------------------------------------
*/

function euf_xpath($html) {
  if (!$html)
    return null;
  $doc = new DOMDocument("1.0", "UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);  
}

function euf_xpathDoc($html,$q) {
  $xpath =  euf_xpath($html);  
  if ($xpath==null)
    return array();
  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}


function euf_cleanText($text) {
	$text = html_entity_decode($text);
  $text = text_cleanSpaces($text);
	return $text;
}

?>
