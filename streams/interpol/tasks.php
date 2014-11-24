<?php
/*
Links
1-50: безследно изчезнали http://www.interpol.int/notice/search/missing/(offset)/0/(Nationality)/122/(current_age_maxi)/100/(search)/1
51-100:  издирвани http://www.interpol.int/notice/search/wanted/(offset)/0/(Nationality)/122/(current_age_maxi)/100/(search)/1
101: новини http://www.interpol.int/Member-countries/Europe/Bulgaria
*/

function interpolIzcheznali() {
  interpolLoad(array(
    "изчезнали", 1, 
    array(array("http://www.interpol.int/notice/search/missing/(offset)/%d/(Nationality)/122/(current_age_maxi)/100/(search)/1",0))
  ));
}

function interpolIzdirvani() {
  interpolLoad(array(
    "издирвани", 0, 
    array(
      array("http://www.interpol.int/notice/search/wanted/(offset)/%d/(Nationality)/122/(current_age_maxi)/100/(search)/1",50),
      array("http://www.interpol.int/notice/search/wanted/(offset)/%d/(RequestingCountry)/122/(current_age_maxi)/100/(search)/1",100)
    )
  ));
}

function interpolProcessIzcheznali() {
  interpolProcess(array("изчезнали",
    1,
    "%s е обявен%s за безследно изчезнал%2\$s /CC @Interpol_HQ",
    "Обявени са още %d българи за безследно изчезнали  /CC @Interpol_HQ",
    "http://www.interpol.int/notice/search/missing/(offset)/0/(Nationality)/122/(current_age_maxi)/100/(search)/1",
    "http://www.interpol.int/notice/search/missing",
    "lipsva", array("mibulgaria","GovAlertEU")));
}

function interpolProcessIzdirvani() {
  interpolProcess(array("издирвани",
    0,
    "%s е обявен%s за издирване /CC @Interpol_HQ",
    "Обявени са още %d души за издирване  /CC @Interpol_HQ",
    "http://www.interpol.int/notice/search/wanted/(offset)/%d/(Nationality)/122/(current_age_maxi)/100/(search)/1",
    "http://www.interpol.int/notice/search/wanted",
    "mibulgaria", "GovAlertEU"));
}

function interpolLoad($prop) {
  global $link;

  setSession(18,$prop[1]==1?1:2);

  echo "> Проверявам за ${prop[0]} в Интерпол\n";

  $codes = array();
  $data = array();
  $available=array();

  $res = $link->query("select code from s_interpol where removed is null and missing=${prop[1]}") or reportDBErrorAndDie();
  while ($row = $res->fetch_array())
    $available[]=$row[0];
  $res->free();

  foreach ($prop[2] as $propU) {

    $html = loadURL(sprintf($propU[0],0),$propU[1]+1);
    if (!$html) return;
    $xpath = interpol_xpath($html);
    if (!$xpath) {
      reportError("Грешка при зареждане на начална страница");
      return;
    } 
    $items = $xpath->query("//div[@class='bloc_pagination']");
    if (!$items || $items->length==0) {
      reportError("Грешка при откриване на бройка");
      return;
    }
    $profiles = $items->item(0)->textContent;
    $profiles = intval(str_replace("Search result : ","",$profiles));
    
    echo "Открити ".$profiles." профила. Преглеждам...\n";
   
    for ($skip=0;$skip<$profiles;$skip+=9) {
      if ($skip>0) {
        $html = loadURL(sprintf($propU[0],$skip),$propU[1]+$skip/9+1);
        if (!$html) return;
        $xpath = interpol_xpath($html);
        if (!$xpath) {
          reportError("Грешка при зареждане на страница ".($skip/9+1));
          return;
        } 
      }
    
      $items = $xpath->query("//div[@class='bloc_bordure']/div");
      if (!$items || $items->length==0) {
        reportError("Грешка при откриване нa профили на страница ".($skip/9+1));
        return;
      }
      foreach ($items as $item) {
        $code = $item->childNodes->item(5)->childNodes->item(1)->getAttribute('href');
        $code = substr($code,strrpos($code,'/')+1);
        $code = $link->escape_string($code);
        if (in_array($code,$codes))
          continue;
        $photo = $item->childNodes->item(1)->firstChild->getAttribute('src');
        if (substr($photo,-16)!='NotAvailable.gif') {
          $photo = str_replace('GetThumbnail','GetPicture',$photo);
          $photo = str_replace(array(' ','%20'),'',$photo);
          $photo = $link->escape_string($photo);
        }
        $name = $item->childNodes->item(3)->childNodes->item(3)->childNodes;
        $name = $name->item(2)->textContent.' '.$name->item(0)->textContent;
        $name = mb_convert_case(transliterate(mb_convert_case($name,MB_CASE_UPPER)),MB_CASE_TITLE);
        $name = $link->escape_string($name);
        $codes[]=$code;
        $data[$code] = array($name,$photo);
      }
    }
  }
  
  $remove = array_diff($available,$codes);
  $add = array_diff($codes,$available);

  echo "Открити са ".count($add)." нови съобщения и ".count($remove)." за премахване.\n";

  if (count($remove)>0) {
    $link->query("update s_interpol set removed=now() where code in ('".implode("','",$remove)."')") or reportDBErrorAndDie();
  }
  if (count($add)>0)
    foreach ($add as $code) {
      $link->query("insert into s_interpol (code,name,added,photo,missing) value ('$code','".$data[$code][0]."',now(),'".$data[$code][1]."',${prop[1]}) ON DUPLICATE KEY UPDATE removed=null") or reportDBErrorAndDie();
    }

}
 
