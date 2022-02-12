<?php
/**
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Decoda\Filter;
defined('_JEXEC') or die;


use Decoda\Decoda;
use \DateTime;

/**
 * Provides tags for basic font styling.
 */
class DefaultFilter extends AbstractFilter {

    /**
     * Configuration.
     *
     * @type array
     */
    protected $_config = array(
        'timeFormat' => 'D, M jS Y, H:i'
    );

    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'b' => array(
            'htmlTag' => array('b', 'strong'),
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE
        ),
        'i' => array(
            'htmlTag' => array('i', 'em'),
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE
        ),
        'u' => array(
            'htmlTag' => 'u',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE
        ),
        's' => array(
            'htmlTag' => 'del',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE
        ),
        'sub' => array(
            'htmlTag' => 'sub',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE
        ),
        'sup' => array(
            'htmlTag' => 'sup',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE
        ),
        'abbr' => array(
            'htmlTag' => 'abbr',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_INLINE,
            'attributes' => array(
                'default' => AbstractFilter::ALNUM
            ),
            'mapAttributes' => array(
                'default' => 'title'
            )
        ),
        'br' => array(
            'htmlTag' => 'br',
            'autoClose' => true,
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_NONE
        ),
        'hr' => array(
            'htmlTag' => 'hr',
            'autoClose' => true,
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_NONE
        ),
        'time' => array(
            'htmlTag' => 'time',
            'displayType' => Decoda::TYPE_INLINE,
            'allowedTypes' => Decoda::TYPE_NONE
        )
    );

    /**
     * Parse the timestamps for the time tag.
     *
     * @param array $tag
     * @param string $content
     * @return array
     */
    public function time(array $tag, $content) {
        $timestamp = is_numeric($content) ? $content : strtotime($content);

        $content = date($this->getConfig('timeFormat'), $timestamp);

        $tag['attributes']['datetime'] = date(DateTime::ISO8601, $timestamp);

        return array($tag, $content);
    }

}