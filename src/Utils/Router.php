<?php
namespace App\Utils;

use FastRoute\RouteCollector;
use App\Controllers\RecipeController;
use App\Middleware\AuthMiddleware;

class Router
{
    public static function dispatch($httpMethod, $uri)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            // Recipes endpoints
            $r->addRoute('GET', '/recipes', function ($vars, $body) {
                return (new RecipeController())->index();
            });
            
            $r->addRoute('GET', '/recipes/search', function ($vars, $body) {
                return (new RecipeController())->search($_GET);
            });
            
            $r->addRoute('GET', '/recipes/{id:\d+}', function ($vars, $body) {
                return (new RecipeController())->show($vars['id']);
            });
            
            $r->addRoute('POST', '/recipes', function ($vars, $body) {
                AuthMiddleware::authenticate();
                return (new RecipeController())->store($body);
            });
            
            $r->addRoute('PUT', '/recipes/{id:\d+}', function ($vars, $body) {
                AuthMiddleware::authenticate();
                return (new RecipeController())->update($vars['id'], $body);
            });
            
            $r->addRoute('PATCH', '/recipes/{id:\d+}', function ($vars, $body) {
                AuthMiddleware::authenticate();
                return (new RecipeController())->update($vars['id'], $body);
            });
            
            $r->addRoute('DELETE', '/recipes/{id:\d+}', function ($vars, $body) {
                AuthMiddleware::authenticate();
                return (new RecipeController())->destroy($vars['id']);
            });
            
            $r->addRoute('POST', '/recipes/{id:\d+}/rating', function ($vars, $body) {
                return (new RecipeController())->rate($vars['id'], $body);
            });
            
            // Auth endpoints
            $r->addRoute('POST', '/auth/login', function ($vars, $body) {
                return (new \App\Controllers\AuthController())->login($body);
            });
        });

        return $dispatcher->dispatch($httpMethod, $uri);
    }
}