<?php

/*
links:
0: бюлетин http://eea.government.bg/airq/bulletin.jsp
*/

function eea_Zamarsiavane() {
  global $link;
  setSession(24,0);

  echo "> Проверявам за замърсяване в ИАОС\n";
  
  $html = loadURL("http://eea.government.bg/airq/bulletin.jsp",0);
  if (!$html) return;
  $items = eea_xpathDoc($html,"//table[@class='list autowidth']//tr[td]");

  $pollution=array(array(),array(),array(),array(),array());
  $firstCity=false;
  $cityCount= $items->length;
	foreach ($items as $item) {
    $city = eea_cleanText($link->escape_string($item->firstChild->textContent));
    if ($city=="Не са регистрирани превишения на нормите.")
      return;
    $res=$link->query("SELECT lon,lat FROM s_eea_airstations where bg_name='$city'") or reportDBErrorAndDie();
    if ($res->num_rows==0) {
      echo "Липсващи координати за град: $city\n";
      continue;
    }
    $row = $res->fetch_array();
    $coord = $row[0].",".$row[1];
    if ($firstCity==false)
      $firstCity=$coord;
    if (eea_cleanText($item->childNodes->item(1)->textContent)!="" || eea_cleanText($item->childNodes->item(2)->textContent)!="")
      $pollution[0][]='{"type":"Feature","properties":{"marker-symbol":"danger", "marker-size":"large", "marker-color":"#FFFF00"},"geometry":{"type":"Point","coordinates":['.$coord.']}}'; 
    if (eea_cleanText($item->childNodes->item(3)->textContent)!="")
      $pollution[1][]='{"type":"Feature","properties":{"marker-symbol":"danger", "marker-size":"large", "marker-color":"#804000"},"geometry":{"type":"Point","coordinates":['.$coord.']}}';
    if (eea_cleanText($item->childNodes->item(4)->textContent)!="")
      $pollution[2][]='{"type":"Feature","properties":{"marker-symbol":"danger", "marker-size":"large", "marker-color":"#b59170"},"geometry":{"type":"Point","coordinates":['.$coord.']}}';
    if (eea_cleanText($item->childNodes->item(5)->textContent)!="")
      $pollution[3][]='{"type":"Feature","properties":{"marker-symbol":"danger", "marker-size":"large", "marker-color":"#71b5af"},"geometry":{"type":"Point","coordinates":['.$coord.']}}';
    if (eea_cleanText($item->childNodes->item(6)->textContent)!="")
      $pollution[4][]='{"type":"Feature","properties":{"marker-symbol":"danger", "marker-size":"large", "marker-color":"#5262b5"},"geometry":{"type":"Point","coordinates":['.$coord.']}}';
  }

  $media = array(
    "geo" => array($firstCity),
    "geoimage" => array()
  );
  $pollutantsCount=0;
  for ($i=0;$i<=4;$i++)
    if (count($pollution[$i])>0) {
      $pollutantsCount++;
      $geojson = '{"type":"FeatureCollection","features":['.implode(',',$pollution[$i]).']}';
      $geoimage = loadGeoJSONImage($geojson);
      $media["geoimage"][] = array($geoimage);
    }

  $title = "Вчера ";
  if ($cityCount>1)
    $title.="един град е бил ";
  else
    $title.="$cityCount градa са били ";
  if ($pollutantsCount>1)
    $title.="със замърсен въздух";
  else if (count($pollution[0])>0)
    $title.="с превишени прагови стойности на серен диоксид";
  else if (count($pollution[1])>0)
    $title.="с превишени прагови стойности на азотен диоксид";
  else if (count($pollution[2])>0)
    $title.="с превишени прагови стойности на прахово замърсяване";
  else if (count($pollution[3])>0)
    $title.="с превишени прагови стойности на въглероден оксид";
  else if (count($pollution[4])>0)
    $title.="с превишени прагови стойности на озон";
  
  $url = "http://eea.government.bg/airq/bulletin.jsp?d=".date("d.m.Y");
  $date = date("d.m.Y",strtotime('-1 days'));
  $hash = md5($url);

  $query[]=array($title,null,$date,$url,$hash,$media);


print_r($query);
return;

  echo "Засечено е замърсяване на $cityCount град(а)\n";

  $query[]=array($title,null,'now',$url,$hash);

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function eea_xpathDoc($html,$q) {
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


function eea_cleanText($text) {
	$text = html_entity_decode($text);
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
	return $text;
}

?>
