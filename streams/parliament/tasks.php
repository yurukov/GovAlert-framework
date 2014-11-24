<?php

/*

0 законопроекти http://parliament.bg/bg/bills
1 програма парламентарен контрол http://parliament.bg/bg/parliamentarycontrol
2 програма пленарно заседание http://parliament.bg/bg/plenaryprogram
3 закони http://parliament.bg/bg/laws
4 документи за пленарна зала http://parliament.bg/bg/doc
5/6 решения http://parliament.bg/bg/desision/period
7 събития http://parliament.bg/bg/calendar
8/9 декларации http://parliament.bg/bg/declaration
10 нови комисии http://parliament.bg/bg/parliamentarycommittees

- комисии - заседания http://parliament.bg/bg/parliamentarycommittees/members/2289/sittings
- комисии - новини http://parliament.bg/bg/parliamentarycommittees/members/2289/news
- комисии - документи http://parliament.bg/bg/parliamentarycommittees/members/2289/documents
- комисии - доклади http://parliament.bg/bg/parliamentarycommittees/members/2290/reports/period/2014-11
- комисии - стенограми http://parliament.bg/bg/parliamentarycommittees/members/2289/steno/period/2014-11

*/

function parlZakonoproekti() {
  echo "> Проверявам за законопроекти в НС\n";
  setSession(4,0);

  $html = loadURL("http://parliament.bg/bg/bills",0);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//table[@class='billsresult']//tr[not(@class)]");
  if (is_null($items)) return;

  $queryGov=array();
  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->childNodes->item(0)->childNodes->item(1)->getAttribute("href"));
    $date = trim($item->childNodes->item(4)->textContent);
    $date = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $url = $item->childNodes->item(0)->childNodes->item(1)->getAttribute("href");
    $url = "http://parliament.bg$url";
    $title = $item->childNodes->item(0)->textContent;
    if (mb_strlen($title)>100) {
      $title = mb_ereg_replace("Законопроект за изменение и допълнение","ЗпИД",$title,"im");
      $title = mb_ereg_replace("Законопроект","Зп",$title,"im");
    } 
    $title = mb_ereg_replace("ЗИД","ЗпИД",$title,"im");
    $title = parl_cleanText($title);

    $importer = parl_cleanText($item->childNodes->item(6)->textContent);
    $importer = mb_convert_case($importer,MB_CASE_LOWER);

    if ($importer=="министерски съвет")
      $queryGov[]=array($title,null,$date,$url,$hash);
    else
      $query[]=array($title,null,$date,$url,$hash);
  }

  echo "Възможни ".(count($query)+count($queryGov))." нови законопроекта\n";

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie',true);

  $itemids = saveItems($queryGov);
  queueTweets($itemids,'narodnosabranie',array("GovAlertEU","GovBulgaria"));

}

function parlParlamentarenKontrol() {
  echo "> Проверявам за парламентарен контрол в НС\n";
  setSession(4,1);

  $html = loadURL("http://parliament.bg/bg/parliamentarycontrol",1);
  if (!$html) return;

  if (mb_strpos($html,"Програмата ще бъде публикувана")!==false)
    return;

  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@class='rightinfo']/ul[@class='frontList']/li/a");
  if (is_null($items)) return;

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->getAttribute("href"));
    $url = $item->getAttribute("href");
    $title = $item->textContent;
    $title = substr($title,10)." - програма за ".substr($title,0,2).".".substr($title,3,2).".".substr($title,6,4);
    $title = parl_cleanText($title);
    $query[]=array($title,null,"now","http://parliament.bg$url",$hash);
  }

  echo "Възможни ".count($query)." нови точки\n";

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie');
}

