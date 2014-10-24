<?php
$path = dirname(__FILE__);
require_once("$path/../streams/common.php");
require_once("$path/../streams/twitter.php");
require_once("$path/strategy/tasks.php");

strategy_processUrl('http://www.strategy.bg/PublicConsultations/View.aspx?lang=bg-BG&Id=1234');

?>
