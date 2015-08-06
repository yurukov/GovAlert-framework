<?php

ini_set('user_agent','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36 (email=yurukov@gmail.com; reason=scraping data, please contact me if you find any issues)');  
ini_set('default_socket_timeout', 30); 
set_time_limit(20*60);
date_default_timezone_set('Europe/Sofia');
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
set_error_handler('errorHandler');

$link = mysqli_connect('localhost', 'username', 'password', "activist") or die("Не мога да се свържа с базата данни. ".$link->error);
$link->set_charset("utf8");

$session = array("sourceid"=>null,"category"=>null,"error"=>false);

/*
    Session handling
*/

function setSession($sourceid,$category) {
   global $session;
   $session["sourceid"]=$sourceid;
   $session["category"]=$category;
   $session["error"]=false;
}

function resetSession() {
   global $session;
   $session["sourceid"]=null;
   $session["category"]=null;
   $session["error"]=false;
}

function checkSession() {
  global $session;
  if ($session["sourceid"]==null) {
    reportError("Не е заредена сесията");
    return false;
  }
  return true;
}

function reportDBErrorAndDie() {
	global $link;
	$dscr="Грешка при запитване към базата данни.\n".$link->error;
	reportError($dscr);
	die($dscr);
}

function reportError($descr) {
  global $link,$session,$debug;
  if ($descr===null)
    return;
  $sourceid=$session["sourceid"]!=null ? $session["sourceid"] : 0;
  $category=$session["category"]!=null ? $session["category"] : 0;
  if (is_array($descr) || is_object($descr))
    $descr = json_encode($descr);
  $e = new Exception();
  $trace = str_replace("/home/yurukov1/public_html/govalert/","",$e->getTraceAsString());
  echo "Запазвам грешка [$sourceid,$category]: $descr\n$trace\n";
  if ($debug) 
    return;
  $descr = $link->escape_string("$descr\n$trace");
  $link->query("insert LOW_PRIORITY ignore into error (sourceid, category, descr) value ($sourceid,$category,'$descr')") or reportDBErrorAndDie();
  $session["error"]=true;
}

/*
    Loading data
*/

