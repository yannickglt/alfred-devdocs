<?php
/**
 *  Configure Alfred DevDocs Workflow
 *  Availables Commands :
 *  	add : Add a doc to the workflow
 *  	remove  : Remove a doc to the workflow
 *  	refrehs : Refresh all installed docs databases
 *  	list : List all availlables databases
 *      nuke : Reset to no docs selected
 *      addall : Add all docs in workflow
 */
namespace CFPropertyList;
require_once 'vendor/autoload.php';
require_once 'workflows.php';
require_once 'documentations.php';

class DevDocsConf {

	private $commands      = array('add' => 1, 'remove' => 1, 'refresh' => 1, 'list' => 1 , 'select' => 0, 'addAll' => 0 , 'nuke' => 0);
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

        if (method_exists($this, $this->currentCmd[0].'Cmd')) {
        	$this->{$this->currentCmd[0].'Cmd'}();
        }
    }

    private function openPlist(){
    	$this->pList = new CFPropertyList($this->rootPath.'/info.plist');
    	$this->pList = $this->pList->toArray();
    }

    private function parseCommand($rawQuery){
    	$this->currentCmd = explode(' ', $rawQuery);
        if(!empty($this->currentCmd)){
            $commandToCheck = (strpos($this->currentCmd[0], 'select') === 0)? 'select' : $this->currentCmd[0];
            return ( 
                ($commandToCheck === 'select' || key_exists($commandToCheck, $this->commands)) && 
                (count($this->currentCmd) - 1 >= $this->commands[$commandToCheck])
            );
        }
        else{
            $this->currentCmd[0] = '';
        }
        return false;
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
        asort($this->currentConfig, SORT_STRING | SORT_FLAG_CASE);
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

    private function filter($search, $collection){
        $filtered = array_filter(
            $collection, 
            function($key) use ($search){
                return ($search !== '')? stripos($key, $search) !== false : true;
            }
        );
        asort($filtered, SORT_STRING | SORT_FLAG_CASE);
        return $filtered;
    }

    private function selectAddCmd(){
        $search         = (isset($this->currentCmd[1]))? $this->currentCmd[1] : '';
        $docsAvailables = array_diff($this->documentations, $this->currentConfig);
        $docsAvailables = $this->filter($search, $docsAvailables);
        foreach ($docsAvailables as $docName => $key) {
            $this->workflows->result( 
                $key,
                "add ".$docName,
                $docName,
                '',
                $this->rootPath.'/'.$key.'.png'
            );
        }
        $this->flushToAlfred();
    }

    private function addCmd(){
    	$this->currentConfig[$this->currentCmd[1]] = $this->documentations[$this->currentCmd[1]];
    	$this->regeneratePlist();
        echo $this->currentCmd[1].' added !';
    }

    private function selectRemoveCmd(){
        $search         = (isset($this->currentCmd[1]))? $this->currentCmd[1] : '';
        $docsAvailables = $this->filter($search, $this->currentConfig);
        foreach ($docsAvailables as $docName => $key) {
            $this->workflows->result( 
                $key,
                "remove ".$docName,
                $docName,
                '',
                $this->rootPath.'/'.$key.'.png'
            );
        }
        $this->flushToAlfred();
    }

    private function removeCmd(){
    	unset($this->currentConfig[$this->currentCmd[1]]);
        $this->regeneratePlist();
        echo $this->currentCmd[1].' removed !';
    }

    private function selectRefreshCmd(){
        $search         = (isset($this->currentCmd[1]))? $this->currentCmd[1] : '';
        $docsAvailables = $this->filter($search, $this->currentConfig);
        $this->workflows->result( 
            'all',
            "refresh all",
            "All docs",
            '',
            $this->rootPath.'/all.png'
        );
        foreach ($docsAvailables as $docName => $key) {
            $this->workflows->result( 
                $key,
                "refresh ".$docName,
                $docName,
                '',
                $this->rootPath.'/'.$key.'.png'
            );
        }
        $this->flushToAlfred();
    }

    private function refreshCmd(){
        $updateAll = ($this->currentCmd[1] === 'all');
        $docToUpdate = ($updateAll)? array($this->currentCmd[1] => $this->currentConfig[$this->currentCmd[1]]) : $this->currentConfig;
    	foreach ($docToUpdate as $docName => $key) {
            file_put_contents(
            	$this->rootPath."/cache/".$key.".json", 
            	file_get_contents("http://docs.devdocs.io/".$key."/index.json")
            );
    	}
        echo (($updateAll)? 'All data docs': $this->currentCmd[1].' doc').' updated !';
    }

    private function listCmd(){
        $search = (isset($this->currentCmd[1]))? $this->currentCmd[1] : '';
        $docs   = $this->filter($search, $this->documentations);
    	foreach ($docs as $docName => $key) {
            $this->workflows->result( 
            	$key,
            	json_encode($conf),
            	$docName,
            	(isset($this->currentConfig[$docName]))? 'Already in your doc list' : '',
            	$this->rootPath.'/'.$key.'.png'
            );
        }
        $this->flushToAlfred();
    }

    private function addAllCmd(){
        $this->currentConfig = $this->documentations;
        $this->regeneratePlist();
        echo 'All docs added !';
    }

    private function nukeCmd(){
        $this->currentConfig = array();
        $this->regeneratePlist();
        echo 'All docs removed !';
    }

}
new DevDocsConf($query, $documentations);
