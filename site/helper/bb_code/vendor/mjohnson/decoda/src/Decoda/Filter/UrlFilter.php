<?php
/**
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Decoda\Filter;
defined('_JEXEC') or die;


use Decoda\Decoda;

/**
 * Provides tags for URLs.
 */
class UrlFilter extends AbstractFilter {

    /**
     * Configuration.
     *
     * @type array
     */
    protected $_config = array(
        'protocols' => array('http', 'https', 'ftp', 'irc', 'telnet', 'mailto'),
        'defaultProtocol' => 'http'
    );

    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'url' => array(
            'htmlTag' => 'a',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE,
            'attributes' => array(
                'default' => true,
                'target' => '/^(?:blank|parent|top)$/'
            ),
            'mapAttributes' => array(
                'default' => 'href'
            )
        ),
        'link' => array(
            'aliasFor' => 'url'
        )
    );

    /**
     * Using shorthand variation if enabled.
     *
     * @param array $tag
     * @param string $content
     * @return string
     */
    public function parse(array $tag, $content) {
        $url = isset($tag['attributes']['href']) ? $tag['attributes']['href'] : $content;
        $protocols = $this->getConfig('protocols');
        $defaultProtocol = $this->getConfig('defaultProtocol');
        $hasProtocol = preg_match('/^(' . implode('|', $protocols) . ')/i', $url);

        if (!in_array($defaultProtocol, $protocols)) {
            $defaultProtocol = 'http';
        }

        // Allow relative and absolute paths, else check protocols
        if (!preg_match('/^(\.\.?)?\//', $url)) {
            if (!$hasProtocol) {
                // Only allow if no protocol exists, just not the ones not in the list
                if (preg_match('/^(?![a-z]+:\/\/)/', $url) && filter_var($defaultProtocol . '://' . $url, FILTER_VALIDATE_URL)) {
                    $url = $defaultProtocol . '://' . $url;
                } else {
                    return $url;
                }
            }

            // Return an invalid URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }

        $tag['attributes']['href'] = $url;

        if (!empty($tag['attributes']['target'])) {
            $tag['attributes']['target'] = '_' . $tag['attributes']['target'];
        }

        if ($this->getParser()->getConfig('shorthandLinks')) {
            $tag['content'] = $this->message('link');

            return '[' . parent::parse($tag, $content) . ']';
        }

        return parent::parse($tag, $content);
    }

    /**
     * Strip a node but keep the URL regardless of location.
     *
     * @param array $tag
     * @param string $content
     * @return string
     */
    public function strip(array $tag, $content) {
        $url = isset($tag['attributes']['href']) ? $tag['attributes']['href'] : $content;

        return parent::strip($tag, $url);
    }

}
