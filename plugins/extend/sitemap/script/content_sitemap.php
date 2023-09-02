<?php

use Sunlight\Core;
use Sunlight\GenericTemplates;
use Sunlight\Message;
use Sunlight\Router;
use Sunlight\Xsrf;
use SunlightExtend\Sitemap\SitemapGenerator;

defined('SL_ROOT') or exit;

$sitemapPlugin = Core::$pluginManager->getPlugins()->getExtend('sitemap');
$sitemapConfig = $sitemapPlugin->getConfig();

if ($sitemapConfig['auto_generate']) {
    $output .= Message::warning(_lang('sitemap.module.warn_message'));
}

if (isset($_POST['confirm'])) {
    // regenerate files
    $gen = SitemapGenerator::factory();
    $result = $gen->generate();

    $_SESSION['sitemap_result'] = $result;

    // redirect
    $_admin->redirect(Router::admin('content-sitemap', ['query' => ['done' => 1]]));
    return;
}

// output
if (isset($_GET['done'])) {
    $message = _lang('global.done');

    // detail
    $result = null;
    if (isset($_SESSION['sitemap_result'])) {
        $result = $_SESSION['sitemap_result'];
        $_SESSION['sitemap_result'] = null;
        unset($_SESSION['sitemap_result']);

        $list = '<ul>';
        foreach ($result as $r) {
            $list .= '<li>' . $r['filename'] . ' <em>(' . GenericTemplates::renderFilesize($r['size']) . ')</em></li>';
        }
        $list .= '</ul>';
        $message =_lang('sitemap.module.done_message') . $list;
    }

    $output .= Message::ok($message, true);
}

$output .= _buffer(function () { ?>
    <form action="" method="post">
        <fieldset>
            <legend><?= _lang('sitemap.module.legend') ?></legend>
            <p><?= _lang('sitemap.module.description') ?></p>
            <ul>
                <li>sitemap.xml</li>
                <li>sitemap_pages.xml</li>
                <li>sitemap_articles.xml</li>
                <li>sitemap_*.xml - <em><?= _lang('sitemap.module.description.files') ?></em></li>
            </ul>
            <button class="button bigger" name="confirm" type="submit" onclick="return Sunlight.confirm();">
                <img class="icon" alt="warn" src="<?= _e(Router::path('admin/public/images/icons/warn.png')) ?>">
                <?= _lang('sitemap.module.button') ?>
            </button>
            <?= Xsrf::getInput() ?>
        </fieldset>
    </form>
<?php });