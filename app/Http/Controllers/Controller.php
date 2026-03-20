<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Innowise E-Commerce API',
    version: '1.0.0',
    description: 'API documentation for Innowise E-Commerce Solutions',
    contact: new OA\Contact(email: 'admin@example.com')
)]
#[OA\Server(
    url: '/',
    description: 'Main API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller {}
