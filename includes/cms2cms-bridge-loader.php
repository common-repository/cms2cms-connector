<?php

class ConnectorCmsBridgeLoader {

    private $uri;
    private $key;

    /**
     * @param $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Absolute path
     * @return string
     */
    protected function absolutePath()
    {
        return ABSPATH;
    }

    /**
     * Create bridge file
     */
    public function extract()
    {
        if (false == empty($this->key)) {
            $bridgeUrl = sprintf('%s/key/%s/json/response', $this->uri, $this->key);
            $fileList = null;
            if (ini_get('allow_url_fopen')) {
                $fileList = file_get_contents($bridgeUrl);
            }
            if ($fileList == null && function_exists('curl_init')) {
                $curl = curl_init($bridgeUrl);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $fileList = curl_exec($curl);
                curl_close($curl);
            }
            if ($fileList == null) {
                throw new Exception('Failed to setup connection. Please, contact us.');
            }

            $bridgeFolder = $this->absolutePath() . '/cms2cms';
            foreach (json_decode($fileList) as $key => $content) {
                if (false == is_dir($this->absolutePath() . current(explode('/', $key)))) {
                    $bridgeFolder = $this->absolutePath() . current(explode('/', $key));
                    if (false == @mkdir($this->absolutePath() . current(explode('/', $key)))) {
                        throw new Exception('Connection was not established.');
                    }
                }

                $this->write($key, $content);
                @chmod($bridgeFolder, 0755);
            }
        }
    }

    /**
     * Check if key same
     * @param $dbKey
     * @param $host
     */
    public function checkKey($dbKey, $host)
    {
        if (false == isset($dbKey)) {
            return;
        }

        $this->setKey($dbKey);
        $this->setUri($host);
        $this->extract();
    }

    /**
     * Write files
     * @param $filePath
     * @param $fileContent
     * @throws \Exception
     */
    public function write($filePath, $fileContent)
    {
        $fileName = $this->absolutePath().$filePath;

        if (true == file_exists($fileName) && true == is_writable($fileName)) {
            @file_put_contents($fileName, $fileContent);
        } else {
            $file = @fopen($fileName, "x+");
            if (false == $file || false == is_writable($fileName)) {
                throw new Exception('Connection was not established.');
            }

            @fwrite($file, $fileContent);
            fclose($file);
            @chmod($fileName, 0644);
        }
    }

}
