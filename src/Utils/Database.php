<?php
namespace App\Utils;

use PDO;

class Database
{
    private static $connection = null;

    public static function init()
    {
        if (self::$connection === null) {
            try {
                $host = 'postgres';
                $db = 'hellofresh';
                $user = 'hellofresh';
                $pass = 'hellofresh';
                $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                
                self::$connection = new PDO($dsn, $user, $pass, $options);
                self::initDatabase();
            } catch (\PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    public static function getConnection()
    {
        if (self::$connection === null) {
            self::init();
        }
        
        return self::$connection;
    }
    
    private static function initDatabase()
    {
        $queries = [
            // Create recipes table
            "CREATE TABLE IF NOT EXISTS recipes (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                prep_time INTEGER NOT NULL,
                difficulty INTEGER NOT NULL CHECK (difficulty BETWEEN 1 AND 3),
                vegetarian BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Create ratings table
            "CREATE TABLE IF NOT EXISTS ratings (
                id SERIAL PRIMARY KEY,
                recipe_id INTEGER NOT NULL REFERENCES recipes(id) ON DELETE CASCADE,
                rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Create users table
            "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            // Create default user
            "INSERT INTO users (username, password) 
             SELECT 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
             WHERE NOT EXISTS (SELECT id FROM users WHERE username = 'admin')"
        ];
        
        foreach ($queries as $query) {
            self::$connection->exec($query);
        }
    }
}