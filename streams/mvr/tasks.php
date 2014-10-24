<?php
/*
Links
0: новини http://press.mvr.bg/default.htm
1: кампании http://press.mvr.bg/Kampanii/default.htm
2: благоевград http://www.blagoevgrad.mvr.bg/Prescentar/Novini/default.htm
3: благоевград издирвани http://www.blagoevgrad.mvr.bg/Prescentar/Izdirvani_lica/default.htm
4: бургас ------ http://www.rdvr-burgas.org/Bul/Suobshtenie/Realno.htm
5: варна http://varna.mvr.bg/Prescentar/Novini/default.htm
6: велико търново http://www.veliko-tarnovo.mvr.bg/Prescentar/Novini/default.htm
7: велико търново изчезнали http://www.veliko-tarnovo.mvr.bg/Prescentar/Izdirvani_lica/default.htm
8: видин http://www.vidin.mvr.bg/PressOffice/News/default.htm
9: видин изчезнали http://www.vidin.mvr.bg/Pressoffice/Izdirvani_lica/default.htm
10: враца http://www.vratza.mvr.bg/PressOffice/News/default.htm
11: враца изчезнали http://www.vratza.mvr.bg/Pressoffice/Izdirvani_lica/default.htm
12: габрово http://www.gabrovo.mvr.bg/PressOffice/News/default.htm
13: габрово изчезнали http://www.gabrovo.mvr.bg/PressOffice/Wanted/default.htm
14: добрич http://dobrich.mvr.bg/Prescentar/Novini/default.htm
15: кърджали http://www.kardjali.mvr.bg/PressOffice/News/default.htm
16: кърджали изчезнали http://www.kardjali.mvr.bg/PressOffice/Izirva_se/default.htm
17: кюстендил http://www.kustendil.mvr.bg/PressOffice/News/default.htm
18: ловеч http://www.lovech.mvr.bg/PressOffice/News/default.htm
19: ловеч изчезнали http://www.lovech.mvr.bg/PressOffice/Wanted/default.htm
20: монтана http://www.montana.mvr.bg/PressOffice/News/default.htm
21: монтана изчезнали http://www.montana.mvr.bg/PressOffice/Wanted/default.htm
22: пазарджик http://pazardjik.mvr.bg/Prescentar/Novini/default.htm
23: пазарджик изчезнали http://pazardjik.mvr.bg/Prescentar/Izdirvani_lica/default.htm
24: перник http://www.pernik.mvr.bg/Prescentar/Novini/default.htm
25: перник изчезнали http://www.pernik.mvr.bg/Prescentar/Izdirvani_lica/default.htm
26: плевен http://www.pleven.mvr.bg/PressOffice/News/default.htm
27: плевен изчезнали http://www.pleven.mvr.bg/PressOffice/Wanted/default.htm
28: пловдив ------ http://plovdiv.mvr.bg/news.php
29: разград http://www.razgrad.mvr.bg/PressOffice/News/default.htm
30: русе http://www.ruse.mvr.bg/Prescentar/Novini/default.htm
31: русе изчезнали http://www.ruse.mvr.bg/Prescentar/Izdirvani_lica/default.htm
32: силистра http://www.silistra.mvr.bg/Prescentar/Novini/default.htm
33: силистра изчезнали http://www.silistra.mvr.bg/Prescentar/Izdirvani_lica/default.htm
34: сливен http://sliven.mvr.bg/Prescentar/Novini/default.htm
35: сливен изчезнали http://sliven.mvr.bg/Prescentar/Izdirvani_lica/default.htm
36: смолян http://www.smolyan.mvr.bg/Prescentar/Novini/default.htm
37: смолян изчезнали http://www.smolyan.mvr.bg/Prescentar/Izdirvani_lica/default.htm
38: софия http://www.odmvr-sofia.mvr.bg/Prescentar/Novini/default.htm
39: стара загора http://www.starazagora.mvr.bg/PressOffice/News/default.htm
40: стара загора изчезнали http://www.starazagora.mvr.bg/PressOffice/Wanted/default.htm
41: търговище http://targovishte.mvr.bg/Prescentar/Novini/default.htm
42: хасково http://haskovo.mvr.bg/Prescentar/Novini/default.htm
43: шумен http://www.shumen.mvr.bg/Prescentar/Novini/default.htm
44: шумен изчезнали http://www.shumen.mvr.bg/Prescentar/Izdirvani_lica/default.htm
45: ямбол http://www.yambol.mvr.bg/Prescentar/Novini/default.htm
46: ямбол изчезнали http://www.yambol.mvr.bg/Izdirvani_lica/default.htm


*/

