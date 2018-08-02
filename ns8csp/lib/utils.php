<?php
/**
 * Utility functions
 * (c) 2017 NS8.com
 */

class Utils
{

    public static function remoteAddress()
    {

        $xf = '';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $xf = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $remoteAddr = '';

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remoteAddr = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($xf) && trim($xf) != '') {

            $xf = trim($xf);
            $xfs = array();

            //  see if multiple addresses are in the XFF header
            if (strpos($xf, '.') != false) {
                $xfs = explode(',', $xf);
            } else {
                if (strpos($xf, ' ') != false) {
                    $xfs = explode(' ', $xf);
                }
            }

            if (count($xfs) > 0)
            {

                //  get first public address, since multiple private routings can occur and be added to forwarded list
                for ($i = 0; $i < count($xfs); $i++) {

                    $ipTrim = trim($xfs[$i]);

                    if (substr($ipTrim, 0, 7) == '::ffff:' && count(explode('.', $ipTrim)) == 4)
                        $ipTrim = substr($ipTrim, 7);

                    if ($ipTrim != "" && substr($ipTrim, 0, 3) != "10." && substr($ipTrim, 0, 7) != "172.16." && substr($ipTrim, 0, 7) != "172.31." && substr($ipTrim, 0, 8) != "127.0.0." && substr($ipTrim, 0, 8) != "192.168." && $ipTrim != "unknown" && $ipTrim != "::1")
                        return ($ipTrim);

                }
                $xf = trim($xfs[0]);
            }

            if (substr($xf, 0, 7) == '::ffff:' && count(explode('.', $xf)) == 4)
                $xf = substr($xf, 7);

            //  a tiny % of hits have an unknown ip address
            if (substr($xf, 0, 7) == "unknown")
                return "127.0.0.1";

            return ($xf);

        } else {

            //  a tiny % of hits have an unknown ip address, so return a default address
            if (substr($remoteAddr, 0, 7) == "unknown")
                return "127.0.0.1";

            return ($remoteAddr);
        }
    }
}