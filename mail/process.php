<?php
$path = dirname(__FILE__);
require_once("$path/../streams/common.php");
require_once("$path/../streams/twitter.php");
require_once("$path/strategy/tasks.php");

$mail = stream_get_contents(STDIN);

ob_start();

echo date()."> Нов мейл \n";

if (strpos($mail,"From: info@strategy.bg")!==false)
  mailStrategy($mail);

echo "done\n";

$contents = ob_get_flush();
file_put_contents("$path/../log/mail.log",$contents,FILE_APPEND);
?>
