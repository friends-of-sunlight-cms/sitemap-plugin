<?php

use Sunlight\Extend;
use SunlightExtend\Sitemap\SitemapGenerator;
use SunlightExtend\Sitemap\SitemapIndexGenerator;
use SunlightExtend\Sitemap\SitemapRemover;

return function (array $args): void {
    if (!$this->getConfig()['auto_generate']) {
        return;
    }

    if (
        /* change slug */ $args['article']['slug'] != $args['changeset']['slug']
        /* change parent */ || $args['article']['home1'] != $args['changeset']['home1']
        /* change public */ || $args['article']['public'] != $args['changeset']['public']
        /* change visible */ || $args['article']['visible'] != $args['changeset']['visible']
        /* change confirmed */ || $args['article']['confirmed'] != $args['changeset']['confirmed']
    ) {
        $gen = SitemapGenerator::factory();
        $gen->generate();
    }
};
