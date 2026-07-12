<?php
// Direct access to a template file must not execute anything.
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (BailaYaWP\Renderer's static render methods), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/** @var array $data */
/** @var list<BailaYa\Dto\StudioLocation> $locations */

use BailaYaWP\Helpers;

$hidePrimaryBadge = $data['hidePrimaryBadge'] ?? false;
$hideDirections   = $data['hideDirections'] ?? false;
?>
<div class="<?php echo esc_attr($data['className']); ?>">
  <?php foreach ($locations as $location): ?>
    <?php
      $address = Helpers::format_studio_location($location);
      $mapsUrl = Helpers::studio_location_maps_url($location);
    ?>
    <div class="<?php echo esc_attr($data['itemClassName']); ?>">
      <h3 class="<?php echo esc_attr($data['nameClassName']); ?>">
        <?php echo esc_html($location->name); ?>
        <?php if (!$hidePrimaryBadge && $location->isPrimary): ?>
          <span class="<?php echo esc_attr($data['badgeClassName']); ?>">
            <?php echo esc_html($data['labels']['primary']); ?>
          </span>
        <?php endif; ?>
      </h3>

      <?php if ($address !== ''): ?>
        <p class="<?php echo esc_attr($data['addressClassName']); ?>">
          <?php echo esc_html($address); ?>
        </p>
      <?php endif; ?>

      <?php if (!$hideDirections && $mapsUrl !== ''): ?>
        <a
          href="<?php echo esc_url($mapsUrl); ?>"
          class="<?php echo esc_attr($data['linkClassName']); ?>"
          target="_blank"
          rel="noopener noreferrer"
        >
          <?php echo esc_html($data['labels']['directions']); ?>
        </a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
