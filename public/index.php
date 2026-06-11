<?php
/**
 * Front controller – single entry point for all web requests
 *
 * Every URL is routed through this file by Nginx (try_files directive).
 * It bootstraps the application, registers all routes, and hands off
 * to the appropriate controller action.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AppointmentController;
use App\Controllers\ApiController;
use App\Middleware\AuthMiddleware;

$router = new Router();

// ── Public pages ──────────────────────────────────────────────────────────────
$router->get('/',         [HomeController::class,        'index']);
$router->get('/home',     [HomeController::class,        'index']);

// ── Authentication ────────────────────────────────────────────────────────────
$router->get('/login',    [AuthController::class,        'showLogin']);
$router->post('/login',   [AuthController::class,        'login']);
$router->get('/register', [AuthController::class,        'showRegister']);
$router->post('/register',[AuthController::class,        'register']);
$router->get('/logout',   [AuthController::class,        'logout']);

// ── Calendar (requires login) ─────────────────────────────────────────────────
$router->get('/calendar', [AppointmentController::class, 'index']);

// ── JSON API (requires login) ─────────────────────────────────────────────────
$router->get('/api/appointments',         [ApiController::class, 'index']);
$router->post('/api/appointments',        [ApiController::class, 'store']);
$router->put('/api/appointments/:id',     [ApiController::class, 'update']);
$router->delete('/api/appointments/:id',  [ApiController::class, 'destroy']);

$router->dispatch();
