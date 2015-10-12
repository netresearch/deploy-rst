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
 * Modifies the Confluence markup to look nice in AIDA docs.
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://github.com/netresearch/deploy-rst
 */
class Driver_Confluence_Filter_Aida implements Driver_Confluence_Filter
{
    public $doc;

    /**
     * Modify rST markup.
     *
     * @param string $doc reStructuredText markup
     *
     * @return string Modified reStructuredText markup
     */
    public function preFilter($doc)
    {
        //remove headline
        $doc = preg_replace('#^\*\*+\n[^\n]+\n\*\*+\n#', '', $doc);
        $this->doc = $doc;
        $this->convertSphinxRoles();
        return $this->doc;
    }

    /**
     * Modify confluence markup.
     *
     * @param string $doc Confluence markup
     *
     * @return string Modified confluence markup
     */
    public function postFilter($doc)
    {
        $this->doc = $doc;

        $this->showAttachments();
        $this->fixToc();
        $this->fixImages();

        return $this->doc;
    }

    public function convertSphinxRoles()
    {
        $this->doc = preg_replace_callback(
            '#:(doc|ref):`([^`]+)`#',
            function ($parts) {
                $link = $parts[2];
                if (strpos($link, '<') !== false) {
                    list($title, $link) = explode('<', $link);
                    $linkParts = explode('/', trim($link, ' >'));
                    $link      = end($linkParts);
                    $title     = trim($title);
                    return '`' . $title . ' <' . $link . '>`_';
                }
                return '`' . $link . ' <' . $link . '>`_';
            },
            $this->doc
        );
    }

    public function fixImages()
    {
        $this->doc = preg_replace_callback(
            '#(![^.!]+?\.(:?png|jpg))#',
            function ($parts) {
                if (substr($parts[0], 0, 5) == '!doc/') {
                    $parts[0] = '!' . substr($parts[0], 5);
                }
                return str_replace('/', '-', $parts[0]);
            },
            $this->doc
        );
        $this->doc = preg_replace_callback(
            '#^{gallery:include=(.+)}$#m',
            function ($parts) {
                return str_replace(
                    array(',doc/', '=doc/', '/'),
                    array(',', '=', '-'),
                    $parts[0]
                );
            },
            $this->doc
        );
    }

    public function fixToc()
    {
        $this->doc = str_replace(
            '{toc}',
            "{panel}\n{toc:maxLevel=4}\n{panel}",
            $this->doc
        );
    }

    public function showAttachments()
    {
        $this->doc .= <<<CFL

h1. Attachments
{attachments}

CFL;
    }
}

?>
