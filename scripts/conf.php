<?php
/**
 *  Configure Alfred DevDocs Workflow
 *  Availables Commands :
 *  	add : Add a doc to the workflow
 *  	remove  : Remove a doc to the workflow
 *  	refrehs : Refresh all installed docs databases
 *  	list : List all availlables databases
 */
/**
 *  Todo :
 *  	- create a snippet for quick add doc in docs availables
 *  	- edit a plist file and save it
 */
namespace CFPropertyList;
require_once 'vendor/autoload.php';
require_once 'workflows.php';
require_once 'documentations.php';

class DevDocsConf {

	private $commands      = array('add' => 1, 'remove' => 1, 'refresh' => 0, 'list' => 0);
	private $currentCmd    = array();
	private $currentConfig = array();
    private $query;
    private $documentations;
    private $workflows;
    private $pList;
    private $rootPath;


    public function __construct($query, $documentations) {
		$this->query          = $query;
		$this->documentations = $documentations;
		$this->workflows      = new \Workflows();

        $this->parseCommand($query);
        $this->buildRootPath();
        $this->openPlist();
        $this->setCurrentConfig();

        if ($this->parseCommand($query)) {
        	$this->{$this->currentCmd[0].'Cmd'}();
        }
    }

    private function openPlist(){
    	$this->pList = new CFPropertyList($this->rootPath.'/info.plist');
    	$this->pList = $this->pList->toArray();
    }

    private function parseCommand($rawQuery){
    	$this->currentCmd = explode(' ', $rawQuery);
    	return (!empty($this->currentCmd) && key_exists($this->currentCmd[0], $this->commands) && (count($this->currentCmd) - 1) === $this->commands[$this->currentCmd[0]] );
    }

    private function buildRootPath(){
    	$this->rootPath = str_replace('/scripts', '', $this->workflows->path());
    }

    private function setCurrentConfig(){
    	foreach ($this->pList['connections'] as $key => $value) {
    		if(in_array($key, $this->documentations)){
    			array_push($this->currentConfig, $key);
    		}
    	}
    }

    private function addCmd(){
    	echo PHP_EOL;
       	echo "Add Command : ".$this->currentCmd[1];
        echo PHP_EOL;
    }

    private function removeCmd(){
    	echo PHP_EOL;
       	echo "Remove Command : ".$this->currentCmd[1];
        echo PHP_EOL;
    }

    private function updateCmd(){
    	echo PHP_EOL;
       	echo "Update Command";
        echo PHP_EOL;
    }

    private function listCmd(){
    	echo PHP_EOL;
       	echo "List Command";
        echo PHP_EOL;
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
$query = "add rails";
// $query = "remove bouleshit";
new DevDocsConf($query, $documentations);