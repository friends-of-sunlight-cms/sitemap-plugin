<?php

use Sunlight\Router;

return function (array $args) {
    if (!$this->getConfig()['footer_link']) {
        return '';
    }

    if (file_exists(SL_ROOT . DIRECTORY_SEPARATOR . 'sitemap.xml')) {
        $link = Router::path('sitemap.xml');
        $args['output'] .= '<li><a href="' . _e($link) . '" target="_blank">Sitemap</a></li>';
    }
};