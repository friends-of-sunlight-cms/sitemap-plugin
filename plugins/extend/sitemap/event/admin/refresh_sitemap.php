<?php

use SunlightExtend\Sitemap\SitemapGenerator;
use SunlightExtend\Sitemap\SitemapIndexGenerator;
use SunlightExtend\Sitemap\SitemapRemover;

return function (array $args): void {
    if (!$this->getConfig()['auto_generate']) {
        return;
    }

    $gen = SitemapGenerator::factory();
    $gen->generate();
};