function loadURL($address,$linki=null) {
  global $link,$debug,$session;
  if (!checkSession())
    return false;

  echo "Зареждам $address... ";
  
  $address=str_replace(" ","%20",$address);
  $hashdata=false;
  $hashdatadirty=false;

  if (!$debug && $linki!==null) {
    $res = $link->query("select hash,lastchanged,etag,headpostpone,ignorehead from scrape where sourceid=".$session["sourceid"]." and url=$linki limit 1") or reportDBErrorAndDie();      
    if ($res->num_rows>0)
      $hashdata=$res->fetch_assoc();
    $res->free();
    if ($hashdata && !$hashdata['ignorehead']) {
      $hashdatadirty=array(0,0,0);

      if ($hashdata['lastchanged']!=null || $hashdata['etag']!=null || $hashdata['headpostpone']==null || strtotime($hashdata['headpostpone'])<time()) {
        $context  = stream_context_create(array('http' =>array('method'=>'HEAD')));
        $fd = fopen($address, 'rb', false, $context);
        $headdata = stream_get_meta_data($fd);
        fclose($fd);
        $foundlc=false;
        $foundet=false;
        foreach ($headdata as $header) {
          if ($hashdata['lastchanged']!=null && substr($header,0,strlen("Last-Modified: "))=="Last-Modified: ") {
            $foundlc=true;
            if (strtotime(substr($header,strlen("Last-Modified: ")))==strtotime($hashdata['lastchanged'])) {
              echo "страницата не е променена [Last-Modified]\n";
              return false;
            } else {
              $hashdata['lastchanged']="'".$link->escape_string(substr($header,strlen("Last-Modified: ")))."'";
              $hashdatadirty[0]=1;
            }
          }
          if ($hashdata['etag']!=null && substr($header,0,strlen("ETag: "))=="ETag: ") {
            $foundet=true;
            if (substr($header,strlen("ETag: "))==$hashdata['etag'] || substr($header,strlen("ETag: ")+2)==$hashdata['etag']) {
              echo "страницата не е променена [ETag]\n";
              return false;      
            } else {
              $hashdata['etag']=substr($header,strlen("ETag: "));
              if (substr($header,0,strlen("W/"))=="W/")
                $hashdata['etag']=substr($hashdata['etag'],2);
              $hashdata['etag']="'".$link->escape_string($hashdata['etag'])."'";
              $hashdatadirty[1]=1;
            }
          }
        }
        if (!$foundlc && $hashdata['lastchanged']!=null) {
          $hashdata['lastchanged']='null';
          $hashdatadirty[0]=1;
        }
        if (!$foundet && $hashdata['etag']!=null) {
          $hashdata['etag']='null';
          $hashdatadirty[1]=1;
        }
        if (!$foundlc && !$foundet) {
          $hashdata['headpostpone']='DATE_ADD(NOW(),INTERVAL 1 MONTH)';
          $hashdatadirty[2]=1;
        } else if ($hashdata['headpostpone']!=null) {
          $hashdata['headpostpone']='null';
          $hashdatadirty[2]=1;
        }
      }
    }
  }

  $loadstart=microtime(true);
  $html = file_get_contents($address);
  setPageLoad($linki!==null?$linki:$address,$loadstart);
	if ($html===false || $html===null) {
		sleep(2);
    echo "втори опит... ";
    $loadstart=microtime(true);
		$html = file_get_contents($address);
    setPageLoad($linki!==null?$linki:$address,$loadstart);
 	}

  if ($html===false || $html===null) {
    reportError("Грешка при зареждане на сайта");
    echo "грешка при зареждането\n";
    return false;
  }

  if (!$debug && $linki!==null) {
    if ($hashdata===false) {
      $link->query("replace scrape (sourceid,url,hash,loadts) value (".$session["sourceid"].",$linki,'$hash',now())") or reportDBErrorAndDie();  
    } else {
      $hash = md5($html);
      if ($hashdata['hash']!=null && $hashdata['hash']==$hash) {
        echo "страницата не е променена [hash]\n";
        if (!$hashdata['ignorehead']) {
          if ($hashdata['headpostpone']===null)
            $link->query("update scrape set ignorehead=1 where sourceid=".$session["sourceid"]." and url=$linki limit 1") or reportDBErrorAndDie(); 
          else if ($hashdatadirty[0] || $hashdatadirty[1] || $hashdatadirty[2]) {
            $setters = array();
            if ($hashdatadirty[0])
              $setters[]='lastchanged='.$hashdata['lastchanged'];
            if ($hashdatadirty[1])
              $setters[]='etag='.$hashdata['etag'];
            if ($hashdatadirty[2])
              $setters[]='headpostpone='.$hashdata['headpostpone'];
            $link->query("update scrape set ".implode(", ",$setters)." where sourceid=".$session["sourceid"]." and url=$linki limit 1") or reportDBErrorAndDie(); 
          }
        }
        return false;
      }

      $link->query("update scrape set ".
        ($hashdatadirty[0]?'lastchanged='.$hashdata['lastchanged'].', ':'').
        ($hashdatadirty[1]?'etag='.$hashdata['etag'].', ':'').
        ($hashdatadirty[2]?'headpostpone='.$hashdata['headpostpone'].', ':'').
        "hash='$hash', loadts=now() where sourceid=".$session["sourceid"]." and url=$linki limit 1") or reportDBErrorAndDie(); 
    }
  }

  echo "готово\n";
  return $html;
}

