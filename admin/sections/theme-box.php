<?php

if (isset($theme)):

	$is_selected = isset($theme['selected']) && $theme['selected'] == 1;

	$is_premium = isset($theme['demo']) || isset($theme['details']);
?>
	<div class="theme <?php echo $is_premium ? 'premium' : '';?>">
		<div class="corner relative <?php echo $is_selected ? 'active' : '';?>">
			<div class="indicator"></div>
		</div>
		<div class="image" style="background:url(<?php echo isset($theme['icon']) ? esc_attr( $theme['icon'] ) : '' ?>);">
			<div class="relative">
				<div class="overlay">
					<div class="spacer-100"></div>

					<?php if (isset($theme['id']) && !$is_premium): ?>

						<div class="actions">
							<div class="select pwapp_themes_select" data-theme="<?php echo esc_attr($theme['id']);?>" style="display: <?php echo $is_selected ? 'none' : 'block';?>"></div>
						</div>
						<div class="spacer-10"></div>
						<div class="text-select"><?php echo $is_selected ? 'Enabled' : 'Activate';?></div>

					<?php endif;?>

					<?php if ($is_premium): ?>

						<div class="actions">
							<a href="<?php echo isset($theme['demo']) ?  esc_attr($theme['demo']['link']) : esc_attr($theme['details']['link']) ?>"
								target="_blank"
								class="preview pwapp_themes_preview">
							</a>
						</div>
						<div class="spacer-10"></div>
						<div class="text-preview">Preview</div>

					<?php endif;?>
				</div>
			</div>
		</div>
		<div class="name">
			<?php echo isset($theme['id']) && $theme['id'] == 2 ? '&#x1F680;' : '';?>
			<?php echo isset($theme['title']) ? esc_attr($theme['title']) : '';?>
		</div>
		<?php
			if ($is_premium && isset($theme['details']['link']) && isset($theme['details']['text'])):
		?>
			<div class="content">
				<a href="<?php echo esc_attr($theme['details']['link']) ?>" class="btn turquoise smaller" target="_blank">
					<?php echo isset($theme['details']['text']) ? $theme['details']['text'] : '';?>
				</a>
			</div>
		<?php endif; ?>
	</div>
<?php endif;?>
