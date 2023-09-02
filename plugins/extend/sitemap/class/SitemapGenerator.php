<?php

namespace SunlightExtend\Sitemap;

use Sunlight\Extend;
use Sunlight\Page\Page;
use Sunlight\Router;

class SitemapGenerator
{
    private const ALLOWED_PRIORITY = [
        self::PRIORITY_ALWAYS,
        self::PRIORITY_HOURLY,
        self::PRIORITY_DAILY,
        self::PRIORITY_WEEKLY,
        self::PRIORITY_MONTHLY,
        self::PRIORITY_YEARLY,
        self::PRIORITY_NEVER
    ];

    public const PRIORITY_ALWAYS = 'always';
    public const PRIORITY_HOURLY = 'hourly';
    public const PRIORITY_DAILY = 'daily';
    public const PRIORITY_WEEKLY = 'weekly';
    public const PRIORITY_MONTHLY = 'monthly';
    public const PRIORITY_YEARLY = 'yearly';
    public const PRIORITY_NEVER = 'never';

    /** @var SitemapIndexGenerator */
    private $indexGenerator;
    /** @var SitemapRemover */
    private $sitemapRemover;

    /** @var array<string, string> */
    private $priority = [
        'default' => self::PRIORITY_MONTHLY,
        Page::TYPES[Page::SECTION] => self::PRIORITY_MONTHLY,
        Page::TYPES[Page::CATEGORY] => self::PRIORITY_WEEKLY,
        Page::TYPES[Page::BOOK] => self::PRIORITY_DAILY,
        Page::TYPES[Page::GALLERY] => self::PRIORITY_MONTHLY,
        Page::TYPES[Page::GROUP] => self::PRIORITY_MONTHLY,
        Page::TYPES[Page::FORUM] => self::PRIORITY_DAILY,
        Page::TYPES[Page::PLUGIN] => self::PRIORITY_MONTHLY,
        'article' => self::PRIORITY_MONTHLY,
    ];

    /** @var array<string, float> */
    private $changefreq = [
        'default' => 0.5,
        Page::TYPES[Page::SECTION] => 1.0,
        Page::TYPES[Page::CATEGORY] => 0.7,
        Page::TYPES[Page::BOOK] => 0.8,
        Page::TYPES[Page::GALLERY] => 0.8,
        Page::TYPES[Page::GROUP] => 0.5,
        Page::TYPES[Page::FORUM] => 0.8,
        Page::TYPES[Page::PLUGIN] => 0.5,
        'article' => 0.5,
    ];

    /** @var array */
    private $data = [];

    private $result = [];

    public function __construct(
        DataCollector         $dataCollector,
        SitemapIndexGenerator $indexGenerator,
        SitemapRemover        $sitemapRemover
    )
    {
        $this->data = $dataCollector->collectData();
        $this->indexGenerator = $indexGenerator;
        $this->sitemapRemover = $sitemapRemover;

        // event
        Extend::call('sitemap.defaults', [
            'priority' => &$this->priority,
            'changefreq' => &$this->changefreq
        ]);

        $this->normalizePriority();
        $this->normalizeFreq();
    }

    public static function factory(): SitemapGenerator
    {
        return new self(new DataCollector(), new SitemapIndexGenerator(), new SitemapRemover());
    }

    /**
     * @return array
     * @throws \DOMException
     */
    public function generate(): array
    {
        // remove all old sitemap files
        $this->sitemapRemover->remove();

        // generate sitemap file for each category
        $this->generateCategories();

        // generate sitemap index file
        $result = $this->indexGenerator->generate(array_keys($this->data));
        // insert into first position
        array_unshift($this->result, $result);

        return $this->result;
    }

    /**
     * @throws \DOMException
     */
    private function generateCategories(): void
    {
        foreach ($this->data as $category => $items) {
            $dom = new \DomDocument('1.0', 'utf-8');

            $stylesheetEl = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . _e(Router::path('plugins/extend/sitemap/public/gss.xsl', ['absolute' => true])) . '"');

            $urlsetEl = $dom->createElement('urlset');
            $urlsetEl->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

            foreach ($items as $item) {
                $urlEl = $dom->createElement('url');

                $locEl = $dom->createElement('loc', _e(Router::slug($item['slug'], ['absolute' => true])));
                $urlEl->appendChild($locEl);

                // optional element, pages don't have it
                if (!empty($item['time'])) {
                    $lastmodEl = $dom->createElement('lastmod', date('Y-m-d\TH:i:sP', $item['time']));
                    $urlEl->appendChild($lastmodEl);
                }

                $changeFreqEl = $dom->createElement('changefreq', _e($this->changefreq[$item['type'] ?? 'default']));
                $urlEl->appendChild($changeFreqEl);

                $priorityEl = $dom->createElement('priority', _e($this->priority[$item['type'] ?? 'default']));
                $urlEl->appendChild($priorityEl);

                $urlsetEl->appendChild($urlEl);
            }

            $dom->appendChild($stylesheetEl);
            $dom->appendChild($urlsetEl);

            $dom->formatOutput = true;
            $filename = 'sitemap_' . _e($category) . '.xml';
            $size = $dom->save(SL_ROOT . $filename);

            $this->result[] = [
                'filename' => $filename,
                'size' => $size !== false ? $size : 0
            ];
        }
    }

    private function normalizePriority(): void
    {
        foreach ($this->priority as $type => $prio) {
            if (!is_string($prio)) {
                $this->priority[$type] = self::PRIORITY_MONTHLY;
                continue;
            }
            if (!in_array($prio, self::ALLOWED_PRIORITY)) {
                $this->priority[$type] = self::PRIORITY_MONTHLY;
            }
        }
    }

    private function normalizeFreq(): void
    {
        foreach ($this->changefreq as $type => $freq) {
            if (!is_numeric($freq)) {
                $this->changefreq[$type] = 0.5;
                continue;
            }
            $freq = max($freq, 0);
            $freq = min($freq, 1);
            $this->changefreq[$type] = (float)$freq;
        }
    }
}