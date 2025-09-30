<?php
/** @var array $data */
/** @var list<BailaYa\Dto\Instructor> $instructors */

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
              // First available locale text
              $first = reset($instr->bio);
              echo esc_html((string)$first);
            ?>
          </p>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
