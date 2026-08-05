<?php

declare(strict_types=1);

use AxaZara\CS\Config;
use AxaZara\CS\Finder;

// Routes for analysis with `php-cs-fixer`
$routes = ['./src', './tests'];

return Config::createWithFinder(Finder::createWithRoutes($routes));
