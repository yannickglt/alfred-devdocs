<?php
/**
 *  Configure Alfred DevDocs Workflow
 *  Availables Commands :
 *    add : Add a doc to the workflow
 *    remove  : Remove a doc to the workflow
 *    refrehs : Refresh all installed docs databases
 *    list : List all availlables databases
 *      nuke : Reset to no docs selected
 *      addall : Add all docs in workflow
 */
namespace CFPropertyList;
require_once 'vendor/autoload.php';
require_once 'workflows.php';

class DevDocsConf {

  private static $cacheDirectory = 'cache/';

  private $commands = ['add' => 1, 'remove' => 1, 'refresh' => 1, 'list' => 1, 'select' => 0, 'addAll' => 0, 'nuke' => 0];
  private $currentCmd = [];
  private $currentConfig = [];
  private $output = [];
  private $query;
  private $documentations;
  private $workflows;
  private $pList;
  private $rootPath;

  public function __construct($query) {
    $this->query = $query;
    $this->workflows = new \Workflows();
    $cache = $this->workflows->cache();
    if ($cache !== false) {
      self::$cacheDirectory = $cache . '/';
    }

    $this->parseCommand($query);
    $this->buildRootPath();
    $this->openPlist();
    $this->setDocumentations();
    $this->setCurrentConfig();


    if (method_exists($this, $this->currentCmd[0] . 'Cmd')) {
      $this->{$this->currentCmd[0] . 'Cmd'}();
    }
  }

  private function openPlist() {
    $this->pList = new CFPropertyList($this->rootPath . '/info.plist');
    $this->pList = $this->pList->toArray();
  }

  private function parseCommand($rawQuery) {
    $this->currentCmd = explode(' ', $rawQuery);
    if (!empty($this->currentCmd)) {
      $commandToCheck = (strpos($this->currentCmd[0], 'select') === 0) ? 'select' : $this->currentCmd[0];
      return (
        ($commandToCheck === 'select' || key_exists($commandToCheck, $this->commands)) &&
        (count($this->currentCmd) - 1 >= $this->commands[$commandToCheck])
      );
    } else {
      $this->currentCmd[0] = '';
    }
    return false;
  }

  private function buildRootPath() {
    $this->rootPath = str_replace('/scripts', '', $this->workflows->path());
  }

  private function setCurrentConfig() {
    foreach ($this->pList['connections'] as $key => $value) {
      if (array_key_exists($key, $this->documentations)) {
        $this->currentConfig[$key] = $this->documentations[$key];
      }
    }
    uasort($this->currentConfig, function ($elementA, $elementB) {
      return $elementA->slug >= $elementB->slug;
    });
  }

  private function flushToAlfred() {
    echo $this->workflows->toxml();
  }

  private function regeneratePlist() {
    $buildPlist = function ($rootPath, $documentations) { // $documentations are used in the template
      ob_start();
      include $rootPath . '/scripts/plist.phtml';
      $fileContent = ob_get_contents();
      ob_end_clean();
      file_put_contents($rootPath . '/info.plist', $fileContent);
    };
    $buildPlist($this->rootPath, $this->currentConfig);
  }

  private function setDocumentations() {
    $docFile = self::$cacheDirectory . 'docs.json';
    // Keep the docs in cache during 7 days
    if (!file_exists($docFile) || (filemtime($docFile) <= time() - 86400 * 7)) {
      file_put_contents($docFile, file_get_contents('http://devdocs.io/docs/docs.json'));
    }
    $docs = json_decode(file_get_contents($docFile));
    $this->documentations = [];
    foreach ($docs as $doc) {
      $doc->fullName = $doc->name . (!empty($doc->version) ? ' ' . $doc->version : '');
      $this->documentations[$doc->slug] = $doc;
    }
  }

  private function filter($search, $collection) {
    $filtered = array_filter(
      $collection,
      function ($element) use ($search) {
        return ($search !== '') ? stripos($element->slug, $search) !== false : true;
      }
    );
    uasort($filtered, function ($elementA, $elementB) {
      return $elementA->slug >= $elementB->slug;
    });
    return $filtered;
  }