function getUrlFileType($url) {
  if (strpos($url,".pdf")!==false)
    return "[PDF]";
  if (strpos($url,".doc")!==false)
    return "[DOC]";
  if (strpos($url,".xls")!==false || strpos($url,".xlsx")!==false)
    return "[XLS]";

  $context  = stream_context_create(array('http' =>array('method'=>'HEAD')));
  $fd = fopen($url, 'rb', false, $context);
  $data = stream_get_meta_data($fd);
  fclose($fd);
  if (!$data['wrapper_data'])
    return false;

  foreach ($data['wrapper_data'] as $wr)
    if (strpos($wr,"Content-Disposition: attachment")!==false) {
      if (strpos($wr,".pdf")!==false)
        return "[PDF]";
      if (strpos($wr,".doc")!==false)
        return "[DOC]";
      if (strpos($wr,".xls")!==false || strpos($url,".xlsx")!==false)
        return "[XLS]";
    }
  return false;
}

function setPageLoad($url,$loadstart) {
  global $link,$session,$debug;
  if ($debug)
    return;
  if (!checkSession())
    return;
  $loadtime = round((microtime(true)-$loadstart)*1000);
  $res = $link->query("insert LOW_PRIORITY ignore into scrape_load (sourceid,category,url,loadtime) value ".
    "(".$session["sourceid"].",".$session["category"].",'$url',$loadtime)") or reportDBErrorAndDie();      
}

function loadGeoImage($lat,$lng,$zoom) {
  $filename = "/www/govalert/media/maps/static/".str_replace(".","_",$lat."_".$lng)."_$zoom.png";
  if (!file_exists($filename)) {
    $url = "http://api.tiles.mapbox.com/v3/yurukov.i6nmgf1c/pin-l-star+ff0000($lng,$lat,$zoom)/$lng,$lat,$zoom/640x480.png?access_token=[USERTOKEN]";
    $loadstart=microtime(true);
    exec("wget --header='Connection: keep-alive' --header='Cache-Control: max-age=0' --header='Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' --header='User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36' --header='Accept-Encoding: gzip,deflate,sdch' --header='Accept-Language: en-US,en;q=0.8,bg;q=0.6,de;q=0.4' -q -O '$filename' '$url'");
    setPageLoad($url,$loadstart);
    usleep(500000);
  }

  if (!file_exists($filename) || filesize($filename)==0) {
    reportError("Грешка при зареждане на геоснимка $lat,$lng,$zoom.");
    return null;
  }

  return $filename;
}

function loadGeoJSONImage($geoJson) {
  $filename = "/www/govalert/media/maps/static/".md5($geoJson).".png";

  if (!file_exists($filename)) {
    $url = "https://api.tiles.mapbox.com/v4/yurukov.i6nmgf1c/geojson(".urlencode($geoJson).")/auto/800x600.png?access_token=[USERTOKEN]";
echo $url."\n";
    $loadstart=microtime(true);
    exec("wget --header='Connection: keep-alive' --header='Cache-Control: max-age=0' --header='Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' --header='User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36' --header='Accept-Encoding: gzip,deflate,sdch' --header='Accept-Language: en-US,en;q=0.8,bg;q=0.6,de;q=0.4' -q -O '$filename' '$url'");
    setPageLoad($url,$loadstart);
    usleep(500000);
  }

  if (!file_exists($filename) || filesize($filename)==0) {
    reportError("Грешка при зареждане на геоснимка с geoJson: $geoJson");
    return null;
  }

  return $filename;
}

