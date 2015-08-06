<?php

/*

0: съобщения http://bnb.bg/PressOffice/POPressReleases/POPRDate/index.htm
1: платежен баланс http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSBalancePayments/index.htm
2: брутен външен дълг http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSGrossExternalDebt/index.htm
3: парични депозити и кредитни показатели http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSMonetaryStatistics/POPRSMonetarySurvey/index.htm
4: Депозити и кредити по количествени категории и икономически дейности http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSMonetaryStatistics/POPRSDepositsLoans/index.htm
5: лихвена статистика http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSInterestRate/index.htm
6: лизингови дружества http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSLeasingCompanies/index.htm
7: инвестиционни фондове http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSInvestmentFonds/index.htm
8: Дружества, специализирани в кредитиране http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSLendingCorporations/index.htm
9: Статистика на застрахователната дейност http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSInsuranceCompanies/index.htm

*/

function bnb_Saobshtenia() {
  echo "> Проверявам за съобщения в БНБ\n";
  setSession(15,0);

  $html = loadURL("http://bnb.bg/PressOffice/POPressReleases/POPRDate/index.htm",0);
  if (!$html) return;
  $items = bnb_xpathDoc($html,"//div[@id='main']//h3/a");
  if (!$items || $items->length==0) {
    reportError("Грешка при зареждане на страницата");
    return;
  }

  $query=array();
	foreach ($items as $item) {
    $date = trim($item->textContent);
    $date = mb_substr($date,6,4)."-".mb_substr($date,3,2)."-".mb_substr($date,0,2);
    if (strtotime($date)<strtotime("-3 day"))
      continue;
    $url = "http://bnb.bg/PressOffice/POPressReleases/POPRDate/".$item->getAttribute("href");
    $hash = md5($url);
  
    $html1 = loadURL($url);
    if (!$html1) return;
    $items1 = bnb_xpathDoc($html1,"//div[@class='doc_entry']");
    if (!$items1 || $items1->length==0) {
      reportError("Грешка при зареждане на отделно съобщение");
      return;
    }
    $title = $items1->item(0)->textContent;
    $title = bnb_cleanText($title);
    if (mb_strpos($title,"ПРЕССЪОБЩЕНИЕ")!==false) {
      if (mb_strpos($title,"г.")!==null && mb_strpos($title,"г.")<50)
        $title=mb_substr($title,mb_strpos($title,"г.")+3);
      else
        $title=mb_substr($title,14);
    }  
    $title = "Съобщение: $title";

    $description = $items1->item(0)->C14N();
    $description = bnb_cleanDescr($description);

    $query[]=array($title,$description,$date,$url,$hash);
  }
  echo "Възможни ".count($query)." нови съобщения\n";
  $itemids = saveItems($query);
  queueTweets($itemids);
}

