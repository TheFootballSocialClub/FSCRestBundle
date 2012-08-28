<?php

namespace FSC\Common\RestBundle\REST;

use FSC\Common\RestBundle\Model\Representation\AtomLink;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class AtomLinkFactory
{
    /**
     * @see http://www.iana.org/assignments/link-relations/link-relations.xml
     *
     * @var array
     */
    static protected $ianaRelations = array(
        'alternate', 'appendix', 'archives', 'author', 'bookmark', 'canonical', 'chapter', 'collection', 'contents',
        'copyright', 'current', 'describedby', 'disclosure', 'duplicate', 'edit', 'edit-media', 'enclosure', 'first',
        'glossary', 'help', 'hosts', 'hub', 'icon', 'index', 'item', 'last', 'latest-version', 'license', 'lrdd',
        'monitor', 'monitor-group', 'next', 'next-archive', 'nofollow', 'noreferrer', 'payment', 'predecessor-version',
        'prefetch', 'prev', 'previous', 'prev-archive', 'related', 'replies', 'search', 'section', 'self', 'service',
        'start', 'stylesheet', 'subsection', 'successor-version', 'tag', 'up', 'version-history', 'via', 'working-copy',
        'working-copy-of',
    );

    protected $relPrefix;

    public function __construct($relPrefix = '')
    {
        $this->relPrefix = $relPrefix;
    }

    public function create($rel, $href, $type = null)
    {
        if (!in_array($rel, static::$ianaRelations)) {
            $rel = $this->relPrefix.$rel;
        }

        return AtomLink::create($rel, $href, $type);
    }

    public static function getIanaRelations()
    {
        return static::$ianaRelations;
    }
}