function loadItemImage($url,$type=null,$options) {
  if ($type===null) {
    $type=".jpg";
  } else if (substr($type,0,1)!=".")
    $type=".$type";

  if (strtolower($type)!=".jpg" && strtolower($type)!=".jpeg" && strtolower($type)!=".gif" && strtolower($type)!=".png" && strtolower($type)!=".bmp") 
    return null;

  $filename = "/www/govalert/media/item_images/".md5($url).($type==".bmp"?".jpg":$type);
  if (!file_exists($filename)) {
    $loadstart=microtime(true);
    exec("wget --header='Connection: keep-alive' --header='Cache-Control: max-age=0' --header='Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' --header='User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36' --header='Accept-Encoding: gzip,deflate,sdch' --header='Accept-Language: en-US,en;q=0.8,bg;q=0.6,de;q=0.4' -q -O '$filename' '$url'");
    setPageLoad($url,$loadstart);
    if (filesize($filename)>=1.5*1024*1024)
      resizeItemImage($filename,$type);
    else
      fitinItemImage($filename,$type,$options);

    usleep(500000);
  } else {
    if (array_key_exists("ignoreCached",$options) && $options["ignoreCached"])
      return null;
  }

  if (!file_exists($filename) || filesize($filename)==0) {
    if (file_exists($filename))
      unlink($filename);
    if (!array_key_exists("doNotReportError",$options))
      reportError("Грешка при зареждане на снимка: $url");
    return null;
  }

  return $filename;
}

function fitinItemImage($filename, $type, $options) {
  ini_set('memory_limit', '150M' );

  $source = loadResizeItemImage($filename);
  if (!$source)
    return;

  $width=imagesx($source);
  $height=imagesy($source);

  $factor = $height<253?253/$height:1;
  $newheight = floor($height*$factor);
  $newwidth = floor($width*$factor);
  $newwidthR = $newwidth>506?$newwidth:506;
  $offset = ($newwidthR-$newwidth)/2;

  $thumb = imagecreatetruecolor($newwidthR, $newheight);

  $white = imagecolorallocate($thumb, 255, 255, 255);
  imagefill($thumb, 1, 1, $white);
  imagecopyresampled($thumb, $source, $offset, 0, 0, 0, $newwidth, $newheight, $width, $height);
  imagedestroy($source);

  if (array_key_exists("addInterpol",$options)) {
    $interpol = imagecreatefrompng("/www/govalert/media/res/notice-".$options["addInterpol"].".png");
    imagecopyresampled($thumb, $interpol, $newwidthR-57, $newheight-84, 0, 0, 57, 84, 57, 84);
    imagedestroy($interpol);
  }

  saveResizeItemImage($thumb,$filename,$type);
}

function resizeItemImage($filename, $type) {
  ini_set('memory_limit', '150M' );

  $source = loadResizeItemImage($filename);
  if (!$source)
    return;

  $width=imagesx($source);
  $height=imagesy($source);

  $factor=0.7;
  if ($width>1600)
    $factor=1600/$width;
  if ($height*$factor>1600)
    $factor=1600/$height;

  $newwidth = floor($width * $factor);
  $newheight = floor($height * $factor);

  $thumb = imagecreatetruecolor($newwidth, $newheight);

  imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
  imagedestroy($source);
  saveResizeItemImage($thumb,$filename,$type);
}

function loadResizeItemImage($filename) {
  $type = exif_imagetype($filename);
  if ($type==2)
    return imagecreatefromjpeg($filename);
  else if ($type==1)
    return imagecreatefromgif($filename);
  else if ($type==3)
    return imagecreatefrompng($filename);
  else if ($type==6)
    return imagecreatefrombmp($filename);
  return null;
}

function saveResizeItemImage($thumb,$filename,$type) {
  if (strtolower($type)==".jpg" || strtolower($type)==".jpeg" || strtolower($type)==".bmp")
    imagejpeg($thumb,$filename);
  else if (strtolower($type)==".gif")
    imagegif($thumb,$filename);
  else if (strtolower($type)==".png")
    imagepng($thumb,$filename);

  imagedestroy($thumb);
}

