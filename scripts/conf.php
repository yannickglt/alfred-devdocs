<?php

require_once 'workflows.php';

// $workflows = new Workflows();
// $workflows->result('myuid', 'Query '.$query.'  -  '.__FILE__, 'Hey !', 'Sub', 'doc.png');
// echo $workflows->toxml();

class DevDocsConf {

    private $jsonConf;
    private $confPath = 'conf/';
    private $workflows;


    public function __construct($query) {
        $this->workflows = new Workflows();
    }

//     private function checkCache ($documentation) {

//          // Keep the docs in cache during 7 days
//         if (!file_exists("$documentation.json") || (filemtime("$documentation.json") <= time() - 86400 * 7)) {
//             file_put_contents("$documentation.json", file_get_contents("http://docs.devdocs.io/$documentation/index.json"));
//         }
//     }

//     private function processDocumentation ($documentation, $query) {

//         $query = strtolower($query);

//         $baseUrl = "http://docs.devdocs.io/$documentation.html";

//         $data = json_decode(file_get_contents("$documentation.json"));
//         $entries = $data->entries;

//         $found = array();
//         foreach ($entries as $key => $result) {
//             $value = strtolower(trim($result->name));
//             $description = strtolower(utf8_decode(strip_tags($result->type)));
            
//             if (strpos($value, $query) === 0) {
//                 if (!isset($found[$value])) {
//                     $found[$value] = true;
//                     $result->documentation = $documentation;
//                     $this->results[0][] = $result;
//                 }
//             }
//             else if (strpos($value, $query) > 0) {
//                 if (!isset($found[$value])) {
//                     $found[$value] = true;
//                     $result->documentation = $documentation;
//                     $this->results[1][] = $result;
//                 }
//             }
//             else if (strpos($description, $query) !== false) {
//                 if (!isset($found[$value])) {
//                     $found[$value] = true;
//                     $result->documentation = $documentation;
//                     $this->results[2][] = $result;
//                 }
//             }
//         }

//     }

//     private function render () {
//         foreach ($this->results as $level => $results) {
//             foreach ($results as $result) {
//                 $this->workflows->result( $result->name, json_encode($result), $result->name.' ('.$result->type.')', $result->path, $result->documentation.'.png' );
//             }
//         }
//         echo $this->workflows->toxml();
//     }
}

// new DevDocs($query, $documentation);
