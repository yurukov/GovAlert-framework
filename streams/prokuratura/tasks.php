<?php

/*
0: новини http://www.prb.bg/bg/news/aktualno/
1: документи http://www.prb.bg/bg/documents/spravki-i-analizi/
2: конкурси http://www.prb.bg/bg/karieri/
3: галерия http://www.prb.bg/bg/news/gallery/
4: доклади http://www.prb.bg/bg/documents/godishni-dokladi/
5: обществени поръчки http://www.prb.bg/bg/obshestveni-porchki/elektronni-prepiski/

*/

function prok_Novini() {
  echo "> Проверявам за новини в Прокуратурата\n";
  setSession(13,0);

  $html = loadURL("http://www.prb.bg/bg/news/aktualno/",0);
  if (!$html) return;
  $items = prok_xpathDoc($html,"//div[@class='article']//li[@class='list-group-item']");

  $query=array();
	foreach ($items as $item) {
    if ($item->childNodes->item(1)->nodeName=='div') {
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
    }

    $date = trim($item->childNodes->item(3)->getAttribute('datetime'));
    $date = str_replace("T"," ",$date);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $url = "http://www.prb.bg".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = trim($item->childNodes->item(1)->textContent);
    $title = prok_cleanTitle($title);
    $title = prok_cleanText($title);

    $item->removeChild($item->childNodes->item(3));
    $item->removeChild($item->childNodes->item(1));
    $description = trim($item->C14N());
    $description = prok_cleanDesc($description);
    $description = prok_cleanText($description);

    $media=null;
    $html1 = loadURL($url);
    if ($html1) {
      $media = array("image" => array());
      $items1 = prok_xpathDoc($html1,"//div[@class='carousel-inner']//img");
      foreach ($items1 as $item1) {
        $imageurl = $item1->getAttribute("src");
        if (strpos($imageurl,".720x420")!==false)
          $imageurl=substr($imageurl,0,strpos($imageurl,".720x420"));
        $imageurl = "http://www.prb.bg$imageurl";
        $media["image"][]=array(loadItemImage($imageurl),$null);
      }
      if (count($media["image"])==0)
        $media=null;
      else if (count($media["image"])==1)
        $media["image"]=$media["image"][0];
    
      $descrstart = mb_strpos($html1,'<div class="clearfix"></div>')+mb_strlen('<div class="clearfix"></div>');
      $descrstart = mb_strpos($html1,'<div class="clearfix"></div>',$descrstart)+mb_strlen('<div class="clearfix"></div>');
      $description = mb_substr($html1, $descrstart,mb_strpos($html1,'<div class="clearfix"></div>',$descrstart)-$descrstart);
      $description = prok_cleanDesc($description);
      $description = prok_cleanText($description);
    }

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }

  echo "Възможни ".count($query)." нови новини\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function prok_Dokumenti() {
  echo "> Проверявам за документи в Прокуратурата\n";
  setSession(13,1);

  $html = loadURL("http://www.prb.bg/bg/documents/spravki-i-analizi/",1);
  if (!$html) return;
  $items = prok_xpathDoc($html,"//div[@class='article']//li[@class='list-group-item']");

  $query=array();
	foreach ($items as $item) {
    if ($item->childNodes->item(1)->nodeName=='div') {
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
    }

    $date = trim($item->childNodes->item(3)->getAttribute('datetime'));
    $date = str_replace("T"," ",$date);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $url = "http://www.prb.bg".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);

    $title = trim($item->childNodes->item(1)->textContent);
    $title = prok_cleanTitle($title);
    $title = prok_cleanText($title);

    $item->removeChild($item->childNodes->item(3));
    $item->removeChild($item->childNodes->item(1));
    $description = trim($item->C14N());
    $description = prok_cleanDesc($description);
    $description = prok_cleanText($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови документи\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function prok_Konkursi() {
  echo "> Проверявам за конкурси в Прокуратурата\n";
  setSession(13,2);

  $html = loadURL("http://www.prb.bg/bg/karieri/",2);
  if (!$html) return;
  $items = prok_xpathDoc($html,"//div[@class='article']//li[@class='list-group-item']");

  $query=array();
	foreach ($items as $item) {
    if ($item->childNodes->item(1)->nodeName=='div') {
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
    }

    $date = trim($item->childNodes->item(3)->getAttribute('datetime'));
    $date = str_replace("T"," ",$date);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $url = "http://www.prb.bg".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);

    $title = trim($item->childNodes->item(1)->textContent);
    $title = prok_cleanTitle($title);
    $title = prok_cleanText($title);

    $item->removeChild($item->childNodes->item(3));
    $item->removeChild($item->childNodes->item(1));
    $description = trim($item->C14N());
    $description = prok_cleanDesc($description);
    $description = prok_cleanText($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови конкурса\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function prok_Snimki() {
  echo "> Проверявам за галерии в Прокуратурата\n";
  setSession(13,3);

  $html = loadURL("http://www.prb.bg/bg/news/gallery/",3);
  if (!$html) return;
   $items = prok_xpathDoc($html,"//div[@class='article']//div[@class='col-md-6 col-sm-6 col-xs-6' and h3]");

  $query=array();
	foreach ($items as $item) {
    if ($item->childNodes->item(1)->nodeName=='div') {
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
    }

    $date = trim($item->childNodes->item(3)->getAttribute('datetime'));
    $date = str_replace("T"," ",$date);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $url = "http://www.prb.bg".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $title = trim($item->childNodes->item(1)->textContent);
    $title = prok_cleanTitle($title);
    $title = prok_cleanText($title);

    $media=null;
    $html1 = loadURL($url);
    if ($html1) {
      $media = array("image" => array());
      $items1 = prok_xpathDoc($html1,"//div[@class='carousel-inner']//img");
      foreach ($items1 as $item1) {
        $imageurl = $item1->getAttribute("src");
        if (strpos($imageurl,".720x420")!==false)
          $imageurl=substr($imageurl,0,strpos($imageurl,".720x420"));
        $imageurl = "http://www.prb.bg$imageurl";
        $media["image"][]=array(loadItemImage($imageurl),$null);
      }
      if (count($media["image"])==0)
        $media=null;
      else if (count($media["image"])==1)
        $media["image"]=$media["image"][0];
    }

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }

  echo "Възможни ".count($query)." нови галерии\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function prok_Dokladi() {
  echo "> Проверявам за доклади в Прокуратурата\n";
  setSession(13,4);

  $html = loadURL("http://www.prb.bg/bg/documents/godishni-dokladi/",4);
  if (!$html) return;
  $items = prok_xpathDoc($html,"//div[@class='article']//li[@class='list-group-item']");

  $query=array();
	foreach ($items as $item) {
    if ($item->childNodes->item(1)->nodeName=='div') {
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
    }

    $date = trim($item->childNodes->item(3)->getAttribute('datetime'));
    $date = str_replace("T"," ",$date);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $url = "http://www.prb.bg".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);

    $title = trim($item->childNodes->item(1)->textContent);
    $title = prok_cleanTitle($title);
    $title = prok_cleanText($title);

    $item->removeChild($item->childNodes->item(3));
    $item->removeChild($item->childNodes->item(1));
    $description = trim($item->C14N());
    $description = prok_cleanDesc($description);
    $description = prok_cleanText($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови доклади\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function prok_Porachki() {
  echo "> Проверявам за поръчки в Прокуратурата\n";
  setSession(13,5);

  $html = loadURL("http://www.prb.bg/bg/obshestveni-porchki/elektronni-prepiski/",5);
  if (!$html) return;
  $items = prok_xpathDoc($html,"//div[@class='article']//li[@class='list-group-item']");

  $query=array();
	foreach ($items as $item) {
    if ($item->childNodes->item(1)->nodeName=='div') {
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
    }

    $date = trim($item->childNodes->item(3)->getAttribute('datetime'));
    $date = str_replace("T"," ",$date);
    if (strtotime($date)<strtotime("-1 weeks"))
      continue;

    $url = "http://www.prb.bg".$item->childNodes->item(1)->firstChild->getAttribute("href");
    $hash = md5($url);

    $title = trim($item->childNodes->item(1)->textContent);
    $title = prok_cleanTitle($title);
    $title = prok_cleanText($title);

    $item->removeChild($item->childNodes->item(3));
    $item->removeChild($item->childNodes->item(1));
    $description = trim($item->C14N());
    $description = prok_cleanDesc($description);
    $description = prok_cleanText($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }

  echo "Възможни ".count($query)." нови поръчки\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}


/*
-----------------------------------------------------------------
*/

function prok_xpathDoc($html,$q) {
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


function prok_cleanTitle($title) {
  if (mb_substr($title,-1)==".")
    $title = mb_substr($title,0,mb_strlen($title)-1);
  $title=mb_ereg_replace("Република България","РБ",$title,"im");
  $title=mb_ereg_replace("Р България","РБ",$title,"im");
  $title=mb_ereg_replace("„|“","",$title,"im");
  $title=mb_ereg_replace("Народно(то)? събрание","НС",$title,"im");
  $title=mb_ereg_replace("Министерски(ят)? съвет","МС",$title,"im");
  $title=mb_ereg_replace("(ИЗБИРАТЕЛНИ КОМИСИИ)|(избирателна комисия)","ИК",$title,"im");
  $title=mb_ereg_replace("ОБЯВЛЕНИЕОТНОСНО:?|ОТНОСНО:?|С Ъ О Б Щ Е Н И Е|СЪОБЩЕНИЕ|г\.|ч\.|\\\\|„|\"|'","",$title,"im");
  return $title;
}

function prok_cleanDesc($description) {
  $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description),"im");
  $description = mb_ereg_replace("\s?(title|name|style|class|id|target)=[\"'].*?[\"']\s?","",$description,"im");
  $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|<span>[  ]*</span>","",$description,"im");
  $description = mb_ereg_replace("</?li>","",$description,"im");
  return $description;
}

function prok_cleanText($text) {
  $text = text_cleanSpaces($text);
	$text = html_entity_decode($text);
	return $text;
}

?>