function imagecreatefrombmp($p_sFile)
{
  $file = fopen($p_sFile,"rb");
  $read = fread($file,10);
  while(!feof($file)&&($read<>""))
  $read    .=    fread($file,1024);
  $temp = unpack("H*",$read);
  $hex = $temp[1];
  $header = substr($hex,0,108);
  if (substr($header,0,4)=="424d") {
    $header_parts = str_split($header,2);
    $width = hexdec($header_parts[19].$header_parts[18]);
    $height = hexdec($header_parts[23].$header_parts[22]);
    unset($header_parts);
  }
  $x = 0;
  $y = 1;
  $image = imagecreatetruecolor($width,$height);
  $body = substr($hex,108);
  $body_size = (strlen($body)/2);
  $header_size = ($width*$height);
  $usePadding = ($body_size>($header_size*3)+4);
  for ($i=0;$i<$body_size;$i+=3) {
    if ($x>=$width) {
      if ($usePadding)
        $i += $width%4;
      $x = 0;
      $y++;
      if ($y>$height)
        break;
    }
    $i_pos = $i*2;
    $r = hexdec($body[$i_pos+4].$body[$i_pos+5]);
    $g = hexdec($body[$i_pos+2].$body[$i_pos+3]);
    $b = hexdec($body[$i_pos].$body[$i_pos+1]);
    $color = imagecolorallocate($image,$r,$g,$b);
    imagesetpixel($image,$x,$height-$y,$color);
    $x++;
  }
  unset($body);
  return $image;
}

/*
    Saving data
*/

function saveItem($title,$description,$pubts,$url,$hash,$media=null) {
  return saveItems(array(array($title,$description,$pubts,$url,$hash,$media)));
}

function saveItems($items) {
  global $link,$session;
  if (!checkSession())
    return;
  if (!$items || count($items)==0)
    return;

  echo "Запазвам ".count($items)."... ";
  $hashes=array();
  foreach ($items as $item)
    $hashes[]="'".$item[4]."'";
  $res=$link->query("select hash from item where hash in (".implode(",",$hashes).") limit ".count($hashes)) or reportDBErrorAndDie();
  $hashes=array();
  while ($row = $res->fetch_array())
    $hashes[]=$row[0];
  $res->free();

  $query = array();
  foreach ($items as $item) {
    if (in_array($item[4],$hashes))
      continue;
    $item[0]=$item[0]!==null?"'".$link->escape_string($item[0])."'":"null";
    $item[1]=$item[1]!==null?"'".$link->escape_string($item[1])."'":"null";
    $item[2]=$item[2]!==null? ($item[2]=='now'?'now()':"'".$link->escape_string($item[2])."'") :"null";
    $item[3]=$item[3]!==null?"'".$link->escape_string($item[3])."'":"null";
    $query[]=array("(${item[0]},${item[1]},${session['sourceid']},${session['category']},${item[2]},now(),${item[3]},'${item[4]}')",$item[5]);
  }
  echo "от тях ".count($query)." са нови... ";

  $changed = array();
  if (count($query)>0) {
    $query = array_reverse($query);
    foreach ($query as $value) {
      $link->query("insert LOW_PRIORITY ignore into item (title,description,sourceid,category,pubts,readts,url,hash) value ".$value[0]) or reportDBErrorAndDie(); 
      if ($link->affected_rows>0) {
        $changed[]=$link->insert_id;
        if ($value[1] && is_array($value[1])) { 
          $mediaquery = array();
          foreach ($value[1] as $mediakey => $mediavalue) {
            if (!$mediavalue[0] || $mediavalue[0]==null)
              continue;
            if (is_array($mediavalue[0])) {
              foreach ($mediavalue as $mediavalueitem) {
                if (!$mediavalueitem[0] || $mediavalueitem[0]==null)
                  continue;
                $mediavalueitem[0] = "'".$link->escape_string($mediavalueitem[0])."'";
                $mediavalueitem[1] = !$mediavalueitem[1] || $mediavalueitem[1]==null ? "null" : "'".$link->escape_string($mediavalueitem[1])."'";
                $mediaquery[]="(".$link->insert_id.",'$mediakey',".$mediavalueitem[0].",".$mediavalueitem[1].")";
              }
            } else {
              $mediavalue[0] = "'".$link->escape_string($mediavalue[0])."'";
              $mediavalue[1] = !$mediavalue[1] || $mediavalue[1]==null ? "null" : "'".$link->escape_string($mediavalue[1])."'";
              $mediaquery[]="(".$link->insert_id.",'$mediakey',".$mediavalue[0].",".$mediavalue[1].")";
            }
          }
          $link->query("insert LOW_PRIORITY ignore into item_media (itemid,type,value,description) values ".implode(",",$mediaquery)) or reportDBErrorAndDie();
        }
      }
    }
  }
  echo "записани ".count($changed)."\n";
  return $changed;
}

