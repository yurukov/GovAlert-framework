<?php

function queueTextTweet($text, $urls, $account='govalerteu',$retweet=false) {
  global $link,$session;
  if (!checkSession())
    return;
  if (!$text || mb_strlen($text)==0)
    return;

  if ($urls && !is_array($urls))
    $urls=array($urls);

  echo "Планирам tweet за srcid=".$session["sourceid"]." текст='$text' и адреси ".implode(", ",$urls)."\n";

  $position=1;
  foreach ($urls as $url) {
    $res = $link->query("select linkid from link where url='$url'") or reportDBErrorAndDie();
    if ($res->num_rows>0) {
      $row=$res->fetch_assoc();
      $linkid=intval($row['linkid']);
    } else {
      $link->query("insert LOW_PRIORITY into link (url) value ('$url')") or reportDBErrorAndDie();
      $linkid=$link->insert_id;
    }
    if (!$linkid)
      return;
    $urltext = "http://GovAlert.eu/-".linkCode($linkid);
    if (mb_strpos($text,"$".$position))
      $text = mb_ereg_replace("\\$".$position,$urltext,$text);
    else
      $text .= " ".$urltext;
    $position++;
  }

  if (!$retweet)
    $retweet="null";
  else if (is_string($retweet))
    $retweet = "'$retweet'";
  else if (is_array($retweet))
    $retweet = "'".implode(",",$retweet)."'";
  else
    $retweet="'govalerteu'";

  $link->query("insert LOW_PRIORITY ignore into tweet (account, queued, text, sourceid, priority, retweet) value ('$account',now(),'$text',".$session["sourceid"].",1,$retweet)") or reportDBErrorAndDie(); 
}

function queueTweets($itemids, $account='govalerteu',$retweet=false) {
  global $link;
  if (!$itemids || count($itemids)==0)
    return;
  echo "Планирам ".count($itemids)." tweet-а\n";

  if (!$retweet)
    $retweet="null";
  else if (is_string($retweet))
    $retweet = "'$retweet'";
  else if (is_array($retweet))
    $retweet = "'".implode(",",$retweet)."'";
  else
    $retweet="'govalerteu'";

  $query = array();
  foreach ($itemids as $id)
    $query[]="($id,'$account',now(), $retweet)";
  $link->query("insert LOW_PRIORITY ignore into tweet (itemid, account, queued, retweet) values ".implode(",",$query)) or reportDBErrorAndDie(); 
}

function replaceAccounts($title,$cutlen) {
  $map = array(
    "@KGeorgievaEU" => array("Кристалина Георгиева","Кристалина"),
    "@CIKBG" => array("Централната избирателна комисия","ЦИК"),
    "@BgPresidency" => array("Президентът на РБ","Президента на РБ","президентът на Република България","президента на Република България","президентът Плевнелиев","президента Плевнелиев","президентът Росен Плевнелиев","президента Росен Плевнелиев"),
    "@EP_Bulgaria" => array("Европейски Парламент","Европейския Парламент","Европейският Парламент"),
    "@TomislavDonchev" => array("Томислав Дончев", "министър Дончев"),
    "@BoykoBorissov" => array("Бойко Борисов","премиера Борисов","премиерът Борисов"),
    "@SvMalinov" => array("Светослав Малинов", "министър Малинов"),
    "@evapaunova" => array("Ева Паунова"),
    "@JunckerEU" => array("Юнкер"),
    "@IvailoKalfin" => array("Ивайло Калфин","министър Калфин","Калфин"),
    "@FandakovaY" => array("Йорданка Фандъкова","Фандъкова"),
    "@Stoli4naOb6tina" => array("Столична община"),
    "@UniversitySofia" => array("Софийски университет"),
    "@MoskovPetar" => array("Петър Москов", "министър Москов"),
    "@rmkanev" => array("Радан Кънев"),
    "@KunevaMeglena" => array("Меглена Кунева","министър Кунева"),
    "@LilyanaPavlova" => array("Лиляна Павлова","министър Павлова")
  );

  foreach ($map as $account=>$strings)
    $title=replaceAccount($title,$account,$cutlen, $strings);

  return $title;
}