function mvrNovini() {
  loadMVRpage("","МВР","новини",0,"http://press.mvr.bg/default.htm","http://press.mvr.bg","govalerteu");
}

function mvrKampanii() {
  loadMVRpage("","МВР","кампании",1,"http://press.mvr.bg/Kampanii/default.htm","http://press.mvr.bg");
}

function mvrBlagoevgrad() {
  loadMVRpage("[Благоевград] ","МВР Благоевград","новини",2,"http://www.blagoevgrad.mvr.bg/Prescentar/Novini/default.htm","http://www.blagoevgrad.mvr.bg");
}

function mvrBlagoevgradIzdirvani() {
  loadMVRpage("[Благоевград] ","МВР Благоевград","издирвани",3,"http://www.blagoevgrad.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.blagoevgrad.mvr.bg","lipsva",true);
}

function mvrVarna() {
  loadMVRpage("[Варна] ","МВР Варна","новини",5,"http://varna.mvr.bg/Prescentar/Novini/default.htm","http://varna.mvr.bg");
}

function mvrVelikotarnovo() {
  loadMVRpage("[В.Търново] ","МВР В.Търново","новини",6,"http://www.veliko-tarnovo.mvr.bg/Prescentar/Novini/default.htm","http://www.veliko-tarnovo.mvr.bg");
}

function mvrVelikotarnovoIzdirvani() {
  loadMVRpage("[В.Търново] ","МВР В.Търново","изчезнали",7,"http://www.veliko-tarnovo.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.veliko-tarnovo.mvr.bg","lipsva",true);
}

function mvrVidin() {
  loadMVRpage("[Видин] ","МВР Видин","новини",8,"http://www.vidin.mvr.bg/PressOffice/News/default.htm","http://www.vidin.mvr.bg");
}

function mvrVidinIzdirvani() {
  loadMVRpage("[Видин] ","МВР Видин","изчезнали",9,"http://www.vidin.mvr.bg/Pressoffice/Izdirvani_lica/default.htm","http://www.vidin.mvr.bg","lipsva",true);
}

function mvrVraca() {
  loadMVRpage("[Враца] ","МВР Враца","новини",10,"http://www.vratza.mvr.bg/PressOffice/News/default.htm","http://www.vratza.mvr.bg");
}

function mvrVracaIzdirvani() {
  loadMVRpage("[Враца] ","МВР Враца","изчезнали",11,"http://www.vratza.mvr.bg/Pressoffice/Izdirvani_lica/default.htm","http://www.vratza.mvr.bg","lipsva",true);
}

function mvrGabrovo() {
  loadMVRpage("[Габрово] ","МВР Габрово","новини",12,"http://www.gabrovo.mvr.bg/PressOffice/News/default.htm","http://www.gabrovo.mvr.bg");
}

function mvrGabrovoIzdirvani() {
  loadMVRpage("[Габрово] ","МВР Габрово","изчезнали",13,"http://www.gabrovo.mvr.bg/PressOffice/Wanted/default.htm","http://www.gabrovo.mvr.bg","lipsva",true);
}

function mvrDobrich() {
  loadMVRpage("[Добрич] ","МВР Добрич","новини",14,"http://dobrich.mvr.bg/Prescentar/Novini/default.htm","http://dobrich.mvr.bg");
}

function mvrKardjali() {
  loadMVRpage("[Кърджали] ","МВР Кърджали","новини",15,"http://www.kardjali.mvr.bg/PressOffice/News/default.htm","http://www.kardjali.mvr.bg");
}

