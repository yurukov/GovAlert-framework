<?php

require_once("common.php");
if ($debug) exit;
require_once("twitter.php");

echo "\n\nSTREAMS ".date("r")."\n";
runTasks(count($argv)>1);
postTwitter();
echo "END\n";

?>
