cd<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');
if (file_exists(__DIR__.'/.env.local')) {
    $dotenv->load(__DIR__.'/.env.local');
}

$key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
if (!$key) {
    echo "No API Key found.";
    exit;
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $key;
$response = file_get_contents($url);
echo $response;