function parlPlenarnoZasedanie() {
  echo "> Проверявам за пленарно заседание в НС\n";
  setSession(4,2);

  $html = loadURL("http://parliament.bg/bg/plenaryprogram",2);
  if (!$html) return;

  if (mb_strpos($html,"Програмата ще бъде публикувана")!==false)
    return;

  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;

  $items = $xpath->query("//div[@class='marktitle']/div[@class='dateclass']");
  if (is_null($items) || $items->length!=1) return;
  $dates = $items->item(0)->textContent;
  $dates = parl_cleanText(str_replace("/",".",$dates));
  $dates = substr($dates,0,5)."-".substr($dates,13);

  $items = $xpath->query("//div[@class='markframe']//ol[@class='frontList']/li");
  if (is_null($items)) return;
  $count = $items->length;
  if ($count==0)
    $count = "";
  elseif ($count==1)
    $count = " от една точка";
  else
    $count = " oт $count точки";
  $title = "Програма за работата на Народното събрание в периода $dates$count";

  $items = $xpath->query("//div[@class='markframe']");
  if (is_null($items) || $items->length==0) return;
  $description = $items->item(0)->C14N();
  $hash = md5($description);

  $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
  $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
  $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>|</?img.*?>","",$description);
  $description = parl_cleanText($description);

  $items = $xpath->query("//div[@class='rightinfo']/ul[@class='frontList']/li/a");
  if (is_null($items) || $items->length==0) return;
  $url = $items->item(0)->getAttribute("href");

  $itemids = saveItem($title,$description,"now","http://parliament.bg$url",$hash);
  queueTweets($itemids,'narodnosabranie');
}

function parlZakoni() {
  echo "> Проверявам за закони в НС\n";
  setSession(4,3);

  $html = loadURL("http://parliament.bg/bg/laws",3);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//table[@class='billsresult']//tr[not(@class)]");
  if (is_null($items)) return;

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->childNodes->item(0)->childNodes->item(1)->getAttribute("href"));
    $date = trim($item->childNodes->item(2)->textContent);
    $date = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $url = $item->childNodes->item(0)->childNodes->item(1)->getAttribute("href");
    $title_c = $item->childNodes->item(4)->textContent;
    $title_c = parl_cleanText($title_c);
    $title = $item->childNodes->item(0)->textContent;
    if (mb_strlen($title)>88)
      $title = mb_ereg_replace("Закон за изменение и допълнение","ЗИД",$title,"im");
    $title = "ДВ-$title_c/ ".parl_cleanText($title);
    $query[]=array($title,null,$date,"http://parliament.bg$url",$hash);
  }

  echo "Възможни ".count($query)." нови закони\n";

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie',true);
}

function parlDokumentiZala() {
  echo "> Проверявам за документи в зала в НС\n";
  setSession(4,4);

  $html = loadURL("http://parliament.bg/bg/doc",4);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//ul[@class='frontList1']/li/a");
  if (is_null($items)) return;

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->getAttribute("href"));
    $url = $item->getAttribute("href");
    $title = $item->textContent;
    $title = parl_cleanText($title);
    $title = str_replace("/",".",$title);
    $title = "Качени са документите за пленарна зала за $title";
  
    $date = mb_substr($item->textContent,-10);
    $date = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    if (strtotime($date)>strtotime("-1 week")) {
      $conn_id = ftp_connect("193.109.55.85");
      if (!$conn_id) continue;
      $login_result = ftp_login($conn_id, "anonymous", "");
      if (!$login_result) continue;
      $contents = ftp_nlist($conn_id, substr($url,-11));
      if (!$contents || count($contents)==0)
        continue;
    } else 
      continue;

    $query[]=array($title,null,'now',$url,$hash);
  }
  echo "Възможни ".count($query)." нови документа\n";

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie');
}

function parlResheniq() {
  echo "> Проверявам за решения в НС\n";
  setSession(4,5);

  $html = loadURL("http://parliament.bg/bg/desision/period",5);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@class='calendar_columns' and h4/text()='".date("Y")."']//li/a");
  if (is_null($items)) return;
	$lasturl = $items->item($items->length-1)->getAttribute("href");

  $html = loadURL("http://parliament.bg$lasturl",6);
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@id='monthview']//li");
  if (is_null($items)) return;

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->childNodes->item(0)->getAttribute("href"));
    $date = trim($item->childNodes->item(1)->textContent);
    $date = substr($date,8,4)."-".substr($date,5,2)."-".substr($date,2,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $url = $item->childNodes->item(0)->getAttribute("href");
    $title = $item->childNodes->item(0)->textContent;
    $title = parl_cleanText($title);
    $query[]=array($title,null,$date,"http://parliament.bg$url",$hash);
  }

  echo "Възможни ".count($query)." нови решения\n";

  $query = array_reverse($query);
  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie');
}

