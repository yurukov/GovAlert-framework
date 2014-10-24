<?php

if (!isset($argv))
  exit;


echo "----Deploy----\n";

$force = isset($argv[1]) && $argv[1]=="force";

$backupf = "prod/backup-".date("Ymd-His");
echo "backup $backupf\n";
mkdir($backupf, 0775, true);
exec("cp -r prod/streams $backupf; cp -r prod/mail $backupf");

if (isset($argv[1]) && !$force && file_exists("prod/$argv[1]")) {
  echo "restoring prod/".$argv[1]."\n";
  exec("cp -r prod/".$argv[1]."/* prod");
} else if (deploy("streams")+deploy("mail")==0)
   exec("rm -rf $backupf");

function deploy($path) {
  global $force;
  $change = 0;
  if (is_dir($path) && $path!="./pos") {
    if ($handle = opendir($path)) {
    	while (false !== ($file = readdir($handle)))
    		if ($file != "." && $file != "..") 
    			$change+=deploy($path."/".$file);
	  }    
	  closedir($handle);
  } else if (substr($path,-4)==".php" || substr($path,-9)==".htaccess") {
    if (!$force && file_exists("prod/$path") && filectime($path)<filectime("prod/$path"))
      echo "skip\t$path\n";
    else {
      if (!file_exists(dirname("prod/$path")))
        mkdir(dirname("prod/$path"), 0775, true);
      copy($path,"prod/$path");
      $change++;
      echo "copy\t$path\n";
    }
  }
  return $change;
}
?>