function mvrKardjaliIzdirvani() {
  loadMVRpage("[Кърджали] ","МВР Кърджали","изчезнали",16,"http://www.kardjali.mvr.bg/PressOffice/Izirva_se/default.htm","http://www.kardjali.mvr.bg","lipsva",true);
}

function mvrKiustendil() {
  loadMVRpage("[Кюстендил] ","МВР Кюстендил","новини",17,"http://www.kustendil.mvr.bg/PressOffice/News/default.htm","http://www.kustendil.mvr.bg");
}

function mvrLovech() {
  loadMVRpage("[Ловеч] ","МВР Ловеч","новини",18,"http://www.lovech.mvr.bg/PressOffice/News/default.htm","http://www.lovech.mvr.bg");
}

function mvrLovechIzdirvani() {
  loadMVRpage("[Ловеч] ","МВР Ловеч","изчезнали",19,"http://www.lovech.mvr.bg/PressOffice/Wanted/default.htm","http://www.lovech.mvr.bg","lipsva",true);
}

function mvrMontana() {
  loadMVRpage("[Монтана] ","МВР Монтана","новини",20,"http://www.montana.mvr.bg/PressOffice/News/default.htm","http://www.montana.mvr.bg");
}

function mvrMontanaIzdirvani() {
  loadMVRpage("[Монтана] ","МВР Монтана","изчезнали",21,"http://www.montana.mvr.bg/PressOffice/Wanted/default.htm","http://www.montana.mvr.bg","lipsva",true);
}

function mvrPazardjik() {
  loadMVRpage("[Пазарджик] ","МВР Пазарджик","новини",22,"http://pazardjik.mvr.bg/Prescentar/Novini/default.htm","http://pazardjik.mvr.bg");
}

function mvrPazardjikIzdirvani() {
  loadMVRpage("[Пазарджик] ","МВР Пазарджик","изчезнали",23,"http://pazardjik.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://pazardjik.mvr.bg","lipsva",true);
}

function mvrPernik() {
  loadMVRpage("[Перник] ","МВР Перник","новини",24,"http://www.pernik.mvr.bg/Prescentar/Novini/default.htm","http://www.pernik.mvr.bg");
}

function mvrPernikIzdirvani() {
  loadMVRpage("[Перник] ","МВР Перник","изчезнали",25,"http://www.pernik.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.pernik.mvr.bg","lipsva",true);
}

function mvrPleven() {
  loadMVRpage("[Плевен] ","МВР Плевен","новини",26,"http://www.pleven.mvr.bg/PressOffice/News/default.htm","http://www.pleven.mvr.bg");
}

function mvrPlevenIzdirvani() {
  loadMVRpage("[Плевен] ","МВР Плевен","изчезнали",27,"http://www.pleven.mvr.bg/PressOffice/Wanted/default.htm","http://www.pleven.mvr.bg","lipsva",true);
}

function mvrRazgrad() {
  loadMVRpage("[Разград] ","МВР Разград","новини",29,"http://www.razgrad.mvr.bg/PressOffice/News/default.htm","http://www.razgrad.mvr.bg");
}

function mvrRuse() {
  loadMVRpage("[Русе] ","МВР Русе","новини",30,"http://www.ruse.mvr.bg/Prescentar/Novini/default.htm","http://www.ruse.mvr.bg");
}

function mvrRuseIzdirvani() {
  loadMVRpage("[Русе] ","МВР Русе","изчезнали",31,"http://www.ruse.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.ruse.mvr.bg","lipsva",true);
}

function mvrSilistra() {
  loadMVRpage("[Силистра] ","МВР Силистра","новини",32,"http://www.silistra.mvr.bg/Prescentar/Novini/default.htm","http://www.silistra.mvr.bg");
}

function mvrSilistraIzdirvani() {
  loadMVRpage("[Силистра] ","МВР Силистра","изчезнали",33,"http://www.silistra.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.silistra.mvr.bg","lipsva",true);
}