function parlSabitiq() {
  echo "> Проверявам за събития в НС\n";
  setSession(4,6);

  $html = loadURL("http://parliament.bg/bg/calendar",7);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@class='markframe']//*[local-name()='div' or local-name()='li']");
  if (is_null($items)) return;

  $currentDateT=false;
  $currentDate=false;
  $query=array();
	foreach ($items as $item) {
    if ($item->nodeName=='div') {
      if ($currentDate!=false && count($query)>0) {
        $query = array_reverse($query);
        $itemids = saveItems($query);
        if (count($itemids)<=3)
          queueTweets($itemids,'narodnosabranie');
        else
          queueTextTweet("Планирани са ".count($itemids)." нови събития за $currentDateT","http://parliament.bg/bg/calendar",'narodnosabranie');  
      }

      $currentDate = $item->textContent;
      $currentDate = substr($currentDate,-10,2).".".substr($currentDate,-7,2);
      $currentDateT = $item->textContent;
      $currentDateT = str_replace("/",".",$currentDateT);

      $query=array();
    } else {
      if ($currentDate==false)
        reportError("Грешка в събитията на парламента");
      $time = trim($item->childNodes->item(1)->textContent);
      $date = "$currentDate $time";
      if (strtotime($date)<time())
        continue;

      if ($item->childNodes->item(3)->nodeName=="a")
        $url = $item->childNodes->item(3)->getAttribute("href");
      else
        $url = "/bg/calendar#".$item->childNodes->item(0)->getAttribute("name");
      $hash = md5($url);
      $item->removeChild($item->childNodes->item(1));
      $item->removeChild($item->childNodes->item(0));
      $title = $item->textContent;
      $title = parl_cleanText($title);
      $title = "Събитие [$date] $title";
      $description = $item->C14N();
      $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
      $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
      $description = mb_ereg_replace("<br>[  ]*</br>|<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
      $description = parl_cleanText($description);
      $query[]=array($title,$description,"now","http://parliament.bg$url",$hash);
    }
  }

  if ($currentDate!=false && count($query)>0) {
    echo "Възможни ".count($query)." нови събития\n";
    $query = array_reverse($query);
    $itemids = saveItems($query);
    if (count($itemids)<=5)
      queueTweets($itemids,'narodnosabranie');
    else
      queueTextTweet("Планирани са ".count($itemids)." нови събития за $currentDateT","http://parliament.bg/bg/calendar",'narodnosabranie');  
  }
}

function parlDeklaracii() {
  echo "> Проверявам за декларации в НС\n";
  setSession(4,7);

  $html = loadURL("http://parliament.bg/bg/declaration",8);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@class='calendar_columns' and h4/text()='".date("Y")."']//li/a");
  if (is_null($items)) return;
	$lasturl = $items->item($items->length-1)->getAttribute("href");

  $html = loadURL("http://parliament.bg$lasturl",9);
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//div[@id='monthview']//li");
  if (is_null($items)) return;

  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->childNodes->item(0)->getAttribute("href"));
    $date = trim($item->childNodes->item(1)->textContent);
    $date = substr($date,8,4)."-".substr($date,5,2)."-".substr($date,2,2);
    if (strtotime($date)<strtotime("-1 month"))
      continue;
    $url = $item->childNodes->item(0)->getAttribute("href");
    $title = $item->childNodes->item(0)->textContent;
    $title = parl_cleanText($title);
    $query[]=array($title,null,$date,"http://parliament.bg$url",$hash);
  }
  echo "Възможни ".count($query)." нови декларации\n";
  $query = array_reverse($query);
  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie',true);
}

