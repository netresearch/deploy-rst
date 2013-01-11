<?php
declare(encoding='utf-8');
/**
 * Part of DeployRst
 *
 * PHP Version 5
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://github.com/netresearch/deploy-rst
 */
namespace netresearch\DeployRst;

/**
 * Atlassian Confluence wiki driver.
 * Stores rST documents in a confluence wiki
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://github.com/netresearch/deploy-rst
 */
class Driver_Confluence extends Driver
{
    protected $cflHost;
    protected $cflUser;
    protected $cflPass;
    protected $cflSpace;
    protected $cflPage;
    protected $filterName;
    protected $noDeploy = false;

    protected $filterObj;

    /**
     * Marks the beginning of the automatic deployed rST document
     *
     * @var string
     */
    public $markerBegin = "{html}<!-- BEGIN deploy-content -->{html}\n";

    /**
     * Marks the end of the automatic deployed rST document
     *
     * @var string
     */
    public $markerEnd   = "{html}<!-- END deploy-content -->{html}\n";

    /**
     * Create a new instance, set some variables, load tools and parameters
     *
     * @param string $file    Path to rST file
     * @param string $metas   rST meta settings
     * @param string $options CLI options
     */
    public function __construct($file, $metas, $options)
    {
        parent::__construct($file, $metas, $options);
        $this->loadTools();
        $this->loadParameters();
    }

    /**
     * Run the driver: Deploy the rST file into the wiki
     *
     * @return void
     */
    public function run()
    {
        if ($this->noDeploy) {
            echo $this->convertRst();
            return;
        }

        $this->storePage(
            $this->embedIntoPage(
                $this->getCurrentPage(),
                $this->convertRst()
            )
        );
    }

    /**
     * Loads confluence-specific options into the command line parser
     *
     * @param object $parser Command line parser object
     *
     * @return void
     */
    public static function loadHelp(\Console_CommandLine $parser)
    {
        $parser->addOption(
            'user',
            array(
                'long_name' => '--user',
                'optional' => true,
                'action' => 'StoreString',
                'description' => 'Confluence user name'
            )
        );
        $parser->addOption(
            'password',
            array(
                'long_name' => '--password',
                'optional' => true,
                'action' => 'StoreString',
                'description' => 'Confluence user password'
            )
        );
        $parser->addOption(
            'no_deploy',
            array(
                'long_name' => '--no-deploy',
                'optional' => true,
                'action' => 'StoreTrue',
                'description' => 'Do not deploy, echo output only'
            )
        );
        $parser->addOption(
            'filter',
            array(
                'long_name' => '--filter',
                'optional' => true,
                'action' => 'StoreString',
                'description' => 'rST filter name (e.g. "aida")'
            )
        );
        $parser->addOption(
            'confluence_host',
            array(
                'long_name'   => '--confluence-host',
                'optional'    => true,
                'action'      => 'StoreString',
                'description' => 'Confluence host name (with http://)',
                'help_name'   => 'host'
            )
        );
        $parser->addOption(
            'confluence_space',
            array(
                'long_name'   => '--confluence-space',
                'optional'    => true,
                'action'      => 'StoreString',
                'description' => 'Confluence space name',
                'help_name'   => 'space'
            )
        );
        $parser->addOption(
            'confluence_page',
            array(
                'long_name'   => '--confluence-page',
                'optional'    => true,
                'action'      => 'StoreString',
                'description' => 'Confluence page name',
                'help_name'   => 'page'
            )
        );
    }

    /**
     * Load the paths of required tools: rst2confluence and confluence-cli
     *
     * @return void
     *
     * @throws Exception When one of the tools cannot be found
     */
    public function loadTools()
    {
        error_reporting(error_reporting() & ~E_STRICT);
        include_once 'System.php';
        if (!class_exists('System')) {
            throw new Exception(
                'Could not find PEAR\'s "System" class', 10
            );
        }

        $this->cmd['rst2c'] = \System::which('rst2confluence');
        if ($this->cmd['rst2c'] === false) {
            throw new Exception('rst2confluence not found', 11);
        }
        $this->cmd['cflcli'] = \System::which('confluence-cli');
        if ($this->cmd['cflcli'] === false) {
            throw new Exception('confluence-cli not found', 12);
        }
    }

    /**
     * Load required parameters into the class variables
     *
     * @return void
     */
    protected function loadParameters()
    {
        $this->cflHost  = $this->loadSetting('confluence-host');
        $this->cflSpace = $this->loadSetting('confluence-space');
        $this->cflPage  = $this->loadSetting('confluence-page');
        $this->cflUser  = $this->loadSetting('user');
        $this->cflPass  = $this->loadSetting('password');
        $this->filter   = $this->loadSetting('filter', false);
        $this->noDeploy = $this->loadSetting('no_deploy', false);
    }

    /**
     * Convert the rST file to confluence markup
     *
     * @return string Confluence markup
     */
    public function convertRst()
    {
        $doc = file_get_contents($this->file);

        $doc = $this->runFilter('preFilter', $doc);

        list($rcDoc, $retval) = Exec::runPipe(
            $this->cmd['rst2c'], $doc
        );
        if ($retval !== 0) {
            throw new Exception(
                'Error converting rst to confluence format', 20
            );
        }

        return $this->runFilter('postFilter', $rcDoc);
    }

    /**
     * Run a filter on the document confluence markup.
     *
     * @param string $method Filter method to execute
     * @param string $doc    Confluence markup
     *
     * @return string Filtered confluence markup
     */
    public function runFilter($method, $doc)
    {
        if (!$this->filter) {
            return $doc;
        }

        $filter = $this->loadFilter();
        return $filter->$method($doc);
    }

    /**
     * Load filter object and return it
     *
     * @return Driver_Confluence_Filter
     */
    protected function loadFilter()
    {
        if ($this->filterObj !== null) {
            return $this->filterObj;
        }

        $filterClass = 'netresearch\DeployRst\Driver_Confluence_Filter_'
            . ucfirst($this->filter);
        if (!class_exists($filterClass)) {
            throw new Exception(
                'Confluence filter class does not exist: ' . $filterClass, 21
            );
        }

        $this->filterObj = new $filterClass();
        return $this->filterObj;
    }

    /**
     * Load the current wiki page contents from confluence
     *
     * @return string Confluence markup
     */
    public function getCurrentPage()
    {
        //we cannot pipe it, see
        // https://studio.plugins.atlassian.com/browse/CSOAP-122
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

    /**
     * Embeds the rST document confluence markup into the existing document
     * markup.
     *
     * @param string $curDoc  Current confluence document
     * @param string $newCont rST confluence document
     *
     * @return string Resulting document
     */
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

    /**
     * Store the confluence document in the confluence wiki.
     *
     * @param string $newDoc New document in confluence markup
     *
     * @return void
     */
    public function storePage($newDoc)
    {
        //we cannot pipe because of
        //  https://studio.plugins.atlassian.com/browse/CSOAP-121
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