function mvrSliven() {
  loadMVRpage("[Сливен] ","МВР Сливен","новини",34,"http://sliven.mvr.bg/Prescentar/Novini/default.htm","http://sliven.mvr.bg");
}

function mvrSlivenIzdirvani() {
  loadMVRpage("[Сливен] ","МВР Сливен","изчезнали",35,"http://sliven.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://sliven.mvr.bg","lipsva",true);
}

function mvrSmolqn() {
  loadMVRpage("[Смолян] ","МВР Смолян","новини",36,"http://www.smolyan.mvr.bg/Prescentar/Novini/default.htm","http://www.smolyan.mvr.bg");
}

function mvrSmolqnIzdirvani() {
  loadMVRpage("[Смолян] ","МВР Смолян","изчезнали",37,"http://www.smolyan.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.smolyan.mvr.bg","lipsva",true);
}

function mvrSofiq() {
  loadMVRpage("[София] ","МВР София","новини",38,"http://www.odmvr-sofia.mvr.bg/Prescentar/Novini/default.htm","http://www.odmvr-sofia.mvr.bg");
}

function mvrStaraZagora() {
  loadMVRpage("[С.Загора] ","МВР С.Загора","новини",39,"http://www.starazagora.mvr.bg/PressOffice/News/default.htm","http://www.starazagora.mvr.bg");
}

function mvrStaraZagoraIzdirvani() {
  loadMVRpage("[С.Загора] ","МВР С.Загора","изчезнали",40,"http://www.starazagora.mvr.bg/PressOffice/Wanted/default.htm","http://www.starazagora.mvr.bg","lipsva",true);
}

function mvrTargovishte() {
  loadMVRpage("[Търговище] ","МВР Търговище","новини",41,"http://targovishte.mvr.bg/Prescentar/Novini/default.htm","http://targovishte.mvr.bg");
}

function mvrHaskovo() {
  loadMVRpage("[Хасково] ","МВР Хасково","новини",42,"http://haskovo.mvr.bg/Prescentar/Novini/default.htm","http://haskovo.mvr.bg");
}

function mvrShumen() {
  loadMVRpage("[Шумен] ","МВР Шумен","новини",43,"http://www.shumen.mvr.bg/Prescentar/Novini/default.htm","http://www.shumen.mvr.bg");
}

function mvrShumenIzdirvani() {
  loadMVRpage("[Шумен] ","МВР Шумен","изчезнали",44,"http://www.shumen.mvr.bg/Prescentar/Izdirvani_lica/default.htm","http://www.shumen.mvr.bg","lipsva",true);
}

function mvrQmbol() {
  loadMVRpage("[Ямбол] ","МВР Ямбол","новини",45,"http://www.yambol.mvr.bg/Prescentar/Novini/default.htm","http://www.yambol.mvr.bg");
}

function mvrQmbolIzdirvani() {
  loadMVRpage("[Ямбол] ","МВР Ямбол","изчезнали",46,"http://www.yambol.mvr.bg/Izdirvani_lica/default.htm","http://www.yambol.mvr.bg","lipsva",true);
}



/* them idiots */