function checkHash($hash) {
  global $link;
  $res=$link->query("select hash from item where hash='$hash' limit 1") or reportDBErrorAndDie();
  return $res->num_rows==0;
}

function checkTitle($title) {
  global $link,$session;
  if (!checkSession())
    return true;
  $res=$link->query("select hash from item where title='$title' and sourceid=${session['sourceid']} limit 1") or reportDBErrorAndDie();
  return $res->num_rows==0;
}

/*
    Running tasks
*/

function runTasks($force) {
  global $link;
  $res = $link->query("select tasktd from task_stat where tasks is null and tasktd>date_sub(now(), interval 20 minute) limit 1") or reportDBErrorAndDie();
  if ($res->num_rows>0) {
    echo "Върви друг процес.\n";
    return;
  }
  $res->free();
  $link->query("insert LOW_PRIORITY ignore into task_stat value (now(),null,null)") or reportDBErrorAndDie();

  $loadstart=microtime(true);
  $res = $link->query("select lib, task, delay from task where active=1".($force?"":" and (lastrun is null or date_add(lastrun, interval delay hour)<=date_add(now(), interval 5 minute))")." order by lib asc, priority desc limit 30") or reportDBErrorAndDie();      
  $tasks=$res->num_rows;
  echo "Пускам ".$res->num_rows." задачи\n";
  while ($row=$res->fetch_assoc()) 
    if (file_exists($row["lib"]."/tasks.php")) {
      require_once($row["lib"]."/tasks.php");
      resetSession();
      call_user_func($row["task"]);
      if (!$force && $row["delay"]!=0) {
        if (!$session["error"] || $row["delay"]<=4) {
          if ($row["delay"]>24)
            $link->query("update task set lastrun=date_sub(now(),interval ".rand(10,180)." minute) where lib='${row["lib"]}' and task='${row["task"]}' limit 1") or reportDBErrorAndDie();  
          else
            $link->query("update task set lastrun=now() where lib='${row["lib"]}' and task='${row["task"]}' limit 1") or reportDBErrorAndDie();  
        } else
          echo "Засякох грешка. Не маркирам като пусната задача. Ще опитам пак след малко.\n";
      }
    }
  $res->free();
  $tool=ceil((microtime(true)-$loadstart)*1000);
  $link->query("update task_stat set tasks=$tasks, took=$tool where tasks is null limit 1") or reportDBErrorAndDie();
}


/*
    shortcode utils
*/

function linkCode($id) {
  $chars ="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $code = "";
  while ($id>0) {
    $rest = $id%strlen($chars);
    $code = substr($chars,$rest,1).$code;
    $id = floor($id/strlen($chars));
  }
  return $code;
}

function codeToId($code) {
  $chars ="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $id = "";
  for ($i=0;$i<strlen($code);$i++) {
    $pos = strpos($chars,substr($code,strlen($code)-1-$i,1));
    if ($pos==-1)
      return false;
    $id += $pos*pow(strlen($chars),$i);
  }
  return $id;
}

