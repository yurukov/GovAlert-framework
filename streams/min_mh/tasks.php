<?php

/*

0: съобщения http://www.mh.government.bg/AllMessages.aspx
1: новини http://www.mh.government.bg/News.aspx?pageid=401
2: проекти за нормативни актове http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=393 
3: наредби http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=391
4: постановления http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=381
5: отчети http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=532&currentPage=1

*/

function mh_Saobshteniq() {
  echo "> Проверявам за съобщения в МЗ\n";
  setSession(21,0);

  $html = loadURL("http://www.mh.government.bg/AllMessages.aspx",0);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//table[@id='ctl00_ContentPlaceClient_gvMessages']//a");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent;
    $title = "Съобщение: ".mh_cleanText($title);
    $url = "http://www.mh.government.bg/".$item->getAttribute("href");
    $hash = md5($url);
    $query[]=array($title,null,'now',$url,$hash);
    if (count($query)>=20)
       break;
  }

  echo "Възможни ".count($query)." нови съобщения\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mh_Novini() {
  echo "> Проверявам за новини в МЗ\n";
  setSession(21,1);

  $html = loadURL("http://www.mh.government.bg/News.aspx?pageid=401",1);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//table[@id='ctl00_ContentPlaceClient_ucNewsList_gvwNews']//tr[not(@class)]/td");

  $query=array();
	foreach ($items as $item) {
    $date = $item->childNodes->item(3)->textContent;
    $date = mh_cleanText($date); 
    $date = explode(".",$date);
    $date = substr($date[2],0,4)."-".$date[1]."-".$date[0];

    if (strtotime($date)<strtotime("-1 month"))
      continue;

    $title = $item->childNodes->item(1)->textContent;
    $title = mh_cleanText($title); 

    $description = $item->childNodes->item(5)->textContent;
    $description = mh_cleanText($description); 
    
    if (mb_strlen($title)<25)
      $title.=" ".$description;

    $url = $item->childNodes->item(1)->getAttribute("href");
    $urlstart = strpos($url,'News.aspx');
    $url = substr($url,$urlstart,strpos($url,'"',$urlstart)-$urlstart);
    $url = "http://www.mh.government.bg/$url";
    $hash = md5($url);

    $media=null;
    if ($item->childNodes->item(5)->childNodes->item(1)->nodeName=="input") {
      $imageurl = $item->childNodes->item(5)->childNodes->item(1)->getAttribute("src");
      $imageurl = "http://www.mh.government.bg/$imageurl";
      $imageurl=mb_ereg_replace("small","large",$imageurl,"im");
      $media = array("image" => array(loadItemImage($imageurl),null));
    }

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }

  echo "Възможни ".count($query)." нови новини\n";
  
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mh_Normativni() {
  echo "> Проверявам за нормативни актове в МЗ\n";
  setSession(21,2);

  $html = loadURL("http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=393",2);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//table[@id='ctl00_ContentPlaceClient_ucArticlesList_gvArticles']//tr[not(@class)]/td");

  $query=array();
	foreach ($items as $item) {
    $date = $item->childNodes->item(3)->textContent;
    $date = mh_cleanText($date); 
    $date = explode(".",$date);
    $date = substr($date[2],0,4)."-".$date[1]."-".substr($date[0],-2);

    if (strtotime($date)<strtotime("-1 month"))
      continue;

    $title = $item->childNodes->item(1)->textContent;
    $title = mh_cleanText($title); 
    
    $url = $item->childNodes->item(1)->getAttribute("href");
    $urlstart = strpos($url,'Articles.aspx');
    $url = substr($url,$urlstart,strpos($url,'"',$urlstart)-$urlstart);
    $url = "http://www.mh.government.bg/$url";
    $hash = md5($url);

    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови нормативни актове\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mh_Naredbi() {
  echo "> Проверявам за наредби в МЗ\n";
  setSession(21,3);

  $html = loadURL("http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=391",3);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//table[@id='ctl00_ContentPlaceClient_ucArticlesList_gvArticles']//tr[not(@class)]/td/a[@class='list_article_title']");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent;
    $title = mh_cleanText($title); 

    $url = $item->getAttribute("href");
    $urlstart = strpos($url,'Articles.aspx');
    $url = substr($url,$urlstart,strpos($url,'"',$urlstart)-$urlstart);
    $url = "http://www.mh.government.bg/$url";
    $hash = md5($url);

    $query[]=array($title,null,"now",$url,$hash);
  }

  echo "Възможни ".count($query)." нови наредби\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mh_Postanovleniq() {
  echo "> Проверявам за постановления в МЗ\n";
  setSession(21,4);

  $html = loadURL("http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=381",4);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//table[@id='ctl00_ContentPlaceClient_ucArticlesList_gvArticles']//tr[not(@class)]/td/a[@class='list_article_title']");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent;
    $title = mh_cleanText($title); 

    $url = $item->getAttribute("href");
    $urlstart = strpos($url,'Articles.aspx');
    $url = substr($url,$urlstart,strpos($url,'"',$urlstart)-$urlstart);
    $url = "http://www.mh.government.bg/$url";
    $hash = md5($url);

    $query[]=array($title,null,"now",$url,$hash);
  }

  echo "Възможни ".count($query)." нови постановления\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mh_Otcheti() {
  echo "> Проверявам за отчети в МЗ\n";
  setSession(21,5);

  $html = loadURL("http://www.mh.government.bg/Articles.aspx?lang=bg-BG&pageid=532",5);
  if (!$html) return;
  $items = mh_xpathDoc($html,"//table[@id='ctl00_ContentPlaceClient_ucArticlesList_gvArticles']//tr[not(@class)]/td/a[@class='list_article_title']");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent;
    $title = mh_cleanText($title); 

    $url = $item->getAttribute("href");
    $urlstart = strpos($url,'Articles.aspx');
    $url = substr($url,$urlstart,strpos($url,'"',$urlstart)-$urlstart);
    $url = "http://www.mh.government.bg/$url";
    $hash = md5($url);

    $query[]=array($title,null,"now",$url,$hash);
  }

  echo "Възможни ".count($query)." нови отчети\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function mh_xpathDoc($html,$q) {
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

function mh_cleanText($text) {
	$text = html_entity_decode($text);
  $text = text_cleanSpaces($text);
  $text = text_fixCase($text);
	return $text;
}

?>