function interpolProcess($prop) {
  global $link;

  setSession(18,$prop[1]==1?1:2);;

  $query=array();
  $codes=array();
  $res = $link->query("SELECT code,name,added,photo FROM s_interpol where processed=0 and missing=${prop[1]} and removed is null") or reportDBErrorAndDie();
  echo "> Има ".$res->num_rows." ${prop[0]} без да са обявени тук. Зареждам снимките.\n";
  if ($res->num_rows==0)
    return;

  while ($row = $res->fetch_assoc()) {
    $old = strtotime($row["added"])<strtotime("-2 days");
    $noimage = substr($row["photo"],-16)=='NotAvailable.gif';

    $media = null;
    if (!$noimage) {
      $url=$prop[5]."/".$row["code"];
      loadURL($url);
      $imgoptions = array('doNotReportError'=>1, 'addInterpol'=>($prop[1]==1?'yellow':'red'));
      $imageurl = loadItemImage("http://www.interpol.int".$row["photo"],null,$imgoptions);
      if ($imageurl==null) {
        $imageurl = "http://www.interpol.int".str_replace("ws/","ws/%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20",$row["photo"]);
        $imageurl = loadItemImage($imageurl,null,$imgoptions);
      }
      if ($imageurl!=null) {
        $imagetitle = $row["name"];
        $media = array("image"=>array(array($imageurl,$imagetitle)));
      }
    }
    if ($media==null && !$old && !$noimage)
      continue;
    
    $suffix=mb_substr($row["name"],-1)=='а'?'а':'';
    $title=sprintf($prop[2],$row["name"],$suffix);
    $hash=md5($row["code"]);
    $query[]=array($title,null,'now',$url,$hash,$media);
    $codes[]=$row["code"];
  }

  echo "Възможни ".count($query)." нови ${prop[0]}\n";
  $itemids = saveItems($query);
  if (count($itemids)>5)
    queueTextTweet(sprintf($prop[3],count($itemids)),$prop[3],$prop[6],$prop[7]);
  else
    queueTweets($itemids,$prop[6],$prop[7]);

  if (count($codes)>0) {
    echo "Маркирам ".count($codes)." ${prop[0]} като съобщени\n";
    $link->query("update s_interpol set processed=1 where code in ('".implode("','",$codes)."')") or reportDBErrorAndDie();
  }
}

/*
------------------------------------------------------------------------
*/

function interpol_xpath($html) {
  if (!$html) return null;
  $doc = new DOMDocument("1.0","UTF-8");
  $doc->preserveWhiteSpace=false;
  $doc->strictErrorChecking=false;
  $doc->encoding = 'UTF-8';
  $doc->loadHTML($html);
  return new DOMXpath($doc);
}

function transliterate($textlat = null) {
    $cyr = array('Я','Ц','Ц','Ж','Ч','Щ','Ш','Ю','ЙО','С','ИЙ','А','Б','В','Г','Д','Е','З',
'И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ъ','Я');
    $lat = array('YA','TS','TZ','ZH','CH','SHT','SH','YU','YO','SS','YI','A','B','V','G','D','E','Z',
'I','Y','K','L','M','N','O','P','R','S','T','U','F','H','A','J');
    return str_replace($lat,$cyr,$textlat);
}

?>