  private function selectAddCmd() {
    $search = (isset($this->currentCmd[1])) ? $this->currentCmd[1] : '';
    $docsAvailables = array_diff_key($this->documentations, $this->currentConfig);
    $docsAvailables = $this->filter($search, $docsAvailables);
    foreach ($docsAvailables as $doc) {
      $this->workflows->result(
        $doc->slug,
        "add " . $doc->slug,
        $doc->fullName,
        '',
        $this->getIcon($doc),
        'yes',
        $doc->slug
      );
    }
    $this->flushToAlfred();
  }

  private function addCmd() {
    $doc = $this->documentations[$this->currentCmd[1]];
    $this->currentConfig[$this->currentCmd[1]] = $doc;
    $this->regeneratePlist();
    if (!file_exists($this->rootPath . '/' . $doc->slug . '.png')) {
      @copy($this->rootPath . '/' . $doc->type . '.png', $this->rootPath . '/' . $doc->slug . '.png');
    }
    echo $this->currentCmd[1] . ' added !';
  }

  private function selectRemoveCmd() {
    $search = (isset($this->currentCmd[1])) ? $this->currentCmd[1] : '';
    $docsAvailables = $this->filter($search, $this->currentConfig);
    foreach ($docsAvailables as $doc) {
      $this->workflows->result(
        $doc->slug,
        "remove " . $doc->slug,
        $doc->fullName,
        '',
        $this->getIcon($doc),
        'yes',
        $doc->slug
      );
    }
    $this->flushToAlfred();
  }

  private function removeCmd() {
    unset($this->currentConfig[$this->currentCmd[1]]);
    $this->regeneratePlist();
    echo $this->currentCmd[1] . ' removed !';
  }

  private function selectRefreshCmd() {
    $search = (isset($this->currentCmd[1])) ? $this->currentCmd[1] : '';
    $docsAvailables = $this->filter($search, $this->currentConfig);
    $this->workflows->result(
      'all',
      "refresh all",
      "All docs",
      '',
      $this->rootPath . '/all.png'
    );
    foreach ($docsAvailables as $doc) {
      $this->workflows->result(
        $doc->slug,
        "refresh " . $doc->slug,
        $doc->fullName,
        '',
        $this->getIcon($doc),
        'yes',
        $doc->slug
      );
    }
    $this->flushToAlfred();
  }

  private function refreshCmd() {
    $updateAll = ($this->currentCmd[1] === 'all');
    $docToUpdate = $updateAll ? $this->currentConfig : [$this->currentCmd[1] => $this->currentConfig[$this->currentCmd[1]]];
    foreach ($docToUpdate as $doc) {
      file_put_contents(
        self::$cacheDirectory . $doc->slug . '.json',
        file_get_contents("http://docs.devdocs.io/" . $doc->slug . "/index.json")
      );
    }
    echo (($updateAll) ? 'All data docs' : $this->currentCmd[1] . ' doc') . ' updated !';
  }

  private function listCmd() {
    $search = (isset($this->currentCmd[1])) ? $this->currentCmd[1] : '';
    $docs = $this->filter($search, $this->documentations);
    foreach ($docs as $doc) {
      $this->workflows->result(
        $doc->slug,
        json_encode($doc),
        $doc->fullName,
        (isset($this->currentConfig[$doc->slug])) ? 'Already in your doc list' : '',
        $this->getIcon($doc),
        'yes',
        $doc->slug
      );
    }
    $this->flushToAlfred();
  }

  private function getIcon($doc) {
    if (file_exists($this->rootPath . '/' . $doc->slug . '.png')) {
      return $this->rootPath . '/' . $doc->slug . '.png';
    } else {
      return $this->rootPath . '/' . $doc->type . '.png';
    }
  }

  private function addAllCmd() {
    $this->currentConfig = $this->documentations;
    $this->regeneratePlist();
    echo 'All docs added !';
  }

  private function nukeCmd() {
    $this->currentConfig = [];
    $this->regeneratePlist();
    echo 'All docs removed !';
  }

}

$query = isset($query) ? $query : '';
new DevDocsConf($query);
