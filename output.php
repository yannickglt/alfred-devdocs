<?php

$saveto = "docs/test.html";

$ch = curl_init ($url);


curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);


$raw=curl_exec($ch);

curl_close ($ch);

if(file_exists($saveto)){
  unlink($saveto);
}

$style = file_get_contents('style.css');

$fp = fopen($saveto,'x');
fwrite($fp, $raw.'<style>'.$style.'</style>');
fclose($fp);

exec('qlmanage -p docs/test.html');


?>

