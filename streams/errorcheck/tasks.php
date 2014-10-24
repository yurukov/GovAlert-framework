<?php

function errorcheck() {
  global $link;

  echo "> Проверявам за липса на новини и възможни грешки\n";
  
  $res = $link->query("select * from (select s.sourceid sourceid, s.shortname shortname, s.url url, max(i.readts) lastread, count(i.itemid) items from source s left outer join item i on s.sourceid=i.sourceid group by s.name order by max(readts) asc) a where a.lastread<subdate(now(), interval 2 week) limit 1") or reportDBErrorAndDie();  
  if ($res->num_rows==0) {
    echo "Няма липса на грешки\n";
    return;    
  }

  $row=$res->fetch_assoc();

  setSession($row["sourceid"],0);

  echo "Предупреждение за ".$row["shortname"]."\n";

  $title = "Няма новини от ".$row["shortname"]." от поне две седмици";
  $hash = md5($title.time());

  saveItem($title,null,"now",$row["url"],$hash);

  switch (rand(1,4)) {
    case 1: 
      $tweet = "@yurukov ".$row["shortname"]." не са пускали нищо наскоро. Може би има проблем:"; break;
    case 1: 
      $tweet = "@yurukov провери дали ".$row["shortname"]." не са си променили сайта, че не намирам нищо ново:"; break;
    case 1: 
      $tweet = "@yurukov от доста време няма новини от ".$row["shortname"].". Провери логовете ми за грешки."; break;
    default: 
      $tweet = "@yurukov шефе, няма новини от ".$row["shortname"]." от поне две седмици. Виж дали има проблем със сайта им:"; break;
  }
  queueTextTweet($tweet ,$row["url"]);
}

?>
