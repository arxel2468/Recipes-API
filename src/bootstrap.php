<?php
namespace App;

use App\Utils\Database;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialize database connection
Database::init();

// Setup headers for JSON API
header('Content-Type: application/json');