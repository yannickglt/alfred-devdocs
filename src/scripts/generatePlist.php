<?php
require 'documentations.php';
ob_start();
include 'plist.phtml';
$fileContent = ob_get_contents();
ob_end_clean();

file_put_contents('info.plist', $fileContent);