function replaceAccount($title,$account,$cutlen,$texts) {
  $text=false;
  foreach ($texts as $textT) 
    if (($loc=mb_stripos($title,$textT))!==false &&
        ($loc===0 || !mb_ereg_match("[а-яА-Я]",mb_substr($title,$loc-1,1))) &&
        ($loc+mb_strlen($textT)==mb_strlen($title) || !mb_ereg_match("[а-яА-Я]",mb_substr($title,$loc+mb_strlen($textT),1)))) {
      $text=$textT;
      break;
    }
  if ($text===false || $loc+mb_strlen($account)>=$cutlen)
    return $title;
  $firstPart = mb_substr($title,0,$loc);
  if (trim($firstPart)=='')
    $firstPart=".";
  return $firstPart.$account.mb_substr($title,$loc+mb_strlen($text));  
}

function loadAccounts() {
  global $link;

  $twitterAuth=array();
  $res=$link->query("SELECT handle, token, secret FROM twitter_auth") or reportDBErrorAndDie();  
  while ($row=$res->fetch_assoc()) {
    $twitterAuth[strtolower($row['handle'])]=array($row['token'],$row['secret']);
  }
  $res->free();

  return $twitterAuth;
}

function deleteTweets($account,$tweets) {
  global $link;
  $twitterAuth = loadAccounts();

  require_once('/www/govalert/twitter/twitteroauth/twitteroauth.php');
  require_once('/www/govalert/twitter/config.php');

  $currentAccount=strtolower($account);
  $currentAuth=$twitterAuth[$currentAccount];
  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $currentAuth[0], $currentAuth[1]);
  $connection->host = "https://api.twitter.com/1.1/";
  $connection->useragent = 'Bulgarian public sector alerts';
  $connection->ssl_verifypeer = TRUE;
  $connection->content_type = 'application/x-www-form-urlencoded';

  foreach ($tweets as $tweet) {
    echo "> Изтривам tweet $tweet от $account...";
    $tres = $connection->post('statuses/destroy', array(
      'id' => $tweet,
      'trim_user' => 'true'
    ));

    if ($tres->errors) {
      echo "грешка: ".$tres->errors[0]->message."\n";
      $errortext = $link->escape_string(json_encode($tres));
      reportError("Грешка докато изтривах tweet $tweet от $account:\n$errortext");
    } else {
      echo "готово.\n";
    }
  }
}

