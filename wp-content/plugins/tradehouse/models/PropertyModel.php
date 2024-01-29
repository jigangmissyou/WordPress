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
        register_rest_field('housing_rent', 'custom_fields', array(
            'get_callback' => array($this, 'get_custom_fields'),
            'update_callback' => null,
            'schema' => null,
        ));
        // $this->custom_fields();
    }

    function get_custom_fields($object, $field_name, $request) {
        $post_id = $object['id'];
    
        $custom_fields = array(
            'rent_price'         => get_post_meta($post_id, '_rent_price', true),
            'payment_frequency'  => get_post_meta($post_id, '_payment_frequency', true),
            'bedrooms'           => get_post_meta($post_id, '_bedrooms', true),
            'bathrooms'          => get_post_meta($post_id, '_bathrooms', true),
            'parking_spaces'     => get_post_meta($post_id, '_parking_spaces', true),
            'parking_type'       => get_post_meta($post_id, '_parking_type', true),
            'availability_date'  => get_post_meta($post_id, '_availability_date', true),
            'pet_allowed'        => get_post_meta($post_id, '_pet_allowed', true),
            'smoker_allowed'     => get_post_meta($post_id, '_smoker_allowed', true),
        );
    
        return $custom_fields;
    }

    function custom_fields() {
        add_meta_box(
            'tradehouse_custom_fields',
            'Housing Rent Details',
            array($this, 'tradehouse_render_custom_fields'),
            'housing_rent',
            'normal',
            'default'
        );
    }

    function tradehouse_render_custom_fields($post) {
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
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type != 'housing_rent') return;
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
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
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

    public function getNewsByCategory($category_id) {
        // Implement your logic to retrieve news data for a specific category
        $args = array(
            'post_type'      => 'news',
            'posts_per_page' => -1, // Retrieve all news posts
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'category', // Change this to your actual taxonomy name
                    'field'    => 'id',
                    'terms'    => $category_id,
                ),
            ),
        );

        $news_query = new WP_Query($args);

        $news = array();

        if ($news_query->have_posts()) {
            while ($news_query->have_posts()) {
                $news_query->the_post();

                // Customize this part based on your actual data structure
                $news[] = array(
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'date'    => get_the_date(),
                    // Add more fields as needed
                );
            }

            wp_reset_postdata();
        }

        return $news;
    }

    public function getPicturesFromCategory($category_slug) {
        $args = array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'media_category' => $category_slug, 
        );
    
        $attachments = get_posts($args);
    
        $picture_urls = array();
        foreach ($attachments as $attachment) {
            $picture_urls[] = wp_get_attachment_url($attachment->ID);
        }
    
        return $picture_urls;
    }

    public function getHousingRentPosts($data = []) {
        $args = array(
            'post_type'      => 'housing_rent',
            'posts_per_page' => -1,
            // 'meta_query'     => array(
            //     'relation' => 'AND',
            //     // array(
                //     'key'     => '_bedrooms',
                //     'value'   => array($minBedrooms, $maxBedrooms),
                //     'type'    => 'NUMERIC',
                //     'compare' => 'BETWEEN',
                // ),
                // array(
                //     'key'     => '_bathrooms',
                //     'value'   => array($minBathrooms, $maxBathrooms),
                //     'type'    => 'NUMERIC',
                //     'compare' => 'BETWEEN',
                // ),
                // 可以添加更多的条件...
            // ),
            // 'tax_query'      => array(
            //     array(
            //         'taxonomy' => 'region_taxonomy', // 替换为你的地理区域的自定义分类法
            //         'field'    => 'slug',
            //         'terms'    => $region,
            //     ),
            // ),
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        );

        // 添加价格条件
        if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
            $minPrice = (int) $_GET['min_price'];
            $maxPrice = (int) $_GET['max_price'];
            $args['meta_query'][] = array(
                'key'     => '_rent_price',
                'value'   => array($minPrice, $maxPrice),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            );
        }

    
        $query = new WP_Query($args);
    
        $posts = array();
    
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_data = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                    'link'  => get_the_permalink(),
                    'houseImg' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
                    'price' => get_post_meta(get_the_ID(), '_rent_price', true),
                    'bedrooms' => get_post_meta(get_the_ID(), '_bedrooms', true),
                    'parkings' => get_post_meta(get_the_ID(), '_parking_spaces', true),
                    'parking_type' => get_post_meta(get_the_ID(), '_parking_type', true),
                    'availability_date' => get_post_meta(get_the_ID(), '_availability_date', true),
                    'pet_allowed' => get_post_meta(get_the_ID(), '_pet_allowed', true),
                    'smoker_allowed' => get_post_meta(get_the_ID(), '_smoker_allowed', true),
                    'created_at' => get_the_date(),
                    
                    // 添加其他字段...
                );
    
                $posts[] = $post_data;
            }
            wp_reset_postdata();
        }
    
        return rest_ensure_response($posts);
    }
    
    
}
?>
