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
        add_action('save_post', array($this->model, 'save_custom_fields'));
        add_action('rest_api_init', array($this, 'tradehouse_watchlist'));
    }

    public function tradehouse_watchlist() {
        // Add to watchlist
        register_rest_route('tradehouse/v1', '/add-to-watchlist/', array(
            'methods' => 'POST',
            'callback' => array($this, 'handleWatchlistSubmission')
        ));
        // Get user watchlist
        register_rest_route('tradehouse/v1', '/get-user-watchlist/', array(
            'methods' => 'GET',
            'callback' => array($this, 'getUserWatchlist'),
        ));
        // Cancle watchlist
        register_rest_route('tradehouse/v1', '/cancle-watchlist/', array(
            'methods' => 'POST',
            'callback' => array($this, 'cancelWatchlist'),
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
}
?>
