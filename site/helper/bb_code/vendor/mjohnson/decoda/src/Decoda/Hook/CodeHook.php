<?php
/**
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Decoda\Hook;
defined('_JEXEC') or die;


/**
 * Caches [code] blocks so that the inner content doesn't get processed.
 */
class CodeHook extends AbstractHook {

    /**
     * Cached code blocks during the parsing process.
     *
     * @var array
     */
    protected $_cache = array();

    /**
     * Cache code blocks before parsing. It use regexp (?R) recursivity mask to deal with other nested code tags
     * see http://php.net/manual/en/regexp.reference.recursive.php and http://stackoverflow.com/questions/2909588/regex-bbcode-perfecting-nested-quote#answer-2909930 for more informations
     *
     * @param string $string
     * @return mixed
     */
    public function beforeParse($string) {
        $this->_cache = array();

        return preg_replace_callback('/\[code(.*?)\](((?R)|.)*?)\[\/code\]/is', array($this, '_en_codeCallback'), $string);
    }

    /**
     * Retrieve code blocks after parsing.
     *
     * @param string $string
     * @return mixed
     */
    public function afterParse($string) {
        $string = preg_replace_callback('/\<pre(.*?)><code>(\$\$CODE(\d+)\$\$)<\/code>\<\/pre>/is', array($this, '_de_codeCallback'), $string);

        $this->_cache = array();

        return $string;
    }

    /**
     * Encode content using base64.
     *
     * @param array $matches
     * @return string
     */
    protected function _en_codeCallback(array $matches) {
        $cacheSize = count($this->_cache);
        $this->_cache[$cacheSize] = $matches[2];

        return '[code' . $matches[1] . ']$$CODE' . $cacheSize . '$$[/code]';
    }

    /**
     * Decode content using base64.
     *
     * @param array $matches
     * @return string
     */
    protected function _de_codeCallback(array $matches) {
        return '<pre' . $matches[1] . '><code>' . $this->_cache[$matches[3]] . '</code></pre>';
    }

}
