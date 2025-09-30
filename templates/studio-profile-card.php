<?php
/** @var array $data */
/** @var BailaYa\Dto\StudioProfile $profile */

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
    $bizLabel = ($locale === 'es') ? 'Horario Comercial' : 'Business Hours';
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

      <?php if (!empty($profile->address)): ?>
        <p class="<?php echo esc_attr($data['labelClassName']); ?>">
          <?php echo esc_html($addrLabel ? "{$addrLabel}: " : ''); ?>
          <?php
            $addr = $profile->address . (!empty($profile->unit) ? ', ' . $profile->unit : '');
            echo esc_html($addr);
          ?>
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
