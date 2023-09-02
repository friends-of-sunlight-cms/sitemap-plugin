<?php

use Sunlight\Router;
use Sunlight\User;

return function (array $args) {
    $userHasAccess = $this->getConfig()['level_access'] <= User::getLevel();
    if (!$userHasAccess) {
        return;
    }

    $args['modules'] += [
        'others' => [
            'label' => _lang('admin.menu.other'),
            'modules' => [
                'sitemap' => [
                    'label' => _lang('sitemap.module.title'),
                    'url' => Router::admin('content-sitemap'),
                    'icon' => $this->getAssetPath('public/images/icons/sitemap.png'),
                    'access' => $userHasAccess,
                ]
            ]
        ]
    ];
};
