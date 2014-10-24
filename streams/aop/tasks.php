<?php

//ne e napraveno

/*
links:
0: документи и поръчки http://rop3-app1.aop.bg:7778/portal/page?_pageid=93,662251&_dad=portal&_schema=PORTAL
1: публична покана http://rop3-app1.aop.bg:7778/portal/page?_pageid=93,1488254&_dad=portal&_schema=PORTAL&url=687474703A2F2F7777772E616F702E62672F657365617263685F7070322E706870
*/


function aop_Saobshteniq() {
  global $link;

  echo "> Проверявам за нови документи в АОП\n";

  $html = aop_httpPost("http://rop3-app1.aop.bg:7778/portal/page?_pageid=93,662251&_dad=portal&_schema=PORTAL",
          "go_page=0&doc_description=&u_id=&key_word=&btn_pressed=%D0%A2%D1%8A%D1%80%D1%81%D0%B8+...");
  if (!$html)
    return;
  if (!checkPageChanged($html,12,0))
    return;
  $items = aop_xpathDoc($html,"//table[@id='resultaTable']//tr");
echo $items->length;
exit;

  $info = array();
  $query=array();
	foreach ($items as $item) {
    $hash = md5($item->textContent);
    $date = trim($item->childNodes->item(1)->textContent);
    $date = substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    $date = $link->escape_string($date);
    $item->removeAttribute("class");
    $item->removeChild($item->childNodes->item(1));
    $item->removeChild($item->childNodes->item(0));
    $description = $item->C14N();
    $description = mb_ereg_replace(" </","</",mb_ereg_replace("> ",">",$description));
    $description = mb_ereg_replace("\s?(title|name|style|class|id)=[\"'].*?[\"']\s?","",$description);
    $description = mb_ereg_replace("<p>[  ]*</p>|<a>[  ]*</a>|<div>[  ]*</div>","",$description);
    $description = cik_cleanText($description);
    $description = $link->escape_string($description);
    $title = $item->textContent;
    $title = cik_cleanTitle($title);
    $title = "Съобщение: ".cik_cleanText($title);
    $title = $link->escape_string($title);
    $query[]=array($title,$description,1,$date,'http://www.cik.bg/',$hash);
  }

  $itemids = saveItems($query);
  queueTweets($itemids);
}

/*
-----------------------------------------------------------------
*/

function aop_httpPost($url,$data_url) {
	$data_len = strlen($data_url);
	$html = file_get_contents ($url, false, stream_context_create (array ('http'=>array(
		'method'=>'POST',
		'header'=>"Content-Length: $data_len\r\n".
			    "Connection: keep-alive\r\n".
			    "Cache-Control: max-age=0\r\n".
			    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n".
			    "Origin: http://umispublic.minfin.bg\r\n".
			    "Content-Type: application/x-www-form-urlencoded\r\n".
			    "Referer: http://rop3-app1.aop.bg:7778/portal/\r\n".
			    "Accept-Language: en-US,en;q=0.8,bg;q=0.6,de;q=0.4",
		'timeout' => 5,
		'max_redirects' => 5,
		'content'=>$data_url
	))));
  return $html;
}

function aop_xpathDoc($html,$q) {
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
