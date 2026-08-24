<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    // Charge le fichier routes.yaml
    $routes->import('../config/routes.yaml');

    // Charge tous les fichiers YAML dans config/routes/
    $routes->import('../config/routes/*.yaml');
};

