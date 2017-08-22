<?php
# Download entry point utility for licenced files. Should be protected from directory traversal attacks.
include($_SERVER['DOCUMENT_ROOT'] . "/PL_includes/functions.php");

$filename = $_GET["file"];
if (strpos($filename, '/') !== FALSE || !registered_PUPC_online())
	exit();
#readfile("licenced/" . $filename);
header("Content-type: application/x-msdownload",true,200);
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Pragma: no-cache");
header("Expires: 0");
echo readfile("licenced/" . $filename);

?>
