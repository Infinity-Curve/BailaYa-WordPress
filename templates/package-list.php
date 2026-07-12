<?php
// Direct access to a template file must not execute anything.
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (BailaYaWP\Renderer's static render methods), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/** @var array $data */
/** @var list<BailaYa\Dto\StudioPackage> $packages */

$locale       = $data['locale'] ?? 'en';
$hideValidity = $data['hideValidity'] ?? false;
$buyBaseUrl   = rtrim($data['buyBaseUrl'] ?? '', '/') . '/';
?>
<div class="<?php echo esc_attr($data['className']); ?>">
  <?php foreach ($packages as $pkg): ?>
    <div class="<?php echo esc_attr($data['itemClassName']); ?>">
      <div class="bailaya-pkg-body">
        <h3 class="<?php echo esc_attr($data['nameClassName']); ?>">
          <?php echo esc_html($pkg->name); ?>
        </h3>

        <?php if (!empty($pkg->description)): ?>
          <p class="<?php echo esc_attr($data['descriptionClassName']); ?>">
            <?php echo esc_html($pkg->description); ?>
          </p>
        <?php endif; ?>

        <p class="<?php echo esc_attr($data['metaClassName']); ?>">
          <?php
            echo esc_html($pkg->sessions . ' ' . $data['labels']['classes']);
            if (!$hideValidity) {
                $months     = $pkg->durationMonths;
                $monthLabel = $months === 1 ? $data['labels']['month'] : $data['labels']['months'];
                echo esc_html(' · ' . $data['labels']['validFor'] . ' ' . $months . ' ' . $monthLabel);
            }
          ?>
        </p>

        <?php if (!empty($pkg->allowedDanceTypes)): ?>
          <p class="<?php echo esc_attr($data['typesClassName']); ?>">
            <?php echo esc_html(implode(' · ', $pkg->allowedDanceTypes)); ?>
          </p>
        <?php endif; ?>
      </div>

      <div class="bailaya-pkg-footer">
        <span class="<?php echo esc_attr($data['priceClassName']); ?>">
          <?php
            $formatted = number_format((float)$pkg->price, 2, '.', ',');
            echo esc_html($pkg->currency . ' ' . $formatted);
          ?>
        </span>
        <a
          href="<?php echo esc_url($buyBaseUrl . $pkg->id); ?>"
          class="<?php echo esc_attr($data['btnClassName']); ?>"
        >
          <?php echo esc_html($data['labels']['buy']); ?>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
