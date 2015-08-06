<?php

/*

0: обяви http://www.mi.government.bg/bg/competitions-c38-1.html
1: Продажба на активи http://www.mi.government.bg/bg/competitions-c37-1.html
2: други http://www.mi.government.bg/bg/competitions-c42-1.html
3: обществено обсъждане http://www.mi.government.bg/bg/discussion-news-0.html
4: макробюлетин http://www.mi.government.bg/bg/pages/macrobulletin-79.html
5: избор на финансови институции http://www.mi.government.bg/bg/themes/prilagane-na-pravilata-za-izbor-na-finansovi-institucii-1313-441.html
6: концентрация на фин. средства http://www.mi.government.bg/bg/themes/nalichie-na-koncentraciya-na-finansovi-sredstva-1314-441.html

*/

function mi_Obqvi() {
  echo "> Проверявам за обяви в МИЕ\n";
  setSession(11,0);

  $html = loadURL("http://www.mi.government.bg/bg/competitions-c38-1.html",0);
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@class='col2']/div[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->childNodes->item(1)->textContent);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $title = $item->childNodes->item(1)->childNodes->item(2)->textContent;
    $title = "Обява: ".mi_cleanText($title);
    $url = "http://www.mi.government.bg".$item->childNodes->item(1)->childNodes->item(2)->getAttribute("href");
    $hash = md5($url);
    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови обяви\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mi_Aktivi() {
  echo "> Проверявам за продажба на активи в МИЕ\n";
  setSession(11,1);

  $html = loadURL("http://www.mi.government.bg/bg/competitions-c37-1.html",1);
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@class='col2']/div[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->childNodes->item(1)->textContent);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $title = $item->childNodes->item(1)->childNodes->item(2)->textContent;
    $title = mi_cleanText($title);
    $url = "http://www.mi.government.bg".$item->childNodes->item(1)->childNodes->item(2)->getAttribute("href");
    $hash = md5($url);
    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови продажби на активи\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mi_Drugi() {
  echo "> Проверявам за други в МИЕ\n";
  setSession(11,2);

  $html = loadURL("http://www.mi.government.bg/bg/competitions-c42-1.html",2);
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@class='col2']/div[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(4)->childNodes->item(1)->textContent);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $title = $item->childNodes->item(1)->childNodes->item(2)->textContent;
    $title = mi_cleanText($title);
    $url = "http://www.mi.government.bg".$item->childNodes->item(1)->childNodes->item(2)->getAttribute("href");
    $hash = md5($url);
    $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови други\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mi_Obsajdane() {
  echo "> Проверявам за обществено обсъждане в МИЕ\n";
  setSession(11,3);

  $html = loadURL("http://www.mi.government.bg/bg/discussion-news-0.html");
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@class='col2']/div[@class='row']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(1)->textContent);
    $date = text_bgMonth($date);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;
    $title = $item->childNodes->item(3)->firstChild->textContent; 
    $title = mb_ereg_replace("МИЕ предлага за обществено обсъждане проект","Проект",$title,"im");
    $title = "Обществено обсъждане: ".mi_cleanText($title);

    $url = "http://www.mi.government.bg".$item->childNodes->item(3)->firstChild->getAttribute("href");
    $hash = md5($url);

    $description = $item->childNodes->item(5)->textContent;
    $description = mi_cleanText($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови обсъжданя\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mi_Makrobiuletin() {
  echo "> Проверявам за макробюлетин в МИЕ\n";
  setSession(11,4);

  $html = loadURL("http://www.mi.government.bg/bg/pages/macrobulletin-79.html");
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@class='contentColumn']//a");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent; 
    $title = mi_cleanText($title);
    $title = mb_strtolower($title);
    $title = "Основни макроикономически показатели за $title";

    $url = "http://www.mi.government.bg/".$item->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,'now',$url,$hash);
  }

  echo "Възможни ".count($query)." нови макробюлетина\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mi_Fininst() {
  echo "> Проверявам за избор фин.инст. в МИЕ\n";
  setSession(11,5);

  $html = loadURL("http://www.mi.government.bg/bg/themes/prilagane-na-pravilata-za-izbor-na-finansovi-institucii-1313-441.html");
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@id='description']//p[a]");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent; 
    $title = mi_cleanText($title);
    $title = mb_strtolower($title);
    $title = "Прилагане на правилата за избор на финансови институции $title";

    $url = "http://www.mi.government.bg/".$item->firstChild->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,'now',$url,$hash);
  }

  echo "Възможни ".count($query)." нови избор фин.инст.\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function mi_KoncentraciqFin() {
  echo "> Проверявам за концентрация фин.ср. в МИЕ\n";
  setSession(11,6);

  $html = loadURL("http://www.mi.government.bg/bg/themes/nalichie-na-koncentraciya-na-finansovi-sredstva-1314-441.html");
  if (!$html) return;
  $items = mi_xpathDoc($html,"//div[@id='description']//p[a]");

  $query=array();
	foreach ($items as $item) {
    $title = $item->textContent; 
    $title = mi_cleanText($title);
    $title = mb_strtolower($title);
    $title = "Наличие на концентрация на финансови средства $title";

    $url = "http://www.mi.government.bg/".$item->firstChild->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,'now',$url,$hash);
  }

  echo "Възможни ".count($query)." нови концентрация фин.ср.\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function mi_xpathDoc($html,$q) {
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

function mi_cleanText($text) {
	$text = html_entity_decode($text);
  $text = text_cleanSpaces($text);
	return $text;
}

?>
