<?php
// Direct access to a template file must not execute anything.
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (BailaYaWP\Renderer's static render methods), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/** @var array $data */
/** @var list<BailaYa\Dto\StudioClass> $classes */

$locale = $data['locale'] ?? 'en';
$fmtDay = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, 'EEEE');
$fmtDate = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, 'MMM d');
?>
<ul class="<?php echo esc_attr($data['className']); ?>">
  <?php foreach ($classes as $cls): ?>
    <?php
      $dayName = $fmtDay->format($cls->date);
      $shortDate = $fmtDate->format($cls->date);
    ?>
    <li class="<?php echo esc_attr($data['itemClassName']); ?>">
      <div class="<?php echo esc_attr($data['nameClassName']); ?>">
        <?php echo esc_html($cls->name); ?>
        <span class="bailaya-level">(<?php echo esc_html($cls->level); ?>)</span>
      </div>
      <div class="<?php echo esc_attr($data['detailsClassName']); ?>">
        <?php echo esc_html(sprintf('%s, %s — %s–%s', $dayName, $shortDate, $cls->startTime, $cls->endTime)); ?>
      </div>
      <?php
        $loc = $cls->location ?? null;
        $locationText = '';
        if (!($data['hideLocation'] ?? false) && $loc) {
          if ($loc->isVirtual) {
            $locationText = $loc->virtualPlatform
              ? sprintf('%s · %s', $data['labels']['online'] ?? 'Online', $loc->virtualPlatform)
              : ($data['labels']['online'] ?? 'Online');
          } else {
            $locationText = implode(' · ', array_filter([$loc->roomName, $loc->address]));
          }
        }
      ?>
      <?php if ($locationText !== ''): ?>
        <div class="<?php echo esc_attr($data['locationClassName']); ?>">
          <?php echo esc_html($locationText); ?>
          <?php if ($loc && $loc->mapsUrl): ?>
            <a href="<?php echo esc_url($loc->mapsUrl); ?>" target="_blank" rel="noopener noreferrer" class="<?php echo esc_attr($data['mapLinkClassName']); ?>">
              <?php echo esc_html($data['labels']['viewOnMap'] ?? 'View on map'); ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($cls->instructor): ?>
        <div class="<?php echo esc_attr($data['instructorClassName']); ?>">
          <?php echo esc_html($data['labels']['instructor'] ?? 'Instructor:'); ?>
          <strong>
            <?php
              $full = $cls->instructor->name . ($cls->instructor->lastname ? ' ' . $cls->instructor->lastname : '');
              echo esc_html($full);
            ?>
          </strong>
        </div>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
