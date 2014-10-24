<?php
require_once("common.php");

$url = codeToUrl($_GET["code"]);
if ($url===false) {
  header("HTTP/1.0 404 Not Found");
  echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL /".$_GET["code"]." was not found on this server.</p>
</body></html>";
exit;
}

header('Location: '.$url);
?>
