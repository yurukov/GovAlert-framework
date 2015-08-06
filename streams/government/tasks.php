<?php
/*
Links
0: заседания http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0225&g=
1: решения http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0228&g=
2: събития http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0217&g=
3: документ http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0211&g=
4: водещи новини http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0213&g=
5: новини http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0212&g=
6: обществени поръчки http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0235&g=
*/

function govZasedaniq() {
  echo "> Проверявам заседания на кабинета\n";
  setSession(3,0);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0225&g=",0);
  if (!$html) return;
  $items = gov_xpathDoc($html,"//td[@valign='top' and starts-with(./a/font/text(),'Дневен ред')]");

  echo "Открити ".$items->length." заседания\n";

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->childNodes->item(0)->childNodes->item(1)->textContent);
    $date = $item->childNodes->item(0)->childNodes->item(1)->textContent;
    $date = mb_substr($date,mb_strrpos($date,"на ")+3,10);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-2 day"))
      continue;
    $title = $item->childNodes->item(0)->childNodes->item(1)->textContent;
    $title = mb_ereg_replace("Министерския съвет","МС",$title,"im");
    $url = "http://www.government.bg".$item->childNodes->item(0)->getAttribute("href");
    $query[]=array($title,null,null,$url,$hash);
  }
  
  echo "Възможни ".count($query)." нови заседания\n";
  $itemids = saveItems($query);
  queueTweets($itemids,'GovBulgaria',true);
}

function govResheniq() {
  echo "> Проверявам решения на кабинета\n";
  setSession(3,1);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0228&g=",1);
  if (!$html) return;
  $items = gov_xpathDoc($html,"//table[.//a[@class='header']/text()='Решенията Накратко']//td[@valign='top']/p");

  echo "Открити ".$items->length." решения\n";

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->textContent);
    $date = $item->lastChild->childNodes->item(0)->textContent;
    $date = text_bgMonth($date);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $title = $item->childNodes->item(2)->childNodes->item(0)->textContent;
    $title = text_cleanSpaces($title);
    $title = "Решение: ".$title;
    $url = "http://www.government.bg".$item->childNodes->item(2)->getAttribute("href");
    $query[]=array($title,null,null,$url,$hash);
  }
  
  echo "Възможни ".count($query)." нови решения\n";
  $itemids = saveItems($query);

  if (count($itemids)>3)
    queueTextTweet("Достъпни са ".count($itemids)." нови решения от последното заседание","http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0228&g=",'GovBulgaria',true);
  else
    queueTweets($itemids,'GovBulgaria',true);
}

function govSabitiq() {
  echo "> Проверявам събития на кабинета\n";
  setSession(3,2);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0217&g=",2);
  if (!$html) return;
  $items = gov_xpathDoc($html,"//td[.//a[@class='header']/text()='Предстоящи събития' and table/@bgcolor='#ffffff']//td[@valign='top']/a");
  
  echo "Открити ".$items->length." събития\n";
  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->textContent);
    $title = $item->childNodes->item(1)->textContent;
    $title = text_cleanSpaces($title);
    $title = "Събитие: ".$title;
    $url = "http://www.government.bg".$item->getAttribute("href");
    $query[]=array($title,null,null,$url,$hash);
  }

  echo "Възможни ".count($query)." нови събития\n";
  $itemids = saveItems($query);
  queueTweets($itemids,'GovBulgaria');
}

function govDokumenti() {
  echo "> Проверявам документи на кабинета\n";
  setSession(3,3);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0211&g=",3);
  if (!$html) return;
  $items = gov_xpathDoc($html,"//table[.//a[@class='header']/text()='Документи']//td[@valign='top']/a[@target='_self']");

  echo "Открити ".$items->length." документи\n";
  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->textContent);
    $title = $item->childNodes->item(1)->textContent;
    $title = text_cleanSpaces($title);
    $title = "Нов документ: ".$title;
    $url = "http://www.government.bg".$item->getAttribute("href");
    $query[]=array($title,null,null,$url,$hash);
  }

  echo "Възможни ".count($query)." нови документи\n";
  $itemids = saveItems($query);
  queueTweets($itemids,'GovBulgaria');
}

