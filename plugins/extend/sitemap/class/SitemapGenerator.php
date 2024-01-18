<?php

namespace SunlightExtend\Sitemap;

use Sunlight\Core;
use Sunlight\Extend;
use Sunlight\Page\Page;
use Sunlight\Router;

class SitemapGenerator
{
    private const ALLOWED_FREQUENCIES = [
        self::FREQUENCY_ALWAYS,
        self::FREQUENCY_HOURLY,
        self::FREQUENCY_DAILY,
        self::FREQUENCY_WEEKLY,
        self::FREQUENCY_MONTHLY,
        self::FREQUENCY_YEARLY,
        self::FREQUENCY_NEVER
    ];

    public const FREQUENCY_ALWAYS = 'always';
    public const FREQUENCY_HOURLY = 'hourly';
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_YEARLY = 'yearly';
    public const FREQUENCY_NEVER = 'never';

    /** @var SitemapIndexGenerator */
    private $indexGenerator;
    /** @var SitemapRemover */
    private $sitemapRemover;

    /** @var array<string, float> */
    private $priority = [
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

    /** @var array<string, string> */
    private $changefreq = [
        'default' => self::FREQUENCY_MONTHLY,
        Page::TYPES[Page::SECTION] => self::FREQUENCY_MONTHLY,
        Page::TYPES[Page::CATEGORY] => self::FREQUENCY_WEEKLY,
        Page::TYPES[Page::BOOK] => self::FREQUENCY_DAILY,
        Page::TYPES[Page::GALLERY] => self::FREQUENCY_MONTHLY,
        Page::TYPES[Page::GROUP] => self::FREQUENCY_MONTHLY,
        Page::TYPES[Page::FORUM] => self::FREQUENCY_DAILY,
        Page::TYPES[Page::PLUGIN] => self::FREQUENCY_MONTHLY,
        'article' => self::FREQUENCY_MONTHLY,
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
        $pluginConfig = Core::$pluginManager->getPlugins()->getExtend('sitemap')->getConfig();
        return new self(new DataCollector($pluginConfig), new SitemapIndexGenerator(), new SitemapRemover());
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
            if (!is_numeric($prio)) {
                $this->priority[$type] = 0.5;
                continue;
            }
            $prio = max($prio, 0);
            $prio = min($prio, 1);
            $this->priority[$type] = (float)$prio;
        }
    }

    private function normalizeFreq(): void
    {
        foreach ($this->changefreq as $type => $freq) {
            if (!is_string($freq)) {
                $this->changefreq[$type] = self::FREQUENCY_MONTHLY;
                continue;
            }
            if (!in_array($freq, self::ALLOWED_FREQUENCIES)) {
                $this->changefreq[$type] = self::FREQUENCY_MONTHLY;
            }
        }
    }
}