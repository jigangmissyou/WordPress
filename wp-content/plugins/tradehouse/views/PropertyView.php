<?php
class PropertyView {
    public function renderSuccessResponse($message) {
        return array('success' => true, 'message' => $message);
    }

    public function renderErrorResponse($errorCode, $errorMessage, $statusCode) {
        return new WP_Error($errorCode, $errorMessage, array('status' => $statusCode));
    }
}
?>
