<?php
if (class_exists('PWAPP_Core')):

    if ( ! class_exists( 'PWAPP_Export' ) ) {
        require_once(PWAPP_PLUGIN_PATH.'frontend/export/class-export.php');
    }

    $pwapp_export = new PWAPP_Export();
    $pwapp_texts_json = $pwapp_export->load_language(get_locale(), 'list');

    $pwapp_footer_text = 'Switch to mobile version';
    if ($pwapp_texts_json !== false && isset($pwapp_texts_json['APP_TEXTS']['LINKS']) && isset($pwapp_texts_json['APP_TEXTS']['LINKS']['VISIT_APP'])){
        $pwapp_footer_text = $pwapp_texts_json['APP_TEXTS']['LINKS']['VISIT_APP'];
}
    ?>
    <div id="show-mobile" style="width:100%; text-align: center;">
        <a href="<?php echo home_url(); echo parse_url(home_url(), PHP_URL_QUERY) ? '&' : '?'; echo PWAPP_Options::$prefix; ?>theme_mode=mobile" title="<?php echo $pwapp_footer_text;?>"><?php echo $pwapp_footer_text;?></a>
    </div>
<?php endif;?>
