<?php

namespace SunlightExtend\Sitemap;

use Sunlight\Database\Database as DB;
use Sunlight\Database\TreeReader;
use Sunlight\Database\TreeReaderOptions;
use Sunlight\Extend;
use Sunlight\Page\Page;
use SunlightExtend\Sitemap\Filter\SitemapTreeFilter;

class DataCollector
{
    /** @var array */
    private $data = [
        'pages' => [],
        'articles' => [],
    ];
    /** @var array<int> */
    private $catIds = [];

    public function collectData(): array
    {
        $this->loadPages();
        $this->loadArticles();

        // event
        Extend::call('sitemap.items', ['data' => &$this->data]);

        return $this->data;
    }

    private function loadPages(): void
    {
        $treeReader = new TreeReader('page');

        $options = new TreeReaderOptions();
        $options->columns = ['slug', 'type', 'ord', 'visible', 'public', 'level'];
        $options->sortBy = 'ord';
        $options->filter = new SitemapTreeFilter([]);

        foreach ($treeReader->getFlatTree($options) as $page) {
            if ($page['type'] == Page::CATEGORY) {
                $this->catIds[] = $page['id'];
            }
            $this->data['pages'][$page['id']] = [
                'slug' => $page['slug'],
                'time' => null,
                'type' => Page::TYPES[$page['type']]
            ];
        }
    }

    private function loadArticles(): void
    {
        $arts = DB::query(
            'SELECT id, slug, home1, `time` 
                    FROM ' . DB::table('article') . ' 
                    WHERE home1 IN (' . DB::arr($this->catIds) . ') AND `public`=1 AND visible=1 AND confirmed=1 AND `time`<' . time() . ' 
                    ORDER BY home1 ASC, `time` ASC');

        while ($art = DB::row($arts)) {
            $this->data['articles'][$art['id']] = [
                'slug' => $this->data['pages'][$art['home1']]['slug'] . ' / ' . $art['slug'],
                'time' => $art['time'],
                'type' => 'article'
            ];
        }
    }
}