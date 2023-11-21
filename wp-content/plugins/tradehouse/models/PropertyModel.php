<?php
class PropertyModel {

    function register_post_type() {
        $labels = array(
            'name'               => 'Housing Rents',
            'singular_name'      => 'Housing Rent',
            'menu_name'          => 'Housing Rents',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Housing Rent',
            'edit_item'          => 'Edit Housing Rent',
            'new_item'           => 'New Housing Rent',
            'view_item'          => 'View Housing Rent',
            'search_items'       => 'Search Housing Rents',
            'not_found'          => 'No housing rents found',
            'not_found_in_trash' => 'No housing rents found in Trash',
            'parent_item_colon'  => 'Parent Housing Rent:',
            'all_items'          => 'All Housing Rents',
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'housing_rent'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            // 'rest_base' => 'housing_rent',
        );
    
        // Register taxonomy for housing rent categories
        $taxonomy_labels = array(
            'name'              => 'Housing Rent Categories',
            'singular_name'     => 'Housing Rent Category',
            'search_items'      => 'Search Housing Rent Categories',
            'all_items'         => 'All Housing Rent Categories',
            'parent_item'       => 'Parent Housing Rent Category',
            'parent_item_colon' => 'Parent Housing Rent Category:',
            'edit_item'         => 'Edit Housing Rent Category',
            'update_item'       => 'Update Housing Rent Category',
            'add_new_item'      => 'Add New Housing Rent Category',
            'new_item_name'     => 'New Housing Rent Category',
            'menu_name'         => 'Categories',
        );
    
        $taxonomy_args = array(
            'hierarchical'      => true,
            'labels'            => $taxonomy_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'housing_rent_category'),
            'show_in_rest' => true,
    
        );
    
        register_taxonomy('housing_rent_category', 'housing_rent', $taxonomy_args);
    
        register_post_type('housing_rent', $args);
    }

    function custom_fields() {
        add_meta_box(
            'tradehouse_custom_fields',
            'Housing Rent Details',
            'tradehouse_render_custom_fields',
            'housing_rent',
            'normal',
            'default'
        );
    }

    function render_custom_fields($post) {
        $rent_price = get_post_meta($post->ID, '_rent_price', true);
        $payment_frequency = get_post_meta($post->ID, '_payment_frequency', true);
        $bedrooms = get_post_meta($post->ID, '_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_bathrooms', true);
        $parking_spaces = get_post_meta($post->ID, '_parking_spaces', true);
        $parking_type = get_post_meta($post->ID, '_parking_type', true);
        $availability_date = get_post_meta($post->ID, '_availability_date', true);
        $pet_allowed = get_post_meta($post->ID, '_pet_allowed', true);
        $smoker_allowed = get_post_meta($post->ID, '_smoker_allowed', true);
        ?>

        <label for="rent_price">Rent Price ($): </label>
        <input type="text" name="rent_price" value="<?php echo esc_attr($rent_price); ?>"><br>

        <label for="payment_frequency">Payment Frequency: </label>
        <select name="payment_frequency">
            <option value="week" <?php selected($payment_frequency, 'week'); ?>>Weekly</option>
            <option value="month" <?php selected($payment_frequency, 'month'); ?>>Monthly</option>
        </select><br>

        <label for="bedrooms">Number of Bedrooms: </label>
        <input type="text" name="bedrooms" value="<?php echo esc_attr($bedrooms); ?>"><br>

        <label for="bathrooms">Number of Bathrooms: </label>
        <input type="text" name="bathrooms" value="<?php echo esc_attr($bathrooms); ?>"><br>

        <label for="parking_spaces">Number of Parking Spaces: </label>
        <input type="text" name="parking_spaces" value="<?php echo esc_attr($parking_spaces); ?>"><br>

        <label for="parking_type">Parking Type: </label>
        <select name="parking_type">
            <option value="garage" <?php selected($parking_type, 'garage'); ?>>Garage</option>
            <option value="off_street" <?php selected($parking_type, 'off_street'); ?>>Off Street</option>
        </select><br>

        <label for="availability_date">Availability Date: </label>
        <input type="text" name="availability_date" value="<?php echo esc_attr($availability_date); ?>"><br>

        <label for="pet_allowed">Pet Allowed: </label>
        <input type="checkbox" name="pet_allowed" <?php checked($pet_allowed, 'on'); ?>><br>

        <label for="smoker_allowed">Smoker Allowed: </label>
        <input type="checkbox" name="smoker_allowed" <?php checked($smoker_allowed, 'on'); ?>>
        <?php
    }

    function save_custom_fields($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
        $fields = array(
            'rent_price',
            'payment_frequency',
            'bedrooms',
            'bathrooms',
            'parking_spaces',
            'parking_type',
            'availability_date',
            'pet_allowed',
            'smoker_allowed',
        );
    
        foreach ($fields as $field) {
            if (isset($post[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($post[$field]));
            } else {
                delete_post_meta($post_id, '_' . $field);
            }
        }
    }

    public function addToUserWatchlist($user_id, $property_id) {
        $watchlist = get_user_meta($user_id, 'watchlist', true);
        $watchlist = empty($watchlist) ? array() : $watchlist;
        $watchlist[] = $property_id;
        $watchlist = array_unique($watchlist);
        update_user_meta($user_id, 'watchlist', $watchlist);
    }

    public function getUserWatchlist($user_id) {
        $watchlist = get_user_meta($user_id, 'watchlist', true);
        $watchlist = empty($watchlist) ? array() : $watchlist;

        $properties = array();
        foreach ($watchlist as $property_id) {
            $property = get_post($property_id);
            if ($property && $property->post_type === 'housing_rent') {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    public function removeFromUserWatchlist($user_id, $property_id) {
        $watchlist = get_user_meta($user_id, 'watchlist', true);
        $watchlist = empty($watchlist) ? array() : $watchlist;
        $watchlist = array_diff($watchlist, array($property_id));
        update_user_meta($user_id, 'watchlist', $watchlist);
    }
}
?>
