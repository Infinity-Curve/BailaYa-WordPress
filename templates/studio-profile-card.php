<?php
// Direct access to a template file must not execute anything.
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (BailaYaWP\Renderer's static render methods), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/** @var array $data */
/** @var BailaYa\Dto\StudioProfile $profile */

use BailaYaWP\Helpers;

$locale = $data['locale'] ?? 'en';
$descMap = is_array($profile->description) ? $profile->description : [];
$desc = '';
if ($locale && isset($descMap[$locale])) {
    $desc = (string)$descMap[$locale];
} elseif (!empty($descMap)) {
    $first = reset($descMap);
    $desc = (string)$first;
}

$addrLabel = (string)($data['labels']['addressLabel'] ?? '');
$bizLabel = (string)($data['labels']['businessHoursLabel'] ?? '');
if (!$bizLabel) {
    // Fall back to the site's own language rather than hand-rolling a locale
    // check — WordPress already knows which language to serve.
    $bizLabel = __('Business Hours', 'bailaya');
}

$showAllLocations = $data['showAllLocations'] ?? false;
$locations = $profile->locations ?? [];

// Studios can have several locations; show the primary one and fall back to the
// flat address for studios that predate locations.
$legacyAddress = $profile->address
    ? $profile->address . (!empty($profile->unit) ? ', ' . $profile->unit : '')
    : '';
$primaryAddress = Helpers::format_studio_location(Helpers::primary_location($locations));
if ($primaryAddress === '') {
    $primaryAddress = $legacyAddress;
}
?>
<div class="<?php echo esc_attr($data['className']); ?>">
  <div class="<?php echo esc_attr($data['itemClassName']); ?>">
    <?php if (!empty($profile->logo)): ?>
      <div class="<?php echo esc_attr($data['imageWrapperClassName']); ?>">
        <img
          src="<?php echo esc_url($profile->logo); ?>"
          alt="<?php echo esc_attr($profile->name . ' logo'); ?>"
          class="<?php echo esc_attr($data['imageClassName']); ?>"
        />
      </div>
    <?php endif; ?>
    <div class="<?php echo esc_attr($data['bodyClassName']); ?>">
      <h2 class="<?php echo esc_attr($data['nameClassName']); ?>">
        <?php echo esc_html($profile->name); ?>
      </h2>

      <?php if ($desc): ?>
        <p class="<?php echo esc_attr($data['descriptionClassName']); ?>">
          <?php echo esc_html($desc); ?>
        </p>
      <?php endif; ?>

      <?php if ($showAllLocations && $locations): ?>
        <?php foreach ($locations as $location): ?>
          <p class="<?php echo esc_attr($data['locationClassName']); ?>">
            <span class="<?php echo esc_attr($data['locationNameClassName']); ?>">
              <?php echo esc_html($location->name); ?>
            </span>
            <?php
              $locAddress = Helpers::format_studio_location($location);
              echo $locAddress !== '' ? esc_html(': ' . $locAddress) : '';
            ?>
          </p>
        <?php endforeach; ?>
      <?php elseif ($primaryAddress !== ''): ?>
        <p class="<?php echo esc_attr($data['labelClassName']); ?>">
          <?php echo esc_html($addrLabel ? "{$addrLabel}: " : ''); ?>
          <?php echo esc_html($primaryAddress); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($profile->businessHours)): ?>
        <p class="<?php echo esc_attr($data['labelClassName']); ?>">
          <?php echo esc_html($bizLabel . ': ' . $profile->businessHours); ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>
