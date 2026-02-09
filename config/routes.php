<?php
// config/routes.php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    // Main routes
    $routes->scope(
        '/',
        function (RouteBuilder $builder): void {
            // Standard routes for your main application
            $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
            $builder->connect('/pages/*', 'Pages::display');

            // Routes spécifiques pour DossiersController et UsersController
            // Ces routes API ont été supprimées car la gestion des permissions est maintenant directe
            // $builder->connect(
            //     '/dossiers/getUsers',
            //     [
            //         'controller' => 'Dossiers',
            //         'action' => 'getUsers',
            //         '_ext' => 'json'
            //     ]
            // );
            // $builder->connect(
            //     '/dossiers/updateUserPermission',
            //     [
            //         'controller' => 'Dossiers',
            //         'action' => 'updateUserPermission',
            //         '_ext' => 'json'
            //     ],
            //     ['methods' => ['POST']]
            // );

            // Nouvelles routes pour le panneau d'administration et la mise à jour des permissions
            $builder->connect(
                '/admin/panel',
                ['controller' => 'Dossiers', 'action' => 'adminPanel']
            );
            $builder->connect(
                '/admin/updatePermissions',
                ['controller' => 'Dossiers', 'action' => 'updatePermissions'],
                ['methods' => ['POST']]
            );
            
            // Route pour le téléchargement sécurisé
            $builder->connect(
                '/dossiers/download/*',
                ['controller' => 'Dossiers', 'action' => 'download']
            );

            // Fallback pour les contrôleurs et actions par défaut
            $builder->fallbacks();
        }
    );

    // API routes (à supprimer ou adapter si elles ne sont plus utilisées)
    // Ces routes ont été commentées car le système de permissions n'utilise plus d'API.
    // Si vous avez d'autres APIs, conservez cette section et adaptez-la.
    // $routes->scope(
    //     '/api',
    //     ['_namePrefix' => 'api:'],
    //     function (RouteBuilder $builder): void {
    //         $builder->setExtensions(['json']);
            
    //         // Routes for the Pr API (permissions management)
    //         $builder->resources('Pr', [
    //             'path' => 'pr',
    //             'map' => [
    //                 'getUsers' => [
    //                     'action' => 'getUsers',
    //                     'method' => 'GET',
    //                     'path' => 'users'
    //                 ],
    //                 'updatePermissions' => [
    //                     'action' => 'updatePermissions',
    //                     'method' => 'POST',
    //                     'path' => 'permissions'
    //                 ]
    //             ]
    //         ]);

    //         // If you have other API controllers, add them here
    //         // $builder->resources('OtherApiResource');
    //     }
    // );
};