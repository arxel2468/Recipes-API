<?php
namespace App\Middleware;

use Firebase\JWT\JWT;

class AuthMiddleware
{
    public static function authenticate()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        
        try {
            $secret_key = $_ENV['JWT_SECRET'] ?? 'your_secret_key';
            $decoded = JWT::decode($token, $secret_key, ['HS256']);
            return $decoded;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
    }
}