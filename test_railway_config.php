<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "STEP 1<br>";

require __DIR__ . '/config/db.php';
require __DIR__ . '/functions/ai_helper.php';

echo "STEP 2<br>";

$apiKey = getenv('GEMINI_API_KEY');
var_dump($apiKey);

echo "<br>STEP 3<br>";

$testPrompt = "Halo";

$result = callGeminiAPI($testPrompt);

echo "<pre>";
var_dump($result);
echo "</pre>";

echo "STEP 4<br>";