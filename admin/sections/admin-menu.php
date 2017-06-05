<?php
// get current screen
$screen = get_current_screen();

// set current page
if ($screen->id !== '') {

    if (strpos($screen->id, "_page_") !== false)
        $current_page = substr($screen->id, strpos($screen->id, "_page_") + 6);
    else
        $current_page = substr($screen->id, strpos($screen->id, "_category_") + 10);

} else
    $current_page = '';

?>
<!-- add nav main menu -->
<nav class="menu">
    <ul>
        <li <?php echo $current_page == 'pwapp-options' ? 'class="selected"' : '';?>>
            <a href="<?php echo add_query_arg(array('page'=>'pwapp-options'), network_admin_url('admin.php'));?>">App Themes</a>
        </li>
		<li <?php echo $current_page == 'pwapp-options-theme-settings' ? 'class="selected"' : '';?>>
            <a href="<?php echo add_query_arg(array('page'=>'pwapp-options-theme-settings'), network_admin_url('admin.php'));?>">Look & Feel</a>
        </li>
    </ul>
</nav>
