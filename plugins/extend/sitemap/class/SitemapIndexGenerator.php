<?php

namespace SunlightExtend\Sitemap;

use Sunlight\Router;

class SitemapIndexGenerator
{

    /**
     * @return array<string, int>
     * @throws \DOMException
     */
    public function generate(array $categories): array
    {
        // generate sitemap index file
        $dom = new \DomDocument('1.0', 'utf-8');

        $stylesheetEl = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . _e(Router::path('plugins/extend/sitemap/public/gss.xsl', ['absolute' => true])) . '"');

        $sitemapindexEl = $dom->createElement('sitemapindex');
        $sitemapindexEl->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($categories as $category) {
            $sitemapEl = $dom->createElement('sitemap');

            $locEl = $dom->createElement('loc', _e(Router::path('sitemap_' . _e($category) . '.xml', ['absolute' => true])));
            $sitemapEl->appendChild($locEl);

            $lastmodEl = $dom->createElement('lastmod', date('Y-m-d\TH:i:sP', time()));
            $sitemapEl->appendChild($lastmodEl);

            $sitemapindexEl->appendChild($sitemapEl);
        }

        $dom->appendChild($stylesheetEl);
        $dom->appendChild($sitemapindexEl);

        $dom->formatOutput = true;
        $filename = 'sitemap.xml';
        $size = $dom->save(SL_ROOT . $filename);

        return [
            'filename' => $filename,
            'size' => $size,
        ];
    }
}