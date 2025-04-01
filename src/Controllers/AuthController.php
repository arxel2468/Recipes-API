<?php
namespace App\Controllers;

use App\Utils\Database;
use Firebase\JWT\JWT;
use PDO;

class AuthController
{
    public function login($data)
    {
        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            return ['error' => 'Username and password are required'];
        }
        
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $data['username']);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            return ['error' => 'Invalid credentials'];
        }
        
        $secret_key = $_ENV['JWT_SECRET'] ?? 'your_secret_key';
        $payload = [
            'iss' => 'recipe_api',
            'aud' => 'recipe_client',
            'iat' => time(),
            'exp' => time() + (60 * 60), // Token valid for 1 hour
            'data' => [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]
        ];
        
        $jwt = JWT::encode($payload, $secret_key);
        
        return [
            'token' => $jwt,
            'expires' => $payload['exp']
        ];
    }
}