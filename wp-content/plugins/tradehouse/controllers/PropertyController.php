<?php
class PropertyController {
    private $model;
    private $view;

    public function __construct($model, $view) {
        $this->model = $model;
        $this->view = $view;
    }

    public function init() {
        // add_action('wp_enqueue_scripts', array($this->view, 'enqueue_styles'));
        add_action('init', array($this->model, 'register_post_type'));
        add_action('add_meta_boxes', array($this->model, 'custom_fields'));
        add_action('save_post', array($this->model, 'save_custom_fields'), 10, 2); // Increase priority and pass 2 arguments
        add_action('rest_api_init', array($this, 'registerRestRoutes'));
        add_filter( 'jwt_auth_whitelist', function ( $endpoints ) {
            $tradehouse_endpoints = array(
                '/wp-json/tradehouse/v1/get-renting-house',
                '/wp-json/tradehouse/v1/categories/',
            );
            return array_unique( array_merge( $endpoints, $tradehouse_endpoints ) );
        } );
    }
    
    public function registerRestRoutes() {
        // Watchlist routes
        $this->tradehouse_watchlist();
        // News routes
        register_rest_route('tradehouse/v1', '/get-news/', array(
            'methods' => 'GET',
            'callback' => array($this, 'getNews'),
            'permission_callback' => '__return_true', 

        ));

        // Renting house routes
        register_rest_route('tradehouse/v1', '/get-renting-house/', array(
            'methods' => 'GET',
            'callback' => array($this, 'getRentingHouse'),
            'permission_callback' => '__return_true',

        ));

        register_rest_route('tradehouse/v1', '/get-picture/', array(
            'methods' => 'GET',
            'callback' => array($this, 'getPicturesFromCategory'),
            'permission_callback' => '__return_true', // Add this line

        ));

        register_rest_route('tradehouse/v1', '/categories/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_category_hierarchy'),
            'permission_callback' => '__return_true', // Add this line

        ));

       
    }

    public function tradehouse_watchlist() {
        // Add to watchlist
        register_rest_route('tradehouse/v1', '/add-to-watchlist/', array(
            'methods' => 'POST',
            'callback' => array($this, 'handleWatchlistSubmission'),
            'permission_callback' => '__return_true', // Add this line

        ));
        // Get user watchlist
        register_rest_route('tradehouse/v1', '/get-user-watchlist/', array(
            'methods' => 'GET',
            'callback' => array($this, 'getUserWatchlist'),
            'permission_callback' => '__return_true', // Add this line

        ));
        // Cancle watchlist
        register_rest_route('tradehouse/v1', '/cancle-watchlist/', array(
            'methods' => 'POST',
            'callback' => array($this, 'cancelWatchlist'),
            'permission_callback' => '__return_true', // Add this line

        ));
    }

    public function handleWatchlistSubmission($data) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return $this->view->renderErrorResponse('authentication_error', 'User not logged in.', 401);
        }

        $property_id = sanitize_text_field($data['property_id']);
        $this->model->addToUserWatchlist($user_id, $property_id);

        return $this->view->renderSuccessResponse('Property added to watchlist.');
    }

    public function getUserWatchlist() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return $this->view->renderErrorResponse('authentication_error', 'User not logged in.', 401);
        }

        $watchlist = $this->model->getUserWatchlist($user_id);

        return $watchlist;
    }

    public function cancelWatchlist($data) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return $this->view->renderErrorResponse('authentication_error', 'User not logged in.', 401);
        }

        $property_id = sanitize_text_field($data['property_id']);
        $this->model->removeFromUserWatchlist($user_id, $property_id);

        return $this->view->renderSuccessResponse('Property removed from watchlist.');
    }

    public function getNews($data) {
        // Implement logic to retrieve news from the model
        return $this->model->getNews($data['category_id']);
    }

    public function getPicturesFromCategory($data) {
        // Implement logic to retrieve featured picture from the model
        return $this->model->getPicturesFromCategory($data['category_slug']);
    }

    public function getRentingHouse() {
        // Implement logic to retrieve featured picture from the model
        return $this->model->getHousingRentPosts();
    }

    function get_category_hierarchy($data) {
        $parent_id = isset($data['id']) ? intval($data['id']) : 0;
    
        $categories = get_categories(array(
            'parent' => $parent_id,
            'hide_empty' => false, // Include categories with no posts
        ));
    
        $result = array();
    
        foreach ($categories as $category) {
            $result[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'children' => $this->get_category_hierarchy(array('id' => $category->term_id)), // Recursive call
            );
        }
    
        return $result;
    }
}
?>
