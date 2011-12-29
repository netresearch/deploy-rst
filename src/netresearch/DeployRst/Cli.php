<?php
namespace netresearch\DeployRst;

class Cli
{
    public $file;
    public $metas;
    public $options;

    public function __construct($options = array())
    {
        $this->options = $options;
    }

    public function run()
    {
        try {
            $this->loadParams();
            $this->loadMeta();
            $this->runDriver();
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            exit($e->getCode());
        }
    }

    protected function loadParams()
    {
        if ($GLOBALS['argc'] <= 1) {
            throw new Exception('Please pass a file name', 1);
        }
        $this->file = $GLOBALS['argv'][1];
        if (!file_exists($this->file)) {
            throw new Exception('File does not exist', 2);
        }
    }

    protected function loadMeta()
    {
        $this->metas = Rst::extractMeta($this->file);
        if (!isset($this->metas['deploy-target'])) {
            throw new Exception('No deploy-target meta directive found', 3);
        }
    }

    protected function runDriver()
    {
        $class = '\\netresearch\DeployRst\\Driver_' . ucfirst($this->metas['deploy-target']);
        if (!class_exists($class)) {
            throw new Exception(
                'No wiki driver found for target ' . $this->metas['deploy-target']
            );
        }

        $driver = new $class($this->file, $this->metas, $this->options);
        $driver->run();
    }

}

?>