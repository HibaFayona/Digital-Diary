<?php
session_start();
if(!isset($_SESSION['user_id'])) { http_response_code(403); exit; }

$allowedFonts = [
    "'Lato', sans-serif",
    "'Playfair Display', serif",
    "'Merriweather', serif",
    "'Dancing Script', cursive",
    "'Source Code Pro', monospace",
    "Georgia, serif",
    "Impact, sans-serif",
];
$allowedThemes = ['parchment','midnight','forest','rose','ocean'];

if(isset($_GET['font']) && in_array($_GET['font'], $allowedFonts)){
    $_SESSION['font'] = $_GET['font'];
}
if(isset($_GET['theme']) && in_array($_GET['theme'], $allowedThemes)){
    $_SESSION['theme'] = $_GET['theme'];
}

echo 'ok';