function postTwitter() {
  global $link;

  $res=$link->query("select t.tweetid, t.itemid, t.text, t.sourceid, t.account, t.retweet, i.title, i.url, s.shortname, s.geo, count(m.type) media from tweet t left outer join item i on i.itemid=t.itemid left outer join source s on i.sourceid=s.sourceid or t.sourceid=s.sourceid left outer join item_media m on m.itemid=t.itemid where error is null group by t.tweetid order by t.account, t.priority desc, t.queued, t.itemid limit 5") or reportDBErrorAndDie();  

  if ($res->num_rows>0) {
    echo "Изпращам ".$res->num_rows." tweet/s\n";

    $twitterAuth = loadAccounts();

    require_once('/www/govalert/twitter/twitteroauth/twitteroauth.php');
    require_once('/www/govalert/twitter/config.php');

    $currentAccount=false;
    $connection=false;

    $first=true;
    while ($row=$res->fetch_assoc()) {
      if (!$first)
        sleep(20);
      $first=false;
    
      if ($connection == false || $currentAccount===false || $currentAccount!=strtolower($row['account'])) {
        $currentAccount=strtolower($row['account']);
        $currentAuth=$twitterAuth[$currentAccount];
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $currentAuth[0], $currentAuth[1]);
        $connection->host = "https://api.twitter.com/1.1/";
        $connection->useragent = 'Bulgarian public sector alerts';
        $connection->ssl_verifypeer = TRUE;
        $connection->content_type = 'application/x-www-form-urlencoded';
      }

      if ($row['retweet'] && !$row['itemid'] && !$row['text']) {
        $tres = $connection->post('statuses/retweet/'.$row['retweet']);
      } else {

        $uploadimages=array();     
        $geo = explode(",",$row['geo']);
        if (intval($row['media'])!=0 && $row['itemid']) {
          $connection->host = "https://upload.twitter.com/1.1/";

          $resmedia=$link->query("select type,value from item_media where itemid='".$row['itemid']."' limit 3") or reportDBErrorAndDie();  
          while ($rowmedia=$resmedia->fetch_assoc()) {
              if ($rowmedia['type']=="geo")
                $geo=explode(",",$rowmedia['value']);
              elseif($rowmedia['type']=="image" || $rowmedia['type']=="geoimage") {
                $mediares = $connection->upload('media/upload', array(
                  'media' => "@".$rowmedia['value']
                ));
                if (!$mediares->error && $mediares->media_id_string) {
                  $uploadimages[]=$mediares->media_id_string;
                }
              }
          }
          $resmedia->free();

          $connection->host = "https://api.twitter.com/1.1/";
        }
        if (count($uploadimages)==0)
          $uploadimages=false;

        $messagelen=140;
        $prefix="";
        $postfix="";
        if ($row['text']==null) {
          $postfix = " http://GovAlert.eu/".linkCode(intval($row['itemid']));
          if ($row['url']!=null && mb_strlen($message)<=134) {
            $urltype = getUrlFileType($row['url']);
            if ($urltype) {
              $postfix .= " $urltype";
              $messagelen-=6;
            }
          }
          $title = $row['title'];
        } else {
          $title = $row['text'];
        }
        if (mb_substr($title,0,8)!="@yurukov" && strtolower($row['account'])=="govalerteu")
          $prefix = "[${row['shortname']}] ";
        $messagelen -= ($row['text']==null ? 23 : 0) + ($uploadimages ? 23 : 0) + mb_strlen($prefix);

        $title = replaceAccounts($title,$messagelen);

        if (mb_strlen($title)>$messagelen)
          $title=mb_substr($title,0,$messagelen-3)."...";
        $message = $prefix.$title.$postfix;

        $params = array(
          'status' => $message,
          'lat' => $geo[0], 
          'long' => $geo[1],
          'place_id' => '1ef1183ed7056dc1',
          'trim_user' => 'true',
          'display_coordinates' => 'true'
        );
        if ($uploadimages) {
          $params["media_ids"]=implode(",",$uploadimages);
        }

        $tres = $connection->post('statuses/update', $params);

        if (!$tres->errors) {
          $tweetid=$link->escape_string($tres->id_str);

          if ($row['itemid']) {
            $link->query("update LOW_PRIORITY item set tweetid='$tweetid' where itemid='".$row['itemid']."' limit 1") or reportDBErrorAndDie(); 
          }

          if ($row['retweet']) {
            $accounts = split(",",$row['retweet']);
            $query = array();
            foreach ($accounts as $account) {
              $query[]="('$account',now(),'$tweetid')";
            }
            $link->query("insert LOW_PRIORITY ignore into tweet (account, queued, retweet) values ".implode(",",$query)) or reportDBErrorAndDie(); 
          }
        }
      }

      if ($tres->code==215) {
        echo "Грешка: временна грешка на ауторизацията.\n";
      } else {
        if ($tres->errors) {
          echo "Грешка: $message | ".$tres->errors[0]->message."\n";
          $errortext = $link->escape_string(json_encode($tres));
          $link->query("update tweet set error='$errortext' where tweetid=${row['tweetid']} limit 1") or reportDBErrorAndDie();    
          break;
        } else {
          $link->query("delete from tweet where tweetid=${row['tweetid']} limit 1") or reportDBErrorAndDie();    
        }
      }
    }

  }
  $res->free();
}


?>
