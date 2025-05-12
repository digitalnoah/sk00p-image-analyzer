<?php
// Shared infrastructure bootstrapping for image-analyzer
require_once __DIR__ . '/../../sk00p-root-tools/autoload.php';
Sk00p\Session::start();

// Optionally you can expose user data to the front-end later here.

// Serve the existing static markup
readfile(__DIR__ . '/index.html');