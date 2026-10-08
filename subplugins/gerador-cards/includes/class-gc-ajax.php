<?php
if (!defined('ABSPATH')) exit;

class CG_AJAX {

    public function __construct() {
        add_action('wp_ajax_cg_save_card_data', [ $this, 'save_card_handler' ]);
        add_action('wp_ajax_nopriv_cg_save_card_data', [ $this, 'save_card_handler' ]);
    }

    public function save_card_handler() {
        $card_id      = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        $title        = sanitize_text_field($_POST['title']);
        $body_content = wp_kses_post($_POST['f4']);

        if ($card_id === 0) {
            $card_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $body_content,
                'post_status'  => 'publish',
                'post_type'    => 'saved_cards'
            ]);
        } else {
            wp_update_post([
                'ID'           => $card_id,
                'post_title'   => $title,
                'post_content' => $body_content
            ]);
        }

        if ($card_id && !is_wp_error($card_id)) {
            update_post_meta($card_id, '_cg_field_1', sanitize_text_field($_POST['f1']));
            update_post_meta($card_id, '_cg_field_2', sanitize_text_field($_POST['f2']));
            update_post_meta($card_id, '_cg_field_3', sanitize_text_field($_POST['f3']));
            update_post_meta($card_id, '_cg_field_4', $body_content);

            update_post_meta($card_id, '_cg_field_5_1_raw', sanitize_text_field($_POST['date_raw']));
            update_post_meta($card_id, '_cg_field_5_1_time_raw', sanitize_text_field($_POST['time_raw']));
            update_post_meta($card_id, '_cg_field_5_1', wp_kses_post($_POST['f5_1_compiled']));
            update_post_meta($card_id, '_cg_field_5_1_time', sanitize_text_field($_POST['f5_1_time_compiled']));
            update_post_meta($card_id, '_cg_field_5_2', sanitize_text_field($_POST['f5_2']));
            update_post_meta($card_id, '_cg_field_5_3', sanitize_text_field($_POST['f5_3']));
            update_post_meta($card_id, '_cg_bg_position_y', sanitize_text_field($_POST['bg_position_y']));
            update_post_meta($card_id, '_cg_bg_zoom', sanitize_text_field($_POST['bg_zoom']));

            if (isset($_POST['bg_img']) && !empty($_POST['bg_img'])) {
                $img_data = $_POST['bg_img'];
                if (strpos($img_data, 'data:image') === 0) {
                    @list(, $img_data) = explode(';', $img_data);
                    @list(, $img_data) = explode(',', $img_data);

                    $decoded_image = base64_decode($img_data);
                    $filename = 'card-bg-' . time() . '.png';
                    $wp_upload_dir = wp_upload_dir();
                    $file_path = $wp_upload_dir['path'] . '/' . $filename;

                    file_put_contents($file_path, $decoded_image);
                    $final_bg_url = $wp_upload_dir['url'] . '/' . $filename;

                    update_post_meta($card_id, '_cg_bg_img', esc_url_raw($final_bg_url));
                } else {
                    update_post_meta($card_id, '_cg_bg_img', esc_url_raw($img_data));
                }
            }

            if (isset($_POST['mask_img'])) {
                update_post_meta($card_id, '_cg_mask_img', esc_url_raw($_POST['mask_img']));
            }

            wp_send_json_success([ 'card_id' => $card_id ]);
        } else {
            wp_send_json_error();
        }
    }
}