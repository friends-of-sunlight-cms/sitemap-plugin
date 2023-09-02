<?php

namespace SunlightExtend\Sitemap;

class SitemapRemover
{
    public function remove(): void
    {
        $files = glob(SL_ROOT . 'sitemap*.xml');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
