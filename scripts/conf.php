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
	private $output        = array();
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

        if ($this->parseCommand($query) && method_exists($this, $this->currentCmd[0].'Cmd')) {
        	$this->{$this->currentCmd[0].'Cmd'}();
        	$this->flushToAlfred();
        }
    }

    private function openPlist(){
    	$this->pList = new CFPropertyList($this->rootPath.'/info.plist');
    	$this->pList = $this->pList->toArray();
    }

    private function parseCommand($rawQuery){
    	$this->currentCmd = explode(' ', $rawQuery);
    	return (!empty($this->currentCmd) && key_exists($this->currentCmd[0], $this->commands) && (count($this->currentCmd) - 1) >= $this->commands[$this->currentCmd[0]] );
    }

    private function buildRootPath(){
    	$this->rootPath = str_replace('/scripts', '', $this->workflows->path());
    }

    private function setCurrentConfig(){
    	$flippedDocumentations = array_flip($this->documentations);
    	foreach ($this->pList['connections'] as $key => $value) {
    		if(array_key_exists($key, $flippedDocumentations)){
    			$this->currentConfig[$flippedDocumentations[$key]] = $key;
    		}
    	}
    }

    private function flushToAlfred(){
    	echo $this->workflows->toxml();
    }

    private function regeneratePlist(){
    	$buildPlist = function($rootPath, $documentations){
	    	ob_start();
			include $rootPath.'/scripts/plist.phtml';
			$fileContent = ob_get_contents();
			ob_end_clean();

			file_put_contents($rootPath.'/info.plist', $fileContent);
    	};
    	$buildPlist($this->rootPath, $this->currentConfig);
    }

    private function addCmd(){
    	$this->currentConfig[$this->currentCmd[1]] = $this->documentations[$this->currentCmd[1]];
    	$this->regeneratePlist();
    	$this->workflows->result( 
        	'devdocs--conf--add',
        	'',
        	'Add',
        	'',
        	$this->rootPath.'/doc.png'
        );
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
    	$filter = (isset($this->currentCmd[1]))? $this->currentCmd[1] : '';
    	$docs = array_filter(
    		$this->documentations, 
    		function($docKey) use ($filter){
    			return ($filter !== '')? stripos($docKey, $filter) !== false : true;
    		}
    	);
    	foreach ($docs as $docName => $key) {
            $this->workflows->result( 
            	$key,
            	json_encode($conf),
            	$docName,
            	(isset($this->currentConfig[$docName]))? 'Already in your doc list' : '',
            	$this->rootPath.'/'.$key.'.png'
            );
        }
    }

}
// $query = "add Angular.js";
// $query = "remove bouleshit";
new DevDocsConf($query, $documentations);

//  -- Connexion --
// <key> $doc </key>
// <array>
// 	<dict>
// 		<key>destinationuid</key>
// 		<string>output</string>
// 		<key>modifiers</key>
// 		<integer>0</integer>
// 		<key>modifiersubtext</key>
// 		<string></string>
// 	</dict>
// </array>


//  -- Objects --
// <dict>
// 	<key>config</key>
// 	<dict>
// 		<key>argumenttype</key>
// 		<integer>0</integer>
// 		<key>escaping</key>
// 		<integer>127</integer>
// 		<key>keyword</key>
// 		<string> -- doc --</string>
// 		<key>runningsubtext</key>
// 		<string>Searching for "{query}"</string>
// 		<key>script</key>
// 		<string>$query = "{query}";
// 	$documentation = '-- doc --';
// 	require_once("scripts/devdocs.php");</string>
// 		<key>subtext</key>
// 		<string>Search for -- title -- "{query}"</string>
// 		<key>title</key>
// 		<string>DevDocs - -- title --</string>
// 		<key>type</key>
// 		<integer>1</integer>
// 		<key>withspace</key>
// 		<true/>
// 	</dict>
// 	<key>type</key>
// 	<string>alfred.workflow.input.scriptfilter</string>
// 	<key>uid</key>
// 	<string>-- doc --</string>
// 	<key>version</key>
// 	<integer>0</integer>
// </dict>