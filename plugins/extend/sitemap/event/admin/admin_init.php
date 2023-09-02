<?php

use Sunlight\User;

return function (array $args) {
    $args['admin']->modules['content-sitemap'] = [
        'title' => _lang('sitemap.module.title'),
        'access' => $this->getConfig()['level_access'] <= User::getLevel(),
        'parent' => 'content',
        'script' => __DIR__ . '/../../script/content_sitemap.php',
    ];
};
