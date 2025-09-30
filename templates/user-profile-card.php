<?php
/** @var array $data */
/** @var BailaYa\Dto\UserProfile $profile */

$locale = $data['locale'] ?? 'en';

$bioMap = is_array($profile->bio) ? $profile->bio : [];
$selectedBio = '';
if ($locale && isset($bioMap[$locale])) {
    $selectedBio = (string)$bioMap[$locale];
} elseif (!empty($bioMap)) {
    $first = reset($bioMap);
    $selectedBio = (string)$first;
}

$occLabel = (string)($data['labels']['occupationLabel'] ?? '');
$expLabel = (string)($data['labels']['experienceLabel'] ?? '');
if (!$occLabel) {
    $occLabel = ($locale === 'es') ? 'Ocupación' : 'Occupation';
}
if (!$expLabel) {
    $expLabel = ($locale === 'es') ? 'Años de experiencia' : 'Years of experience';
}
?>
<div class="<?php echo esc_attr($data['className']); ?>">
  <div class="<?php echo esc_attr($data['itemClassName']); ?>">
    <?php if (!empty($profile->image)): ?>
      <div class="<?php echo esc_attr($data['imageWrapperClassName']); ?>">
        <img
          src="<?php echo esc_url($profile->image); ?>"
          alt="<?php echo esc_attr(trim($profile->name . ($profile->lastname ? ' ' . $profile->lastname : ''))); ?>"
          class="<?php echo esc_attr($data['imageClassName']); ?>"
        />
      </div>
    <?php endif; ?>

    <div class="<?php echo esc_attr($data['bodyClassName']); ?>">
      <h3 class="<?php echo esc_attr($data['nameClassName']); ?>">
        <?php echo esc_html($profile->name . ($profile->lastname ? ' ' . $profile->lastname : '')); ?>
      </h3>

      <?php if ($selectedBio): ?>
        <p class="<?php echo esc_attr($data['bioClassName']); ?>">
          <?php echo esc_html($selectedBio); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($profile->occupation)): ?>
        <p class="<?php echo esc_attr($data['labelClassName']); ?>">
          <?php echo esc_html($occLabel . ': ' . $profile->occupation); ?>
        </p>
      <?php endif; ?>

      <?php if ($profile->yearsOfExperience !== null): ?>
        <p class="<?php echo esc_attr($data['labelClassName']); ?>">
          <?php echo esc_html($expLabel . ': ' . (string)$profile->yearsOfExperience); ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>
