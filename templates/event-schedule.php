<?php
// Direct access to a template file must not execute anything.
if (!defined('ABSPATH')) exit;
// This file is included from inside a function (BailaYaWP\Renderer's static render methods), so the variables
// below are function-scoped, not global. PHPCS analyses it standalone and cannot
// see that, hence the disable.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/** @var array $data */
/** @var list<BailaYa\Dto\StudioEvent> $events */

$locale = $data['locale'] ?? 'en';
$fmtDay = new IntlDateFormatter($locale, IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, 'EEEE');
$fmtDate = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, 'MMM d');
?>
<ul class="<?php echo esc_attr($data['className']); ?>">
  <?php foreach ($events as $event): ?>
    <?php
      $dayName = $fmtDay->format($event->date);
      $shortDate = $fmtDate->format($event->date);
    ?>
    <li class="<?php echo esc_attr($data['itemClassName']); ?>">
      <div class="<?php echo esc_attr($data['nameClassName']); ?>">
        <?php echo esc_html($event->name); ?>
        <span class="bailaya-level">(<?php echo esc_html($event->level); ?>)</span>
      </div>
      <div class="<?php echo esc_attr($data['detailsClassName']); ?>">
        <?php echo esc_html(sprintf('%s, %s — %s–%s', $dayName, $shortDate, $event->startTime, $event->endTime)); ?>
      </div>
      <?php
        $loc = $event->location ?? null;
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
      <?php if ($event->host): ?>
        <div class="<?php echo esc_attr($data['hostClassName']); ?>">
          <?php echo esc_html($data['labels']['host'] ?? 'Host:'); ?>
          <strong>
            <?php
              $full = $event->host->name . ($event->host->lastname ? ' ' . $event->host->lastname : '');
              echo esc_html($full);
            ?>
          </strong>
        </div>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
