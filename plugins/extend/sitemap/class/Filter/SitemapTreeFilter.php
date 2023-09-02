<?php

namespace SunlightExtend\Sitemap\Filter;

use Sunlight\Database\TreeFilterInterface;
use Sunlight\Database\TreeReader;
use Sunlight\Page\Page;

class SitemapTreeFilter implements TreeFilterInterface
{
    /** @var string */
    private $sql;

    function __construct()
    {
        $this->sql = $this->compileSql([]);
    }

    /**
     * @param array $node
     * @param TreeReader $reader
     * @return bool
     */
    function filterNode(array $node, TreeReader $reader): bool
    {
        return
            /* visibility */ $node['visible'] == 1
            /* page level */ && $node['level'] == 0
            /* page public */ && $node['public'] == 1
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
        $sql = '%__node__%.public=1';
        $sql .= ' AND %__node__%.visible=1';
        $sql .= ' AND (%__node__%.type!=' . Page::SEPARATOR . ' AND %__node__%.type!=' . Page::LINK . ')';
        $sql .= ' AND %__node__%.level=0';
        return $sql;
    }
}
