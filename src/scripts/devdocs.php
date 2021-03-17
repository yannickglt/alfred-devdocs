<?php

ini_set('display_errors', 0);
ini_set('memory_limit', '-1');

use CFPropertyList\CFPropertyList;

require_once 'vendor/autoload.php';
require_once 'workflows.php';

class DevDocs {
  private static $cacheDirectory = 'cache/';

  private $workflows;
  private $results;
  private $baseUrl;
  private $cacheLife;
  private $template;

  public function __construct($query, $doc) {
    $this->baseUrl = getenv('BASE_URL') ?: 'https://devdocs.io/';
    $this->cacheLife = (int)(getenv('CACHE_LIFE') ?: '7');
    $this->template = getenv('TEMPLATE') ?: '$baseUrl$documentation/$path';
    $this->workflows = new Workflows();
    $cache = $this->workflows->cache();
    if ($cache !== false) {
      self::$cacheDirectory = $cache . '/';
    }
    $this->results = [
      0 => [],
      1 => [],
      2 => []
    ];

    $documentations = $this->getDocumentations();
    if (!isset($doc) || empty($doc)) {
      $rootPath = str_replace('/scripts', '', $this->workflows->path());
      $pList = (new CFPropertyList($rootPath . '/info.plist'))->toArray();
      foreach ($pList['connections'] as $key => $value) {
        if (array_key_exists($key, $documentations)) {
          $this->checkCache($key);
          $this->processDocumentation($key, $query);
        }
      }
    } else {
      $this->checkCache($doc);
      $this->processDocumentation($doc, $query);
    }
    $this->render();
  }

  private function getDocumentations() {
    $docFile = self::$cacheDirectory . 'docs.json';
    // Keep the docs in cache before expired
    if (!file_exists($docFile) || ($this->cacheLife >= 0 && filemtime($docFile) <= time() - 86400 * $this->cacheLife)) {
      $docContent = $this->workflows->fetch($this->baseUrl . 'docs/docs.json');
      file_put_contents($docFile, $docContent);
    } else {
      $docContent = file_get_contents($docFile);
    }
    $docs = json_decode($docContent);
    $documentations = [];
    foreach ($docs as $doc) {
      $doc->fullName = $doc->name . (!empty($doc->version) ? ' ' . $doc->version : '');
      $documentations[$doc->slug] = $doc;
    }
    return $documentations;
  }

  private function checkCache($documentation) {
    if (!file_exists(self::$cacheDirectory)) {
      mkdir(self::$cacheDirectory);
    }
    $docFile = self::$cacheDirectory . $documentation . '.json';
    error_log("Checking existence of cache at $docFile");
    // Keep the docs in cache before expired
    if (!file_exists($docFile) || ($this->cacheLife >= 0 && filemtime($docFile) <= time() - 86400 * $this->cacheLife)) {
      error_log("Download doc for $documentation at \"" . $this->baseUrl . 'docs/' . $documentation . '/index.json') . "\"";
      file_put_contents($docFile, file_get_contents($this->baseUrl . 'docs/' . $documentation . '/index.json'));
    }
  }

  private function processDocumentation($documentation, $query) {

    $query = strtolower($query);
    $data = json_decode(file_get_contents(self::$cacheDirectory . $documentation . '.json'));
    if ($data === null) {
      unlink(self::$cacheDirectory . $documentation . '.json');
    }

    $entries = $data->entries;

    $found = [];
    foreach ($entries as $key => $result) {
      $value = strtolower(trim($result->name));
      $description = strtolower(utf8_decode(strip_tags($result->type)));

      if (empty($query)) {
        $found[$value] = true;
        $result->documentation = $documentation;
        $this->results[0][] = $result;
      } else if (strpos($value, $query) === 0) {
        if (!isset($found[$value])) {
          $found[$value] = true;
          $result->documentation = $documentation;
          $this->results[0][] = $result;
        }
      } else if (strpos($value, $query) > 0) {
        if (!isset($found[$value])) {
          $found[$value] = true;
          $result->documentation = $documentation;
          $this->results[1][] = $result;
        }
      } else if (strpos($description, $query) !== false) {
        if (!isset($found[$value])) {
          $found[$value] = true;
          $result->documentation = $documentation;
          $this->results[2][] = $result;
        }
      }
    }

    if ((count($this->results[0]) === 0) && (count($this->results[1]) === 0) && (count($this->results[2]) === 0)) {
      $this->results[0][] = (object) [
        'name' => 'No results.',
        'documentation' => $documentation
      ];
    }

  }

  private function render() {
    foreach ($this->results as $level => $results) {
      foreach ($results as $result) {
        $title = empty($result->type) ? $result->name : "$result->name ($result->type)";
        $vars = array(
          '$baseUrl' => $this->baseUrl,
          '$documentation' => $result->documentation,
          '$docalt' => str_replace("~", "-", $result->documentation),
          '$name' => $result->name,
          '$path' => $result->path
        );
        //$url = $this->baseUrl . $result->documentation . '/' . $result->path;
        $url = strtr($this->template, $vars);
        $this->workflows->result($result->name, $url, $title, $result->path, $result->documentation . '.png', 'yes', $result->name);
      }
    }
    echo $this->workflows->toxml();
  }
}

$query = isset($query) ? $query : '';
$documentation = isset($documentation) ? $documentation : '';
new DevDocs($query, $documentation);