function govNovini() {
  echo "> Проверявам водещи новини на кабинета\n";
  setSession(3,4);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0213&g=",4);
  if (!$html) return;
  $xpath = gov_xpath($html);
  if (!$xpath) return;  
  $items = $xpath->query("//table[@cellpadding=1]");
  if (!$items) return;  

  echo "Открити ".$items->length." новини\n";

  $query=array();
	foreach ($items as $item) {
    $inneritems = $xpath->query(".//td",$item);
    if ($inneritems->length!=4)
      continue;

    $date = $inneritems->item(1)->textContent;
    $date = text_bgMonth($date);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = "http://www.government.bg".$inneritems->item(0)->firstChild->getAttribute("href");
    $hash = md5($url);

    $description=null;
    $media=null;
    $htmlsub = loadURL($url,3);
    $xpathsub = gov_xpath($htmlsub);
    $itemsub = $xpathsub->query("//table[./tbody/tr/td/font[@style='FONT-SIZE: 11px; TEXT-TRANSFORM: uppercase']]");
    if ($itemsub->length>0) {
      $description = $itemsub->item(0)->C14N();
      $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
      $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
      $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
      $description = text_cleanSpaces($description);
	    $description = html_entity_decode($description);

      $itemimgs = $xpathsub->query(".//img",$itemsub->item(0));
      if ($itemimgs->length>0) {
        $media = array("image" => array());
        foreach ($itemimgs as $itemimg){
          $imageurl = $itemimg->getAttribute("src");
          if (strpos($imageurl,"government.bg")===false)
            $imageurl="http://www.government.bg/".$imageurl;
          $imageurl=mb_ereg_replace("images","bigimg",$imageurl,"im");
          $imagetitle = trim($itemimg->getAttribute("alt"));
          $imagetitle = text_cleanSpaces($imagetitle);
          $media["image"][] = array(loadItemImage($imageurl),$imagetitle);
        }
      }
    }    

    $title = $inneritems->item(0)->firstChild->textContent;
    $title = text_cleanSpaces($title);

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }


  echo "Възможни ".count($query)." нови новини\n";
  $itemids = saveItems($query);
  queueTweets($itemids,'GovBulgaria');
}

function govNovini2() {
  echo "> Проверявам новини на кабинета\n";
  setSession(3,5);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0212&g=",5);
  if (!$html) return;
  $xpath = gov_xpath($html);
  if (!$xpath) return;  
  $items = $xpath->query("//table[@cellpadding=1]");
  if (!$items) return;  

  echo "Открити ".$items->length." новини\n";

  $query=array();
	foreach ($items as $item) {
    $inneritems = $xpath->query(".//td",$item);
    if ($inneritems->length!=4)
      continue;

    $date = $inneritems->item(1)->textContent;
    $date = text_bgMonth($date);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = "http://www.government.bg".$inneritems->item(0)->firstChild->getAttribute("href");
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $description=null;
    $media=null;
    $htmlsub = loadURL($url,3);
    $xpathsub = gov_xpath($htmlsub);
    $itemsub = $xpathsub->query("//table[./tbody/tr/td/font[@style='FONT-SIZE: 11px; TEXT-TRANSFORM: uppercase']]");
    if ($itemsub->length>0) {
      $description = $itemsub->item(0)->C14N();
      $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
      $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
      $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
      $description = text_cleanSpaces($description);
	    $description = html_entity_decode($description);

      $itemimgs = $xpathsub->query(".//img",$itemsub->item(0));
      if ($itemimgs->length>0) {
        $media = array("image" => array());
        foreach ($itemimgs as $itemimg){
          $imageurl = $itemimg->getAttribute("src");
          if (strpos($imageurl,"government.bg")===false)
            $imageurl="http://www.government.bg/".$imageurl;
          $imageurl=mb_ereg_replace("images","bigimg",$imageurl,"im");
          $imagetitle = trim($itemimg->getAttribute("alt"));
          $imagetitle = text_cleanSpaces($imagetitle);
          $media["image"][] = array(loadItemImage($imageurl),$imagetitle);
        }
      }
    } 

    $title = $inneritems->item(0)->firstChild->textContent;
    $title = text_cleanSpaces($title);

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }

  echo "Възможни ".count($query)." нови новини\n";
  $itemids = saveItems($query);
  queueTweets($itemids,'GovBulgaria');
}

function govPorachki() {
  echo "> Проверявам за съобщения за обществени поръчки на кабинета\n";
  setSession(3,6);

  $html = loadURL("http://www.government.bg/cgi-bin/e-cms/vis/vis.pl?s=001&p=0235&g=",6);
  if (!$html) return;
  $items = gov_xpathDoc($html,"//table[.//a[@class='header']/text()='Обществени поръчки до 1.10.2014']//td[@valign='top']/a[@target='_self']");

  echo "Открити ".$items->length." съобщения за обществени поръчки\n";
  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->textContent);
    $title = $item->childNodes->item(1)->textContent;
    $title = text_cleanSpaces($title);
    $url = "http://www.government.bg".$item->getAttribute("href");
    $query[]=array($title,null,null,$url,$hash);
    if (count($query)>=20)
      break;
  }

  echo "Възможни ".count($query)." нови съобщения за обществени поръчки\n";
  $itemids = saveItems($query);
  queueTweets($itemids,'GovBulgaria');
}


/*
------------------------------------------------------------------------
*/

function gov_xpath($html) {
  if (!$html)
    return null;
  $html = mb_convert_encoding($html, 'HTML-ENTITIES', "cp1251");
  $doc = new DOMDocument("1.0", "cp1251");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);
}

function gov_xpathDoc($html,$q) {
  $xpath = gov_xpath($html);

  if ($xpath==null)
    return array();

  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}

?>
