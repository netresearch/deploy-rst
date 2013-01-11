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
 * Filter interface
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://github.com/netresearch/deploy-rst
 */
interface Driver_Confluence_Filter
{
    /**
     * Modify rST markup.
     *
     * @param string $doc reStructuredText markup
     *
     * @return string Modified reStructuredText markup
     */
    public function preFilter($doc);

    /**
     * Modify confluence markup.
     *
     * @param string $doc Confluence markup
     *
     * @return string Modified confluence markup
     */
    public function postFilter($doc);
}

?>