function codeToUrl($code) {
  global $link;
  if (!$code)
    return false;

  $id=false;
  if (is_nan(intval($code)))
    return false;
  if (substr($code,0,1)=="-") {
    $id=codeToId(substr($code,1));
    $query1="select url from link where linkid=$id limit 1";  
    $codetype='link';
  } else {
    $id=codeToId($code);
    $query1="select url from item where itemid=$id limit 1";  
    $codetype='item';
  }

  $res=$link->query($query1);    
  if (!$res)
    return false;
  $row = $res->fetch_array();
  if (!$row)
    return false;
  if ($_SERVER['REMOTE_ADDR']) {
    $ip = explode('.',$_SERVER['REMOTE_ADDR']);
    $ip = sprintf("%02X%02X%02X%02X",intval($ip[0]),intval($ip[1]),intval($ip[2]),intval($ip[3]));
    $link->query("insert LOW_PRIORITY ignore into visit (id,type,ip) value ($id,'$codetype','$ip')");
  }
  return $row[0];
}

/*
-------Text tools----------------------------------------------------------
*/

function text_cleanSpaces($text) {
  $text = str_replace(" "," ",$text);
	$text = mb_ereg_replace("[\n\r\t ]+"," ",$text);
  $text = mb_ereg_replace("(^\s+)|(\s+$)", "", $text);
  return $text;
}

function text_bgMonth($text) {
  $text = mb_ereg_replace("Януари|януари|ЯНУАРИ","01",$text,"imsr");
  $text = mb_ereg_replace("Февруари|февруари|ФЕВРУАРИ","02",$text,"imsr");
  $text = mb_ereg_replace("Март|март|МАРТ","03",$text,"imsr");
  $text = mb_ereg_replace("Април|април|АПРИЛ","04",$text,"imsr");
  $text = mb_ereg_replace("Май|май|МАЙ","05",$text,"imsr");
  $text = mb_ereg_replace("Юни|юни|ЮНИ","06",$text,"imsr");
  $text = mb_ereg_replace("Юли|юли|ЮЛИ","07",$text,"imsr");
  $text = mb_ereg_replace("Август|август|АВГУСТ","08",$text,"imsr");
  $text = mb_ereg_replace("Септември|септември|СЕПТЕМВРИ","09",$text,"imsr");
  $text = mb_ereg_replace("Октомври|октомври|ОКТОМВРИ","10",$text,"imsr");
  $text = mb_ereg_replace("Ноември|ноември|НОЕМВРИ","11",$text,"imsr");
  $text = mb_ereg_replace("Декември|декември|ДЕКЕМВРИ","12",$text,"imsr");
  return $text;
}


/*
-------Utils----------------------------------------------------------
*/

function updateHash($oldhash,$newhash) {
  global $link;
  $link->query("update item set hash='$newhash' where hash='$oldhash' limit 1") or reportDBErrorAndDie(); 
  echo "update hash $oldhash->$newhash ".($link->affected_rows>0?"ok":"fail")."\n";
}

function updateHashUrl($url,$newhash) {
  global $link;
  $link->query("update item set hash='$newhash' where url='$url'") or reportDBErrorAndDie(); 
  echo "update hash $url->$newhash ".($link->affected_rows>0?"ok ".$link->affected_rows:"fail")."\n";
}

function updateHashTitle($title,$newhash) {
  global $link;
  $link->query("update item set hash='$newhash' where title='$title'") or reportDBErrorAndDie(); 
  echo "update hash $title->$newhash ".($link->affected_rows>0?"ok ".$link->affected_rows:"fail")."\n";
}

function errorHandler($errno, $errstr, $errfile, $errline) {
    switch ($errno) {
    case E_USER_ERROR:
        reportError("ERROR [$errno] $errstr");
        exit(1);
        break;

    case E_USER_WARNING:
        reportError("WARNING [$errno] $errstr");
        break;

    case E_USER_NOTICE:
        reportError("NOTICE [$errno] $errstr");
        break;

    default:
        if (strpos($errstr,"htmlParseEntityRef")==-1)
          reportError("UNKNOWN [$errno] $errstr");
        break;
    }
    return true;
}

?>
