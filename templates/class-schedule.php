<?php
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
