<script type="text/javascript">
    if (window.PWAPPJSInterface && window.PWAPPJSInterface != null){
        jQuery(document).ready(function(){

            PWAPPJSInterface.localpath = "<?php echo plugins_url()."/".PWAPP_DOMAIN."/"; ?>";
            PWAPPJSInterface.init();
        });
    }
</script>

<?php
	$upgrade_content = PWAPP_Admin::more_updates();

	// get themes from the upgrade json
	$arr_pro_themes = PWAPP_Admin::upgrade_pro_themes($upgrade_content);
?>

<div id="pwapp-admin">
	<div class="spacer-60"></div>
    <!-- set title -->
    <h1><?php echo PWAPP_PLUGIN_NAME.' '.PWAPP_VERSION;?></h1>
	<div class="spacer-20"></div>
	<div class="app-themes">
        <div class="left-side">
			<!-- add nav menu -->
            <?php include_once(PWAPP_PLUGIN_PATH.'admin/sections/admin-menu.php'); ?>

            <div class="spacer-0"></div>
            <div class="details theming">
                <h2 class="title">Mobile App Theme</h2>
				<div class="spacer-30"></div>
				<p>The Mosaic mobile app theme is the window-display of themes. You can instantly see several categories and choose which ones are of interest and focus on those. Depending on the number of displayed categories, the boxes will resize to fit all available space.</p>
				<div class="spacer-30"></div>
                <div class="themes">
					<div class="theme single" data-theme="2">
						<div class="corner relative <?php echo 'active';?>">
							<div class="indicator"></div>
						</div>
						<div class="image" style="background:url(<?php echo plugins_url()."/".PWAPP_DOMAIN;?>/admin/images/theme-2.jpg);">
							<div class="relative">
								<div class="overlay">
									<div class="spacer-100"></div>
									<div class="spacer-10"></div>
									<div class="text-preview">Enabled</div>
								</div>
							</div>
						</div>
						<div class="name">Mosaic</div>
					</div>
                </div>
            </div>
            <div class="spacer-10"></div>
			<?php if (count($arr_pro_themes) > 0):?>

				<div class="details theming">
					<div class="ribbon relative">
						<div class="starred"></div>
					</div>

					<?php if (isset($upgrade_content['premium']['themes']['title'])):?>
						<h2 class="title"><?php echo $upgrade_content['premium']['themes']['title']; ?></h2>
					<?php else: ?>
						<h2 class="title">Premium Mobile App Themes</h2>
					<?php endif;?>

					<div class="spacer-30"></div>
					<div class="themes">
						<?php
							foreach ($arr_pro_themes as $theme){
								require(PWAPP_PLUGIN_PATH.'admin/sections/theme-box.php');
							}
						?>
					</div>
				</div>
			<?php endif;?>

        </div>

        <div class="right-side">
            <!-- add feedback form -->
            <?php include_once(PWAPP_PLUGIN_PATH.'admin/sections/feedback.php'); ?>
        </div>
	</div>
</div>