function bnb_PlatejenBalans() {
  statsHandling(1,"Платежен баланс","ПЛАТЕЖЕН БАЛАНС",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSBalancePayments/");
}

function bnb_BrutenVanshenDalg() {
  statsHandling(2,"Брутен външен дълг","БРУТЕН ВЪНШЕН ДЪЛГ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSGrossExternalDebt");
}

function bnb_ParichniDepositi() {
  statsHandling(3,"Парични, депозитни и кредитни показатели","ПАРИЧНИ, ДЕПОЗИТНИ И КРЕДИТНИ ПОКАЗАТЕЛИ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSMonetaryStatistics/POPRSMonetarySurvey");
}

function bnb_KreditiDepositi() {
  statsHandling(4,"Депозити и кредити по количествени категории и икономически дейности","ДЕПОЗИТИ И КРЕДИТИ ПО КОЛИЧЕСТВЕНИ КАТЕГОРИИ И ИКОНОМИЧЕСКИ ДЕЙНОСТИ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSMonetaryStatistics/POPRSDepositsLoans");
}

function bnb_LihvenaStatistika() {
  statsHandling(5,"Лихвена статистика","ЛИХВЕНА СТАТИСТИКА",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSInterestRate");
}

function bnb_LizingoviDrujestva() {
  statsHandling(6,"Статистика на лизинговата дейност","СТАТИСТИКА НА ЛИЗИНГОВАТА ДЕЙНОСТ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSLeasingCompanies");
}

function bnb_InvesticionniFondove() {
  statsHandling(7,"Статистика на инвестиционните фондове","СТАТИСТИКА НА ИНВЕСТИЦИОННИТЕ ФОНДОВЕ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSInvestmentFonds");
}

function bnb_KoreditiraneDrujestva() {
  statsHandling(8,"Дружества, специализирани в кредитиране","СТАТИСТИКА НА ДРУЖЕСТВАТА, СПЕЦИАЛИЗИРАНИ В КРЕДИТИРАНЕ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSLendingCorporations");
}

function bnb_ZastrahovatelnaDeinost() {
  statsHandling(9,"Статистика на застрахователната дейност","СТАТИСТИКА НА ЗАСТРАХОВАТЕЛНАТА ДЕЙНОСТ",
    "http://bnb.bg/PressOffice/POStatisticalPressReleases/POPRSInsuranceCompanies");
}



/*
-----------------------------------------------------------------
*/

function statsHandling($category,$tweet,$titleBig,$url) {
  echo "> Проверявам за $tweet в БНБ\n";
  setSession(15,$category);

  $html = loadURL("$url/index.htm",$category);
  if (!$html) return;
  $items = bnb_xpathDoc($html,"//div[@id='main']//h4/a");
  if (!$items || $items->length==0) {
    reportError("Грешка при зареждане на страницата");
    return;
  }
  $query=array();
	foreach ($items as $item) {
    $url = $url."/".$item->getAttribute("href");
    $hash = md5($url);
  
    $html1 = loadURL($url);
    if (!$html1) return;
    $xpath1 = bnb_xpath($html1);
    if (!$xpath1) {
      reportError("Грешка при зареждане на отделно съобщение");
      return;
    }
    $items1 = $xpath1->query("//div[@class='doc_entry']");
    if (!$items1 || $items1->length==0) {
      reportError("Грешка при зареждане на отделно съобщение");
      return;
    }
    $date = $items1->item(0)->textContent;
    $date = text_cleanSpaces($date);
    $datepos = mb_strpos($date," ",mb_strlen("ПРЕССЪОБЩЕНИЕ"))+1;
    $date = mb_substr($date,$datepos,mb_strpos($date,"ч.")-$datepos-1);
    $date = text_bgMonth($date);
    $date = explode(" ",$date);
    $date = $date[2]."-".$date[1]."-".$date[0]." ".$date[4];

    $title = $items1->item(0)->textContent;
    $title = explode("\n",$title);
    for ($i=0;$i<count($title);$i++)
      if (mb_substr($title[$i],0,mb_strlen($titleBig))==$titleBig) 
        $title = mb_substr($title[$i+2],0,-3);
    if (mb_strlen($title)>20) {
      reportError("Грешка във формата на страницата");
      return;
    }
    $title = mb_convert_case($title,MB_CASE_LOWER);
    $title = "$tweet за $title";

    $media=null;
    $items2 = $xpath1->query(".//img",$items1->item(0));
    foreach ($items2 as $item2) {
        $imageurl = "http://bnb.bg".$item2->getAttribute("src");
        $imageurl = loadItemImage($imageurl);
        if ($imageurl==null) continue;
        if ($media==null)
          $media = array("image" => array($imageurl,null));
        else {
          if (!is_array($media["image"][0]))
            $media["image"] = array($media["image"]);
          $media["image"][] = array($imageurl,null);
        }
    }

    $description = $items1->item(0)->C14N();
    $description = bnb_cleanDescr($description);

    $query[]=array($title,$description,$date,$url,$hash,$media);
  }
  echo "Възможни ".count($query)." нов $tweet\n";

  $itemids = saveItems($query);
  queueTweets($itemids);
}

function bnb_xpathDoc($html,$q) {
  $xpath = bnb_xpath($html);  
  if (!$xpath)
    return array();
  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}

function bnb_xpath($html) {
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

function bnb_cleanText($text) {
	$text = html_entity_decode($text);
  $text = text_cleanSpaces($text);
	return $text;
}

function bnb_cleanDescr($description) {
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|bordercolor)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<span>[  ]*</span>|<comment>[  ]*</comment>|<a>[  ]*</a>|<div>[  ]*</div>|<img[^>]*?></img>|<img[^>]*?/>","",$description);
    $description = bnb_cleanText($description);
    return $description;
}


?>
