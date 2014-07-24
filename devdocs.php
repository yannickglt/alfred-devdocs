<?php
// ****************
//error_reporting(0);
require_once('workflows.php');
$w = new Workflows();

 // Keep the docs in cache during 7 days
if ( filemtime("$documentation.json") <= time()-86400*7  || 1) {
    file_put_contents("$documentation.json", file_get_contents("http://docs.devdocs.io/$documentation/index.json"));
}

if (!isset($query)) { $query = urlencode( "css" ); }
$query = strtolower($query);

$baseUrl = "http://docs.devdocs.io/$documentation/";
$endUrl = ".html";

$data = json_decode(file_get_contents("$documentation.json"));
$entries = $data->entries;

$extras = array();
$extras2 = array();
$found = array();

foreach ($entries as $key => $result) {
	$value = strtolower(trim($result->name));
    $description = utf8_decode(strip_tags($result->type));
    
	if (strpos($value, $query) === 0) {
        if (!isset($found[$value])) {
            $found[$value] = true;
            $w->result( $result->name, $baseUrl.$result->path.$endUrl, $result->name." ".$result->path, $result->type, "$documentation.png" );
        }
    }
    else if (strpos($value, $query) > 0) {
        if (!isset($found[$value])) {
            $found[$value] = true;
            $extras[$key] = $result;
        }
    }
    else if (strpos($description, $query) !== false) {
        if (!isset($found[$value])) {
            $found[$value] = true;
            $extras2[$key] = $result;
        }
    }
}

foreach ($extras as $key => $result) {
    $w->result( $result->name, $baseUrl.$result->path.$endUrl, $result->name.' ('.$result->type.')', $result->path, "$documentation.png"  );
}

foreach ($extras2 as $key => $result) {
    $w->result( $result->name, $baseUrl.$result->path.$endUrl, $result->name.' ('.$result->type.')', $result->path, "$documentation.png"  );
}

echo $w->toxml();
// ****************
