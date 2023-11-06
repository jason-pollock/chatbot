<?php

/**
 * Plugin Name: Article Chatbot
 * Description: Discuss article content with OpenAI's GPT-3.5 chatbot.
 * Version: 0.1.0
 * Author: Jason Pollock
 * Author URI: https://coolguy.org
 * License: GPL
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: chatbot-plugin
 */

use CoolGuy\WordPress\Plugin\ChatBot\ChatApi;

if (!defined('ABSPATH')) {
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!$_ENV['OPENAI_API_KEY']) {
    // TODO: Throw exception
    return;
}

do_action('chatbot_plugin_loaded');

add_action('init', function () {
    if (!session_id()) {
        session_start();
    }
});

add_filter('the_content', function ($content) {
    return $content . "<div id='chatgpt-app'></div>";
});

$chat = new ChatApi();
$chat->createRoute('init', 'POST', [$chat, 'initializeChat'], '__return_true');
$chat->createRoute('message', 'POST', [$chat, 'handleMessage'], '__return_true');


