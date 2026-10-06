<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_wmarka_articles_slider
 *
 * @copyright   (C) 2026 Beibit Sakhanov <https://webmarka.kz>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

/**
 * Prepared by the Dispatcher:
 *
 * @var \stdClass                 $module
 * @var \Joomla\Registry\Registry $params
 * @var array                     $list         articles, or [group title => articles] when $grouped
 * @var bool                      $grouped
 * @var string                    $moduleClass
 * @var string                    $blendMode
 * @var string                    $overlayRgba
 */

$esc       = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$headings  = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div'];
$sublayout = static function (string $suffix) use ($module, $params): string {
    $path = ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default') . '_' . $suffix);

    // An alternative layout without its own sublayout falls back to the default one, never to default.php itself.
    return basename($path) === 'default.php' ? ModuleHelper::getLayoutPath($module->module, 'default_' . $suffix) : $path;
};

// The core layout joomla.content.intro_image wraps the picture in figure.item-image: let it fill the card media.
Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle(
    '.wmarka-articles-slider figure.item-image{margin:0;height:100%;display:flex}'
    . '.wmarka-articles-slider figure.item-image img{width:100%;height:100%;object-fit:cover}'
    // uk-slider-container-offset widens the slider by 25px a side; uk-container pads phones by 15px only.
    . '@media (max-width:639px){.wmarka-articles-slider .uk-slider-container-offset{margin-left:-15px;margin-right:-15px;padding-left:15px;padding-right:15px}}'
    // Arrows placed outside the slider take about 60px a side: the slider leaves that room inside the module.
    . '@media (min-width:640px){.wmarka-articles-slider .wmarka-slider-out{padding-left:60px;padding-right:60px}}',
    ['name' => 'mod_wmarka_articles_slider.media']
);

$customHeader = trim((string) $params->get('custom_header', ''));
$headerLink   = trim((string) $params->get('header_link', ''));
$headerTag    = \in_array($params->get('header_tag', 'h2'), $headings, true) ? $params->get('header_tag', 'h2') : 'h2';
$headerClass  = trim($params->get('header_class', 'uk-heading-line') . ' ' . $params->get('header_size', '') . ($params->get('header_link_class', 1) ? ' uk-link-heading' : ''));

$titlesOnly    = $params->get('articles_layout', 'items') === 'titles';
$itemsPath     = $sublayout($titlesOnly ? 'titles' : 'items');
$colsClass     = 'uk-child-width-1-1 ' . $params->get('cols_m', 'uk-child-width-1-3@m');
$slidenavClass = (string) $params->get('slidenav_class', 'uk-slidenav-large');

// Cards keep the arrows inside the slider and show them on hover; title cards place them outside.
$containerClass = $titlesOnly ? 'uk-padding-small' : 'uk-slider-container-offset';
// Arrows outside the slider would stick out of a phone screen, so there they are hidden (swipe works).
$prevClass      = $titlesOnly ? 'uk-position-center-left-out uk-visible@s' : 'uk-position-center-left uk-hidden-hover';
$nextClass      = $titlesOnly ? 'uk-position-center-right-out uk-visible@s' : 'uk-position-center-right uk-hidden-hover';

$groups = $grouped ? $list : [$list];
?>
<div class="wmarka-articles-slider<?php echo $moduleClass !== '' ? ' ' . $esc($moduleClass) : ''; ?>">

    <?php if ($customHeader !== '') : ?>
        <div class="uk-margin-medium-bottom uk-text-center">
            <<?php echo $headerTag; ?> class="<?php echo $esc($headerClass); ?>">
                <?php if ($headerLink !== '') : ?>
                    <a href="<?php echo $esc($headerLink); ?>" class="uk-link-reset"><?php echo $esc($customHeader); ?></a>
                <?php else : ?>
                    <span><?php echo $esc($customHeader); ?></span>
                <?php endif; ?>
            </<?php echo $headerTag; ?>>
        </div>
    <?php endif; ?>

    <?php foreach ($groups as $groupTitle => $items) : ?>
        <?php if ($grouped) : ?>
            <h3 class="uk-h4 uk-margin-medium-top uk-margin-bottom wmarka-articles-group"><?php echo $esc($groupTitle === 'MOD_WMARKA_ARTICLES_SLIDER_UNTAGGED' ? Text::_($groupTitle) : $groupTitle); ?></h3>
        <?php endif; ?>

        <div<?php echo $titlesOnly && $params->get('slidenav', 1) ? ' class="wmarka-slider-out"' : ''; ?> uk-slider="finite: false; autoplay: true; pause-on-hover: true">
            <div class="uk-position-relative uk-visible-toggle" tabindex="-1">

                <div class="uk-slider-container <?php echo $containerClass; ?>">
                    <ul class="uk-slider-items uk-grid-match uk-grid-medium <?php echo $esc($colsClass); ?>" uk-grid>
                        <?php require $itemsPath; ?>
                    </ul>
                </div>

                <?php if ($params->get('slidenav', 1)) : ?>
                    <a class="<?php echo $prevClass; ?> uk-position-small <?php echo $esc($slidenavClass); ?>" href uk-slidenav-previous uk-slider-item="previous"></a>
                    <a class="<?php echo $nextClass; ?> uk-position-small <?php echo $esc($slidenavClass); ?>" href uk-slidenav-next uk-slider-item="next"></a>
                <?php endif; ?>
            </div>

            <?php if ($params->get('dotnav', 0)) : ?>
                <ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin-medium-top"></ul>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>
