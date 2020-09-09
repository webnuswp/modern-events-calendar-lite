<?php
require_once('CtcSplClassLoader.php');

// Load the Ctct namespace
$loader = new \Ctct\CtcSplClassLoader('Ctct', dirname(__DIR__));
$loader->register();
