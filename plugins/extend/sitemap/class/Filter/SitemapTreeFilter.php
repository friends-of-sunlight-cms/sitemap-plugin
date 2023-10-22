<?php

namespace SunlightExtend\Sitemap\Filter;

use Sunlight\Database\TreeFilterInterface;
use Sunlight\Database\TreeReader;
use Sunlight\Page\Page;

class SitemapTreeFilter implements TreeFilterInterface
{
    /** @var array */
    private $options;
    /** @var string */
    private $sql;

    /**
     * Supported options:
     * ------------------
     * - check_visible (1) check page's visible column 1/0
     * - check_public (1) check page's public column 1/0
     *
     * @param array{
     *     check_visible?: bool,
     *     check_public?: bool,
     * } $options see description
     */
    function __construct(array $options)
    {
        // defaults
        $options += [
            'check_visible' => true,
            'check_public' => true,
        ];

        $this->options = $options;
        $this->sql = $this->compileSql($options);
    }

    /**
     * @param array $node
     * @param TreeReader $reader
     * @return bool
     */
    function filterNode(array $node, TreeReader $reader): bool
    {
        return
            /* visibility */ (!$this->options['check_visible'] || $node['visible'] == 1)
            /* page level */ && $node['level'] == 0
            /* page public */ && (!$this->options['check_public'] || $node['public'] == 1)
            /* type check */ && ($node['type'] != Page::SEPARATOR || $node['type'] != Page::LINK);
    }

    /**
     * @param array $invalidNode
     * @param array $validChildNode
     * @param TreeReader $reader
     * @return bool
     */
    function acceptInvalidNodeWithValidChild(array $invalidNode, array $validChildNode, TreeReader $reader): bool
    {
        return true;
    }

    /**
     * @param TreeReader $reader
     * @return string
     */
    function getNodeSql(TreeReader $reader): string
    {
        return $this->sql;
    }

    /**
     * @param array $options
     * @return string
     */
    private function compileSql(array $options): string
    {
        // base conditions
        $sql = '%__node__%.level=0';
        if ($options['check_public']) {
            $sql .= ' AND %__node__%.public=1';
        }
        if ($options['check_visible']) {
            $sql .= ' AND %__node__%.visible=1';
        }
        $sql .= ' AND (%__node__%.type!=' . Page::SEPARATOR . ' AND %__node__%.type!=' . Page::LINK . ')';
        return $sql;
    }
}
