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
 *  	- Configure devdoc conf tasks
 *  	- Reput doc and open url tasks
 *  	- Finish update of plist template
 *  	- Put list when you need to chose a doc before a conf action
 *  	- Bind all actions to task
 *  	- Create a task, put all
 *  	- Create a task, nuke
 *  	- Clean repository
 *  	- Merge !
 */
namespace CFPropertyList;
require_once 'vendor/autoload.php';
require_once 'workflows.php';
require_once 'documentations.php';

class DevDocsConf {

	private $commands      = array('add' => 1, 'remove' => 1, 'refresh' => 1, 'list' => 1);
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
    	unset($this->currentConfig[$this->currentCmd[1]]);
    	$this->regeneratePlist();
    	$this->workflows->result( 
        	'devdocs--conf--remove',
        	'',
        	'Remove',
        	'',
        	$this->rootPath.'/doc.png'
        );
    }

    private function refreshCmd(){
    	foreach ($this->currentConfig as $docName => $key) {
            file_put_contents(
            	$this->rootPath."/".$key.".json", 
            	file_get_contents("http://docs.devdocs.io/".$key."/index.json")
            );
    	}
    	$this->workflows->result( 
        	'devdocs--conf--refresh',
        	'',
        	'refresh',
        	'',
        	$this->rootPath.'/doc.png'
        );
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
// $query = "refresh";
// $query = "remove Angular.js";
// $query = "add Angular.js";
// $query = "add Backbone.js";
// $query = "add Sass";
// $query = "remove Sass";
// $query = "remove bouleshit";
new DevDocsConf($query, $documentations);