function mvrBurgas() {
  echo "> Проверявам за новини в Бургас\n";
  setSession(19,4);

  $html = loadURL("http://www.rdvr-burgas.org/Bul/Suobshtenie/Realno.htm",4);
  if (!$html) return;
  $html = mb_convert_encoding($html, 'UTF-8', 'cp1251');
  $xpath = mvr_xpath($html);
  $items = $xpath ? $xpath->query("//table[@id='AutoNumber1']//td[1]//p") : false;
  if (!$items || $items->length==0) {
    if (!$expectempty)
      reportError("Грешка при зареждане на отделно съобщение");
    return;
  }

  echo "Открити ".$items->length." параграфа\n";

  $skip=true;
  $query=array();
  foreach ($items as $item) {
    $fulltext = text_cleanSpaces($item->textContent);
    $item_1 = $xpath->query(".//img",$item);
    if ($skip || ($fulltext=="" && $item_1->length==0)) {
      if ("СЪОБЩЕНИЕ"==$fulltext)
        $skip=false;
    } else
    if (mb_substr($fulltext,-7)==date("Y")." г." && $item_1->length==0) {
      $date = substr($fulltext,6,4)."-".substr($fulltext,3,2)."-".substr($fulltext,0,2);
      if (strtotime($date)<time()-3600*24*5)
        break;
      $query[]=array("[Бургас] ","",$date,"http://www.rdvr-burgas.org/Bul/Suobshtenie/Realno.htm",null,null);
    } else
    if (count($query)>0) {
      if (mb_strlen($query[count($query)-1][0])<100) {
        $title=$fulltext;
        $title=text_fixCase($title);
        if (mb_strrpos($title,".")>120) {
          $stoppos=0;
          while (($stoppos = mb_strpos($title,".",$stoppos+1))<=120);
          $title = mb_substr($title,0,$stoppos);
        }
        $query[count($query)-1][0].=(mb_strlen($query[count($query)-1][0])!=0 ? " " : "" ).$title;
      }

      $description = $item->C14N();
      $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
      $description = mb_ereg_replace("\s?(title|name|style|class|id|alt|target|align|dir|lang)=[\"'].*?[\"']\s?"," ",$description);
      $description = mb_ereg_replace("<p>[  ]*</p>|<br>[  ]*</br>|<a>[  ]*</a>|<div>[  ]*</div>"," ",$description);
      $description = text_cleanSpaces($description);
      $description = mb_ereg_replace(" >",">",$description);
      $description = mb_ereg_replace("</?span>|&#xD;","",$description);
      $description = text_cleanSpaces($description);
      $description = mb_ereg_replace("> <","><",$description);
      $query[count($query)-1][1].=$description;

      if ($query[count($query)-1][4]==null)
        $query[count($query)-1][4]=md5($fulltext);

      foreach ($item_1 as $itemimg) {
        $imageurl = "http://www.rdvr-burgas.org/Bul/Suobshtenie/".$itemimg->getAttribute("src");
        $imageurl = loadItemImage($imageurl);
        if ($imageurl) {
          if ($query[count($query)-1][5]==null)
            $query[count($query)-1][5] = array("image" => array());
          $query[count($query)-1][5]["image"][] = array($imageurl);
        }
      }
    } 
  }

  echo "Възможни ".count($query)." нови новини\n";

  $itemids = saveItems($query);
  queueTweets($itemids,"mibulgaria");
}

function mvrPlovdiv() {
  echo "> Проверявам за новини в Пловдив\n";
  setSession(19,28);

  $html = loadURL("http://plovdiv.mvr.bg/news.php",28);
  if (!$html) return;
  $html = mb_convert_encoding($html, 'UTF-8', 'cp1251');
  $xpath = mvr_xpath($html);
  $items = $xpath ? $xpath->query("//td[@nowrap='nowrap']") : false;
  if (!$items || $items->length==0) {
    if (!$expectempty)
      reportError("Грешка при зареждане на отделно съобщение");
    return;
  }

  echo "Открити ".$items->length." новини\n";

  $query=array();
  foreach ($items as $item) {
    $date = $item->childNodes->item(0)->textContent;
    $date = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);

    $title = $item->childNodes->item(2)->textContent;
    $title = text_cleanSpaces($title);
    $title = text_fixCase($title);
    if (mb_strrpos($title,".")>120) {
      $stoppos=0;
      while (($stoppos = mb_strpos($title,".",$stoppos+1))<=120);
      $title = mb_substr($title,0,$stoppos);
    }
    $title = "[Пловдив] ".$title;
    if (!checkTitle($title))
      continue;

    $hash = md5($title);
    
    $description = $item->childNodes->item(2)->C14N();
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id|alt|target|align|dir|lang)=[\"'].*?[\"']\s?"," ",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<br>[  ]*</br>|<a>[  ]*</a>|<div>[  ]*</div>"," ",$description);
    $description = mb_ereg_replace(" >",">",$description);
    $description = text_cleanSpaces($description);

    $query[]=array($title,$description,$date,"http://plovdiv.mvr.bg/news.php",$hash);
  }

  echo "Възможни ".count($query)." нови новини\n";

  $itemids = saveItems($query);
  queueTweets($itemids,"mibulgaria");
}


