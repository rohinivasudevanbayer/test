<?php

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

// Workaround for go/ and go.bayer.cnb redirections
if (isset($_SERVER['REQUEST_URI'])) {
  if ($_SERVER['REQUEST_URI'] === '/index.php/go.cnb/?intranet=1') {
    header("Location: https://go.bayer.com");
    exit();
  }
  preg_match('/\/index.php\/go.cnb\/(.*)\?intranet=1/m', $_SERVER['REQUEST_URI'], $matches);
  if (!empty($matches) && !empty($matches[1])) {
    header("Location: https://go.bayer.com/" . $matches[1] . "?intranet=1");
    exit();
  }
}


/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

/**
 * Display all errors when APPLICATION_ENV is development.
 */
if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
    );
}

// Set constants
include __DIR__ . '/../config/constants.config.php';

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';

if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

// Run the application!
Application::init($appConfig)->run();
