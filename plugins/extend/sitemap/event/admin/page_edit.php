<?php

use SunlightExtend\Sitemap\SitemapGenerator;
use SunlightExtend\Sitemap\SitemapIndexGenerator;
use SunlightExtend\Sitemap\SitemapRemover;

return function (array $args): void {

    if (!$this->getConfig()['auto_generate']) {
        return;
    }

    if (
        /* change slug */ $args['page']['slug'] != $args['changeset']['slug']
        /* change parent */ || (isset($args['changeset']['node_parent']) && $args['page']['node_parent'] != $args['changeset']['node_parent'])
        /* change public */ || $args['page']['public'] != $args['changeset']['public']
        /* change visible */ || $args['page']['visible'] != $args['changeset']['visible']
        /* change level */ || (
            isset($args['changeset']['level_inherit'])
            && $args['changeset']['level_inherit'] == 0
            && $args['changeset']['level'] > 0
        )
    ) {
        $gen = SitemapGenerator::factory();
        $gen->generate();
    }
};