/* crappy, yet standard */

function loadMVRpage($prefix,$logtitle,$logwhat,$num,$url,$urlbase,$retweet=false,$expectempty=false) {
  echo "> Проверявам за $logwhat в $logtitle\n";
  setSession(19,$num);

  $html = loadURL($url,$num);
  if (!$html) return;
  $xpath = mvr_xpath($html);
  $items = $xpath ? $xpath->query("//ul[@class='categoryList']/li") : false;
  if (!$items || $items->length==0) {
    if (!$expectempty)
      reportError("Грешка при зареждане на отделно съобщение");
    return;
  }

  echo "Открити ".$items->length." $logwhat\n";

  $query=array();
  foreach ($items as $item) {
    $item_1 = $xpath->query("h3/a",$item);
    $item_2 = $xpath->query("p[@class='dateOfLink']",$item);
    if ($item_1->length==0 || $item_2->length==0)
      continue;

    $url = $urlbase.text_cleanSpaces($item_1->item(0)->getAttribute("href"));
    $hash = md5($url);
    if (!checkHash($hash))
      continue;

    $date = $item_2->item(0)->textContent; 
    $date = text_bgMonth(text_cleanSpaces($date));
    $date = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    if (strtotime($date)<time()-3600*24*7)
      continue;

    $title = $item_1->item(0)->textContent;
    $title = text_cleanSpaces($title);
    $title = $prefix.$title;    
    if (!checkTitle($title))
      continue;

    $description = null;
    $media=array("image" => array());
  
    $html1 = loadURL($url);
    if ($html1) {
      $xpath1 = mvr_xpath($html1);
      $items1 = $xpath1->query("//table[@id='content']//p|//table[@id='content']//h3|//table[@id='content']//div[@id='images']");
      if ($items1->length>0) {
        $description="";
        foreach ($items1 as $item1)
          $description.=$item1->C14N();
        $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
        $description = mb_ereg_replace("\s?(title|name|style|class|id|alt|target|align|dir)=[\"'].*?[\"']\s?"," ",$description);
        $description = mb_ereg_replace("<p>[  ]*</p>|<br>[  ]*</br>|<a>[  ]*</a>|<div>[  ]*</div>"," ",$description);
        $description = mb_ereg_replace(" >",">",$description);
        $description = text_cleanSpaces($description);
        $description = html_entity_decode($description);
      }

      $items2 = $xpath1->query("//table[@id='content']//div[@id='images']//a[text()='Илюстрация']|//table[@id='content']//p/a[text()='Снимки']");
      foreach ($items2 as $item2) {
        $magepageurl = $urlbase.$item2->getAttribute('href');
        $html3 = loadURL($magepageurl);
        $items3 = mvr_xpathDoc($html3,"//div[@id='divIllustrationHeap']//img|//div[@id='divIllustration']//img");
        foreach ($items3 as $item3) {
          $imageurl = $urlbase.$item3->getAttribute('src');
          $imageurl = loadItemImage($imageurl);
          if ($imageurl)
            $media["image"][] = array($imageurl);
        }
      }
    }

    if (count($media["image"])==0)
      $media=null;
    $query[]=array($title,$description,$date,$url,$hash,$media);
  }

  echo "Възможни ".count($query)." нови $logwhat\n";

  $itemids = saveItems($query);
  queueTweets($itemids,$retweet?$retweet:"mibulgaria",$retweet?"mibulgaria":null);
}

/*
------------------------------------------------------------------------
*/

function mvr_xpathDoc($html,$q) {
  $xpath = mvr_xpath($html);  
  if ($xpath==null)
    return array();
  $items = $xpath->query($q);
  return is_null($items)?array():$items;
}

function mvr_xpath($html) {
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

?>
