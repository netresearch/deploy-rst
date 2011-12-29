<?php
namespace netresearch\DeployRst;


class Rst
{
    /**
     * Extract meta data from rST source
     *
     * @param string $rst rST source content
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