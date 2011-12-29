<?php
namespace netresearch\DeployRst;

class Driver_Confluence extends Driver
{
    protected $options;
    protected $file;
    protected $metas;

    protected $cflHost;
    protected $cflUser;
    protected $cflPass;
    protected $cflSpace;
    protected $cflPage;

    public $markerBegin = "{html}<!-- BEGIN deploy-content -->{html}\n";
    public $markerEnd   = "{html}<!-- END deploy-content -->{html}\n";


    public function __construct($file, $metas, $options)
    {
        $this->options = $options;
        $this->file    = $file;
        $this->metas   = $metas;
        $this->loadTools();
        $this->loadParameters();
    }

    public function run()
    {
        $this->storePage(
            $this->embedIntoPage(
                $this->getCurrentPage(),
                $this->convertRst()
            )
        );
    }

    public function loadTools()
    {
        require_once 'System.php';

        $this->cmd['rst2c'] = \System::which('rst2confluence');
        if ($this->cmd['rst2c'] === false) {
            throw new Exception('rst2confluence not found', 10);
        }
        $this->cmd['cflcli'] = \System::which('confluence-cli');
        if ($this->cmd['cflcli'] === false) {
            throw new Exception('confluence-cli not found', 11);
        }
    }

    protected function loadParameters()
    {
        $this->cflHost  = $this->loadSetting('confluence-host');
        $this->cflSpace = $this->loadSetting('confluence-space');
        $this->cflPage  = $this->loadSetting('confluence-page');
        $this->cflUser  = $this->loadSetting('user');
        $this->cflPass  = $this->loadSetting('password');
    }


    public function convertRst()
    {
        list($rcDoc, $retval) = Exec::run(
            $this->cmd['rst2c'] . ' ' . escapeshellarg($this->file)
        );
        if ($retval !== 0) {
            throw new Exception('Error converting rst to confluence format', 20);
        }

        return $rcDoc;
    }

    public function getCurrentPage()
    {
        //we cannot pipe it, see https://studio.plugins.atlassian.com/browse/CSOAP-122
        $tmpfile = tempnam(sys_get_temp_dir(), 'deploy-confluence-');
        $cmd = sprintf(
            $this->cmd['cflcli']
            . ' --server %s --user %s --password %s'
            . ' --action getPageSource --space %s --title %s --file %s --quiet',
            escapeshellarg($this->cflHost),
            escapeshellarg($this->cflUser),
            escapeshellarg($this->cflPass),
            escapeshellarg($this->cflSpace),
            escapeshellarg($this->cflPage),
            escapeshellarg($tmpfile)
        );
        list($lastline, $retval) = Exec::run($cmd);
        $curDoc = file_get_contents($tmpfile);
        unlink($tmpfile);

        //list($curDoc, $retval) = run($cmd);
        if ($retval !== 0) {
            throw new Exception(
                'Error fetching confluence document source' . "\n" . $lastline,
                21
            );
        }
        if (strlen($curDoc) == 0) {
            throw new Exception('Document is empty. This might be a bug', 22);
        }

        return $curDoc;
    }


    public function embedIntoPage($curDoc, $newCont)
    {
        $begin = strpos($curDoc, $this->markerBegin);
        $end   = strpos($curDoc, $this->markerEnd);

        if ($begin === false && $end === false) {
            //add it to the end
            $newDoc = $curDoc . "\n\n"
                . $this->markerBegin . $newCont . $this->markerEnd;
        } else if ($begin  === false || $end === false) {
            throw new Exception('Begin or end marker not found', 23);
        } else if ($end < $begin) {
            throw new Exception('Begin marker after end marker', 24);
        } else {
            //replace it
            $newDoc = substr($curDoc, 0, $begin)
                . $this->markerBegin . $newCont . $this->markerEnd
                . substr($curDoc, $end + strlen($this->markerEnd));
        }

        return $newDoc;
    }

    public function storePage($newDoc)
    {
        //we cannot pipe because of https://studio.plugins.atlassian.com/browse/CSOAP-121
        $tmpfile = tempnam(sys_get_temp_dir(), 'deploy-confluence-');
        file_put_contents($tmpfile, $newDoc);
        $cmd = sprintf(
            $this->cmd['cflcli']
            . ' --server %s --user %s --password %s'
            . ' --action storePage --space %s --title %s --file %s --quiet',
            escapeshellarg($this->cflHost),
            escapeshellarg($this->cflUser),
            escapeshellarg($this->cflPass),
            escapeshellarg($this->cflSpace),
            escapeshellarg($this->cflPage),
            escapeshellarg($tmpfile)
        );
        list($lastline, $retval) = Exec::run($cmd);

        unlink($tmpfile);
        if ($retval !== 0) {
            throw new Exception(
                'Error storing new document in confluence' . "\n" . $lastline, 30
            );
        }
    }
}

?>