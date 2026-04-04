<?php
/** @var array $data */
/** @var list<BailaYa\Dto\PrivateLessonInstructor> $instructors */

$locale      = $data['locale'] ?? 'en';
$bookBaseUrl = rtrim($data['bookBaseUrl'] ?? '', '/') . '/';

$dayNames = [
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
];
?>
<div class="<?php echo esc_attr($data['className']); ?>">
  <?php foreach ($instructors as $instr): ?>
    <div class="<?php echo esc_attr($data['itemClassName']); ?>">

      <?php if (!empty($instr->image)): ?>
        <div class="<?php echo esc_attr($data['imageWrapperClassName']); ?>">
          <img
            src="<?php echo esc_url($instr->image); ?>"
            alt="<?php echo esc_attr(trim($instr->name . ' ' . ($instr->lastname ?? ''))); ?>"
            class="<?php echo esc_attr($data['imageClassName']); ?>"
          />
        </div>
      <?php endif; ?>

      <div class="<?php echo esc_attr($data['bodyClassName']); ?>">
        <h3 class="<?php echo esc_attr($data['nameClassName']); ?>">
          <?php
            $full = $instr->name . ($instr->lastname ? ' ' . $instr->lastname : '');
            echo esc_html($full);
          ?>
        </h3>

        <?php if (is_array($instr->bio) && !empty($instr->bio)): ?>
          <p class="<?php echo esc_attr($data['bioClassName']); ?>">
            <?php
              $bioText = $instr->bio[$locale] ?? reset($instr->bio);
              echo esc_html((string)$bioText);
            ?>
          </p>
        <?php endif; ?>

        <?php if (!empty($instr->availability)): ?>
          <div class="<?php echo esc_attr($data['sectionClassName']); ?>">
            <h4 class="<?php echo esc_attr($data['sectionHeadingClassName']); ?>">
              <?php echo esc_html($data['labels']['availability']); ?>
            </h4>
            <?php foreach ($instr->availability as $slot): ?>
              <div class="<?php echo esc_attr($data['slotClassName']); ?>">
                <?php
                  $dayLabel = $dayNames[$slot->dayOfWeek] ?? ('Day ' . $slot->dayOfWeek);
                  echo esc_html($dayLabel . ': ' . $slot->startTime . ' – ' . $slot->endTime);
                ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($instr->pricing)): ?>
          <div class="<?php echo esc_attr($data['sectionClassName']); ?>">
            <h4 class="<?php echo esc_attr($data['sectionHeadingClassName']); ?>">
              <?php echo esc_html($data['labels']['pricing']); ?>
            </h4>
            <?php foreach ($instr->pricing as $entry): ?>
              <div class="<?php echo esc_attr($data['pricingEntryClassName']); ?>">
                <?php
                  $formatted = number_format((float)$entry->price, 2);
                  echo esc_html($entry->durationMins . ' ' . $data['labels']['minutes'] . ' — ' . $entry->currency . ' ' . $formatted);
                ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a
          href="<?php echo esc_url($bookBaseUrl . $instr->id); ?>"
          class="<?php echo esc_attr($data['btnClassName']); ?>"
        >
          <?php echo esc_html($data['labels']['book']); ?>
        </a>
      </div>

    </div>
  <?php endforeach; ?>
</div>
