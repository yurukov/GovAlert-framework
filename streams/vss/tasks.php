<?php

/*

0: дневен ред http://www.vss.justice.bg/bg/schedule/1.htm
1: протоколи http://www.vss.justice.bg/bg/decisions/2014/1.htm
2: новини http://www.vss.justice.bg/bg/press/2014/2014.htm

*/

function vssDnevenRed() {
  echo "> Проверявам за дневен ред на ВСС\n";
  setSession(9,0);

  $baseurl = vssGetLink("Дневен ред");
  $html = loadURL($baseurl,0);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//td//a[@class='link']");
  $baseurl=substr($baseurl,0,strrpos($baseurl,"/")+1);

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->textContent);
    $date = "20".mb_substr($date,-2)."-".mb_substr($date,-5,2)."-".mb_substr($date,-8,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $url = $baseurl.$item->getAttribute("href");
    $hash = md5($url);

    $html1 = loadURL($url);
    if (!$html1) continue;
    $html1 = mb_convert_encoding($html1, 'UTF-8', 'cp1251');
    mb_ereg_search_init($html1);
    mb_ereg_search(">\s+(\d+)\. ");
    $points="";
    while ($match = mb_ereg_search_regs())
      if (count($match)==2)
        $points=intval($match[1]);
    if ($points!="")
       $points="от $points точки ";

    $title = $item->textContent;
    $title = vss_cleanText($title);
    $title = mb_ereg_replace("№ ","№",$title,"im");
    $title = mb_ereg_replace(" "," на ",$title,"im");
    $title = "Публикуван е дневният ред ".$points."за заседание ".$title;

    $query[]=array($title,null,'now',$url,$hash);
  }
  echo "Възможни ".count($query)." нов запис за дневен ред\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function vssProtokol() {
  echo "> Проверявам за протоколи на ВСС\n";
  setSession(9,1);

  $baseurl = vssGetLink("Протоколи");
  $html = loadURL($baseurl,1);
  if (!$html) return;
  $items = vss_xpathDoc($html,"//td//a[@class='link']");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->textContent);
    $date = "20".mb_substr($date,-2)."-".mb_substr($date,-5,2)."-".mb_substr($date,-8,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;

    $title = $item->textContent;
    $title = vss_cleanText($title);
    $title = mb_ereg_replace("№ ","№",$title,"im");
    $title = mb_ereg_replace(" "," на ",$title,"im");
    $title = "Публикуван е протокол от заседание ".$title;

    $url = $baseurl.$item->getAttribute("href");
    $hash = md5($url);

    $query[]=array($title,null,'now',$url,$hash);
  }
  echo "Възможни ".count($query)." нови протоколи\n";
  $query=array_reverse($query);
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function vssNovini() {
  echo "> Проверявам за новини на ВСС\n";
  setSession(9,2);

  $baseurl = "http://www.vss.justice.bg/bg/press/".date("Y")."/";
  $mainurl = $baseurl.date("Y").".htm";
  $html = loadURL($mainurl,2);
  if (!$html) return;
  $xpath = vss_xpath($html);
  if (!$xpath) return;
  $items = $xpath->query("//td/div");

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->childNodes->item(1)->textContent);
    $date = vss_cleanText($date);
    $date = text_bgMonth($date);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-1 week"))
      continue;

    $item->removeChild($item->childNodes->item(1));
    $title = $item->textContent;
    $title = mb_ereg_replace("“|„|”","",$title);
    $title = vss_cleanText($title);

    if (mb_strpos($title,'юлетин за дейността')!==false) {
      $links = $xpath->query(".//a",$item);
      if ($links->length>0) {
        $url = $baseurl.$links->item(0)->getAttribute("href");
        $hash = md5($url);
        $query[]=array($title,null,$date,$url,$hash);
        continue;
      }
    }

    $url = $mainurl;
    $hash = md5($url.$title);
    if (!checkHash($hash))
      continue;

    $description = trim($item->C14N());
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|face|align|img)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
    $description = text_cleanSpaces($description);
    $description = html_entity_decode($description);

    $media=array("image" => array());
    $imgs = $xpath->query(".//img[not(src)]",$item);
    if ($imgs->length>0) {
      $name=$imgs->item(0)->getAttribute("name");
      $name=str_replace("show","images",$name);
      if (mb_strpos($html,"$name=new Array(")!==false) {
          $medialiststart = mb_strpos($html,"$name=new Array(")+mb_strlen("$name=new Array(");
          $medialist=mb_substr($html,$medialiststart,mb_strpos($html,");",$medialiststart)-$medialiststart);
          $medialist=explode(",",str_replace(array('"',"'"),"",$medialist));
          foreach ($medialist as $mediafile) {
            $imageurl = loadItemImage($baseurl.$mediafile);
            if ($imageurl)
              $media["image"][] = array($imageurl);
          }
       }
    }
    if (count($media["image"])==0)
      $media=null;

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нови новини\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}


/*
-----------------------------------------------------------------
*/

function vssGetLink($which) {
  $html = loadURL("http://www.vss.justice.bg/bg/sessions.htm");
  $html = mb_convert_encoding($html, 'UTF-8', 'cp1251');
  $pos = mb_strpos($html,$which."|")+mb_strlen($which)+1;
  return "http://www.vss.justice.bg/bg/".mb_substr($html,$pos,mb_strpos($html,"\"",$pos)-$pos);
}

function vss_xpath($html) {
  if (!$html)
    return null;
  $html = mb_convert_encoding($html, 'UTF-8', 'cp1251');
  $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
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
  $text = text_cleanSpaces($text);
	$text = html_entity_decode($text);
	return $text;
}

?>
