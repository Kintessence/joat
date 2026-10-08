<?php

function checa_login($atts, $content = null) {
    if (is_user_logged_in()) {
        return do_shortcode($content);
    } else {
		ob_start();
?>
<div class="login-form">
	<?php wp_login_form(); ?>
</div>
<?php
		return ob_get_clean();
    }
}

add_shortcode('checa_login', 'checa_login');