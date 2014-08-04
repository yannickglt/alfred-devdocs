<?php

require_once 'workflows.php';

class DevDocs {

    private $workflows;
    private $results;
    private static $baseUrl = 'http://devdocs.io/';
    private static $cacheDirectory = 'cache/';

    public function __construct($query, $doc) {
        $this->workflows = new Workflows();
        $this->results = array(
            0 => array(),
            1 => array(),
            2 => array()
        );

        if (!isset($doc) || empty($doc)) {
            include 'documentations.php';
            foreach ($documentations as $documentation) {
                $this->checkCache($documentation);
            }
            foreach ($documentations as $documentation) {
                $this->processDocumentation($documentation, $query);
            }
        } else {
            $this->checkCache($doc);
            $this->processDocumentation($doc, $query);
        }
        $this->render();
    }

    private function checkCache ($documentation) {
        if (!file_exists(self::$cacheDirectory)) {
            mkdir(self::$cacheDirectory);
        }
        $docFile = self::$cacheDirectory.$documentation.'.json';
         // Keep the docs in cache during 7 days
        if (!file_exists($docFile) || (filemtime($docFile) <= time() - 86400 * 7)) {
            file_put_contents($docFile, file_get_contents('http://docs.devdocs.io/'.$documentation.'/index.json'));
        }
    }

    private function processDocumentation ($documentation, $query) {

        $query = strtolower($query);
        $data = json_decode(file_get_contents(self::$cacheDirectory.$documentation.'.json'));
        $entries = $data->entries;

        $found = array();
        foreach ($entries as $key => $result) {
            $value = strtolower(trim($result->name));
            $description = strtolower(utf8_decode(strip_tags($result->type)));
            
            if (strpos($value, $query) === 0) {
                if (!isset($found[$value])) {
                    $found[$value] = true;
                    $result->documentation = $documentation;
                    $this->results[0][] = $result;
                }
            }
            else if (strpos($value, $query) > 0) {
                if (!isset($found[$value])) {
                    $found[$value] = true;
                    $result->documentation = $documentation;
                    $this->results[1][] = $result;
                }
            }
            else if (strpos($description, $query) !== false) {
                if (!isset($found[$value])) {
                    $found[$value] = true;
                    $result->documentation = $documentation;
                    $this->results[2][] = $result;
                }
            }
        }

    }

    private function render () {
        foreach ($this->results as $level => $results) {
            foreach ($results as $result) {
                $this->workflows->result( $result->name, self::$baseUrl.$result->documentation.'/'.$result->path, $result->name.' ('.$result->type.')', $result->path, $result->documentation.'.png' );
            }
        }
        echo $this->workflows->toxml();
    }
}

new DevDocs($query, $documentation);
