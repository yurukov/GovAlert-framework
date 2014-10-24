<?php

/*

0: земетресения http://ndc.niggg.bas.bg/data.xml


*/

function basZemetreseniq() {
  global $link;
  echo "> Проверявам за земетресения в БАН\n";
  setSession(14,0);

  $html = loadURL("http://ndc.niggg.bas.bg/data.xml",0);
  if (!$html) return;
  $items = bas_xpathDoc($html,"//marker");

  $query=array();
	foreach ($items as $item) {
    $mag = doubleval($item->getAttribute("mag"));

    $date = trim($item->getAttribute("time"));
    $lat = $item->getAttribute("lat");
    $lng = $item->getAttribute("lon");
    $hash = md5(substr($date,0,-1));
    $inBG = $lat>41.32 && $lat<44.04 && $lng>22.55 && $lng<28.60;

    if ($mag<3 && !($mag>2 && $inBG))
      continue;

    $date = strtotime("$date UTC");
    if ($date<strtotime("-1 day"))
      continue;

    $datediff = time()-$date;
    if ($datediff<60)
      $datediff = "секунди";
    elseif ($datediff<100*60)
      $datediff = round($datediff/60)." мин.";
    elseif ($datediff<4*3600)
      $datediff = round($datediff/3600)." ч.";
    else
      continue;

    $date = date("Y-m-d H:i:s", $date);

    if (!checkHash($hash))
      continue;    

    $res=$link->query("SELECT grad, geo FROM s_bas") or reportDBErrorAndDie();
    $town = null;
    $direction = null;      
    if ($res->num_rows>0) {
      while ($row = $res->fetch_array()) {
        $row[1] = explode(",",$row[1]);
        $directionNew = bas_direction($row[1][0],$row[1][1],$lat,$lng);
        if ($direction==null || $directionNew[0]<$direction[0]) {
          $town = $row[0];
          $direction = $directionNew;
          if ($direction[0]<40)
            break;      
        }      
      }
    }
    
    if ($town==null) {
      $town="Пловдив";
      $direction = bas_direction(42.141948,24.7465238,$lat,$lng);
    }

    if ($direction[0]<15)
      $title = "около $town";
    else
      $title = "на ".$direction[0]." км ".$direction[1]." от $town";
    $title = mb_ereg_replace(" ЮИ "," югоизточно ",$title,"im");
    $title = mb_ereg_replace(" ЮЗ "," югозападно ",$title,"im");
    $title = mb_ereg_replace(" СИ "," североизточно ",$title,"im");
    $title = mb_ereg_replace(" СЗ "," северозападно ",$title,"im");
    $title = mb_ereg_replace(" Ю "," южно ",$title,"im");
    $title = mb_ereg_replace(" С "," северно ",$title,"im");
    $title = mb_ereg_replace(" И "," източно ",$title,"im");
    $title = mb_ereg_replace(" З "," западно ",$title,"im");
    $title = "Земетресение $mag $title преди $datediff";

    $description = text_cleanSpaces($item->getAttribute("location"));
    if ($description=="")
      $description = null;

    $media = array(
      "geo" => array("$lat,$lng",null)
    );

    if ($inBG || $mag>=4.5)
      $media["geoimage"] = array(loadGeoImage($lat,$lng,8),null);

    $query[]=array($title,$description,$date,'http://ndc.niggg.bas.bg',$hash, $media);
  }
  echo "Възможни ".count($query)." нови земетресения\n";

  $itemids = saveItems($query);

  if (count($itemids)<=3)
    queueTweets($itemids);
  else
    queueTextTweet("В последните минути имаше ".count($itemids)." земетресения","http://ndc.niggg.bas.bg");  
}

/*
-----------------------------------------------------------------
*/


function bas_direction($lat1, $lng1, $lat2, $lng2) {
	$pi80 = M_PI / 180;
	$lat1 *= $pi80;
	$lng1 *= $pi80;
	$lat2 *= $pi80;
	$lng2 *= $pi80;

	$r = 6372.797;
	$dlat = $lat2 - $lat1;
	$dlng = $lng2 - $lng1;
	$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	$km = floor($r * $c);

  $bearing=atan2(cos($lat1)*sin($lat2)-sin($lat1)*cos($lat2)*cos($dlng),sin($dlng)*cos($lat2))/M_PI;
  if ($bearing<0.125 && $bearing>-0.125)
    $bearing = "И";
  else if ($bearing>=0.125 && $bearing<0.375)
    $bearing = "СИ";
  else if ($bearing>=0.375 && $bearing<0.625)
    $bearing = "С";
  else if ($bearing>=0.625 && $bearing<0.875)
    $bearing = "СЗ";
  else if ($bearing<=-0.125 && $bearing>-0.375)
    $bearing = "ЮИ";
  else if ($bearing<=-0.375 && $bearing>-0.625)
    $bearing = "Ю";
  else if ($bearing<=-0.625 && $bearing>-0.875)
    $bearing = "ЮЗ";
  else
    $bearing = "З";

	return array($km,$bearing);
}

function bas_xpathDoc($html,$q) {
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

?>
