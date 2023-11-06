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

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!$_ENV['OPENAI_API_KEY']) {
    // TODO: Throw exception
}

