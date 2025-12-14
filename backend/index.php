<?php
require 'vendor/autoload.php'; //run autoloader
require 'rest/services/UserService.php'; 
require 'rest/services/PlaylistService.php';
require 'rest/services/TrackService.php';
require 'rest/services/TagService.php';
require 'rest/services/AuthService.php';

// --- SERVICE REGISTRATION ---
Flight::register('auth_service', "AuthService");
Flight::register('playlist_service', "PlaylistService");
Flight::register('track_service', "TrackService");
Flight::register('tag_service', "TagService");
Flight::register('userService', "UserService");

// 1. INCLUDE ROUTES HERE (Before Flight::start())
require_once __DIR__ .'/rest/routes/AuthRoutes.php';
require_once __DIR__ .'/rest/routes/PlaylistRoutes.php';
require_once __DIR__ .'/rest/routes/TrackRoutes.php';
require_once __DIR__ .'/rest/routes/TagRoutes.php';
require_once __DIR__ .'/rest/routes/UserRoutes.php';

// 2. DEFINE BASE ROUTE
Flight::route('/', function(){ //define route and define function to handle request
 echo "Hello world!";
});

// 3. START ROUTER
Flight::start(); //start FlightPHP
?>