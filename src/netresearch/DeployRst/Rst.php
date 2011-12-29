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
 * @link     https://gitorious.nr/php/deploy-rst
 */
namespace netresearch\DeployRst;

/**
 * Helper methods to extract information from reStructuredText files
 *
 * @category Tools
 * @package  DeployRst
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  http://www.gnu.org/licenses/agpl.html AGPL v3 or later
 * @link     https://gitorious.nr/php/deploy-rst
 */
class Rst
{
    /**
     * Extract meta data from rST source
     *
     * @param string $file Path to rST file
     *
     * @return array Meta data
     */
    public static function extractMeta($file)
    {
        $lines  = file($file);
        $inmeta = false;
        $metas  = array();

        foreach ($lines as $line) {
            $tline = trim($line);
            if ($inmeta) {
                $parts = explode(':', $tline, 3);
                if (count($parts) != 3 || $parts[0] != '') {
                    break;
                }
                $metas[trim($parts[1])] = trim($parts[2]);
            }
            if ($tline == '.. meta::') {
                $inmeta = true;
            }
        }

        return $metas;
    }

}

?>