function parlKomisii() {
  global $link;

  echo "> Проверявам за комисии в НС\n";
  setSession(4,8);

  $html = loadURL("http://parliament.bg/bg/parliamentarycommittees",10);
  if (!$html) return;
  $xpath = parl_xpathDoc($html);
  if (!$xpath) return;
  $items = $xpath->query("//label[@for]/a");

  $commissionids = array();
  $res=$link->query("SELECT committee_id FROM s_parliament_committees order by committee_id") or reportDBErrorAndDie();
  while ($row = $res->fetch_array()) {
    $commissionids[]=$row[0];
  }
  $res->free();

  $commissions = array();
	foreach ($items as $item) {
    $id = $item->getAttribute("href");
    $id = substr($id,strrpos($id,'/')+1);
    $id = intval($id);
    if (in_array($id,$commissionids))
      continue;
    $title = parl_cleanText($item->textContent);
    $title = $link->escape_string($item->textContent);
    $commissions[]=array($id,$title);
  }
  if (count($commissions)==0)
    return;

  echo "Има ".count($commissions)." нови комисии\n";

  $query=array();
  foreach ($commissions as $commission) {
    $link->query("insert LOW_PRIORITY ignore into s_parliament_committees (committee_id,name) value (".$commission[0].",'".$commission[1]."')")
      or reportDBErrorAndDie();
    $title="Нова комисия: ".$commission[1];
    $url = "http://parliament.bg/bg/parliamentarycommittees/members/".$commission[0];
    $hash = md5($url);
    $query[]=array($title,null,'now',$url,$hash);
  }
  $query = array_reverse($query);
  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie',true);
}

function parlKomisiiZasedaniq() {
  global $link;

  echo "> Проверявам за заседания на комисии в НС\n";
  setSession(4,9);

  $checkUrls = array();
  $res=$link->query("SELECT committee_id FROM s_parliament_committees order by committee_id") or reportDBErrorAndDie();
  while ($row = $res->fetch_array()) {
    $checkUrls[]="http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/sittings/period/".date("Y-m");
    $checkUrls[]="http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/sittings/period/".date("Y-m",strtotime("+1 month"));
  }
  $res->free();

  $query = array();
  foreach ($checkUrls as $checkUrl) {
    $html = loadURL($checkUrl);
    if (!$html) continue;
    $xpath = parl_xpathDoc($html);
    if (!$xpath) continue;
    $items = $xpath->query("//div[@id='monthview']//li/a");

	  foreach ($items as $item) {
      $url = 'http://parliament.bg'.$item->getAttribute("href");
      $hash = md5($url);
      if (!checkHash($hash))
        continue;

      $html1 = loadURL($url);
      if (!$html1) continue;
      $xpath1 = parl_xpathDoc($html1);
      if (!$xpath1) continue;

      $items1 = $xpath1->query("//div[@class='marktitle']");
      $title = parl_cleanText($items1->item(0)->firstChild->textContent);
      $items1 = $xpath1->query("//div[@class='marktitle']/div[@class='dateclass']");
      $dateF = parl_cleanText($items1->item(0)->firstChild->textContent);
      $dateF = str_replace("/",".",str_replace(", "," от ",$dateF));

      $title = "Заседание на $dateF на $title";
      $query[]=array($title,null,'now',$url,$hash);
    }
  }

  echo "Възможни ".count($query)." нови заседания\n";
  $query = array_reverse($query);

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie',true);
}

function parlKomisiiNovini() {
  global $link;

  echo "> Проверявам за новини на комисии в НС\n";
  setSession(4,10);

  $checkUrls = array();
  $res=$link->query("SELECT committee_id FROM s_parliament_committees order by committee_id") or reportDBErrorAndDie();
  while ($row = $res->fetch_array()) {
    $checkUrls[]="http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/news/period/".date("Y-m");
    $checkUrls[]="http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/news/period/".date("Y-m",strtotime("-1 month"));
  }
  $res->free();

  $query = array();
  foreach ($checkUrls as $checkUrl) {
    $html = loadURL($checkUrl);
    if (!$html) continue;
    $xpath = parl_xpathDoc($html);
    if (!$xpath) continue;
    $items = $xpath->query("//div[@id='monthview']//li/a");

	  foreach ($items as $item) {
      $url = 'http://parliament.bg'.$item->getAttribute("href");
      $hash = md5($url);
      if (!checkHash($hash))
        continue;

      $title = parl_cleanText($item->textContent);
      $title = "Новина от комисия: $title";
      $query[]=array($title,null,'now',$url,$hash);
    }
  }

  echo "Възможни ".count($query)." нови новини\n";
  $query = array_reverse($query);

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie',true);
}

