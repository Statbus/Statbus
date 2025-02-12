<?php

namespace App\Service;

use HTMLPurifier;

class HTMLSanitizerService
{
    public $purifier;

    public function __construct($config = [])
    {
        require_once __DIR__ . '/../../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
        if (!$config) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('AutoFormat.Linkify', true);
            $config->set('HTML.Allowed', 'br, hr, a[href], font[color], b, h1, h2, h3, h4, h5, em, i, blockquote, ul, ol, li, B, BR, U, HR, p');
            $config->set('HTML.TargetBlank', true);
            $config->set('URI.HostBlacklist', '');
        }
        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * sanitizeString
     *
     * Runs the string through HTML purifier
     *
     * Also strips out any href attribute that starts with a `?`
     *
     * @param string $string
     * @return string
     */
    public function sanitizeString(
        string $string,
        ?\HTMLPurifier_Config $config = null
    ): string {
        $string = html_entity_decode($string);
        $string = preg_replace('/(href=\'\?[\S]+\')/', '', $string);
        $string = nl2br($string);
        $string = str_replace(["</p>\n<br>", "</p>\n<br />", "</p><br>", "</p><br />"], "</p>", $string);
        if ($config) {
            return $this->purifier->purify($string, $config);
        }
        return $this->purifier->purify($string);
    }

    public static function sanitizeStringWithConfig(\HTMLPurifier_Config $config, string $string): string
    {
        require_once __DIR__ . '/../../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
        $purifier = new HTMLPurifier();
        return $purifier->purify($string, $config);
    }
}
