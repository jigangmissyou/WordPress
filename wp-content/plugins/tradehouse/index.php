<?php
/*
Plugin Name: TradeHouse
Description: Apply Trademe-style to housing rent posts.
Version: 1.0
Author: Jigang Guo
*/

require_once(__DIR__.'/models/PropertyModel.php');
require_once(__DIR__.'/views/PropertyView.php');
require_once(__DIR__.'/controllers/PropertyController.php');

$model = new PropertyModel();
$view = new PropertyView();
$controller = new PropertyController($model, $view);
$controller->init();

?>