function parlKomisiiDokumenti() {
  global $link;

  echo "> Проверявам за документи на комисии в НС\n";
  setSession(4,11);

  $checkUrls = array();
  $res=$link->query("SELECT committee_id, name FROM s_parliament_committees order by committee_id") or reportDBErrorAndDie();
  while ($row = $res->fetch_array()) {
    $commName = $row[1];
    $html = loadURL("http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/documents");
    if (!$html) continue;
    $xpath = parl_xpathDoc($html);
    if (!$xpath) continue;
    $items = $xpath->query("//div[@class='markframe']//div[@class='MProw']/a");

    $query = array();
	  foreach ($items as $item) {
      $url = 'http://parliament.bg'.$item->getAttribute("href");
      $hash = md5($url);
      if (!checkHash($hash))
        continue;

      $title = parl_cleanText($item->textContent);
      $title = "Документ в комисия: $title";
      $query[]=array($title,null,'now',$url,$hash);
    }
    echo "Възможни ".count($query)." нови документи\n";

    $itemids = saveItems($query);
    if (count($itemids)<=4)
      queueTweets($itemids,'narodnosabranie');
    else
      queueTextTweet("Качени са ".count($itemids)." нови документа в $commName","http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/documents",'narodnosabranie');  
  }
  $res->free();
}


function parlKomisiiDokladi() {
  global $link;

  echo "> Проверявам за доклади на комисии в НС\n";
  setSession(4,12);

  $checks = array();
  $res=$link->query("SELECT committee_id, name FROM s_parliament_committees order by committee_id") or reportDBErrorAndDie();
  while ($row = $res->fetch_array()) {
    $checks[]=array("http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/reports/period/".date("Y-m"),$row[01]);
    $checks[]=array("http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/reports/period/".date("Y-m",strtotime("-1 month")),$row[1]);
  }
  $res->free();

  $query = array();
  foreach ($checks as $check) {
    $html = loadURL($check[0]);
    if (!$html) continue;
    $xpath = parl_xpathDoc($html);
    if (!$xpath) continue;
    $items = $xpath->query("//div[@id='monthview']//li");

	  foreach ($items as $item) {
      $url = 'http://parliament.bg'.$item->firstChild->getAttribute("href");
      $hash = md5($url);
      if (!checkHash($hash))
        continue;

      $dateP = parl_cleanText($item->lastChild->textContent);
      $dateP = substr(str_replace("/",".",$dateP),2);

      $title = "Доклад от заседанието на $dateP на ".$check[1];
      $query[]=array($title,null,'now',$url,$hash);
    }
  }

  echo "Възможни ".count($query)." нови доклади\n";
  $query = array_reverse($query);

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie');
}

function parlKomisiiStenogrami() {
  global $link;

  echo "> Проверявам за стенограми на комисии в НС\n";
  setSession(4,13);

  $checks = array();
  $res=$link->query("SELECT committee_id, name FROM s_parliament_committees order by committee_id") or reportDBErrorAndDie();
  while ($row = $res->fetch_array()) {
    $checks[]=array("http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/steno/period/".date("Y-m"),$row[01]);
    $checks[]=array("http://parliament.bg/bg/parliamentarycommittees/members/".$row[0]."/steno/period/".date("Y-m",strtotime("-1 month")),$row[1]);
  }
  $res->free();

  $query = array();
  foreach ($checks as $check) {
    $html = loadURL($check[0]);
    if (!$html) continue;
    $xpath = parl_xpathDoc($html);
    if (!$xpath) continue;
    $items = $xpath->query("//div[@id='monthview']//li");

	  foreach ($items as $item) {
      $url = 'http://parliament.bg'.$item->firstChild->getAttribute("href");
      $hash = md5($url);
      if (!checkHash($hash))
        continue;

      $dateP = parl_cleanText($item->lastChild->textContent);
      $dateP = substr(str_replace("/",".",$dateP),2);

      $title = "Стенограма от заседанието на $dateP на ".$check[1];
      $query[]=array($title,null,'now',$url,$hash);
    }
  }

  echo "Възможни ".count($query)." нови стенограми\n";
  $query = array_reverse($query);

  $itemids = saveItems($query);
  queueTweets($itemids,'narodnosabranie');
}

/*
-----------------------------------------------------------------
*/

function parl_xpathDoc($html) {
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

function parl_cleanText($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	$text = html_entity_decode($text);
	return $text;
}

?>
