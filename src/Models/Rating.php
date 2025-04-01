<?php
namespace App\Models;

use App\Utils\Database;
use PDO;

class Rating
{
    public $id;
    public $recipe_id;
    public $rating;
    public $created_at;
    
    public static function create($recipe_id, $rating)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ratings (recipe_id, rating)
            VALUES (:recipe_id, :rating)
            RETURNING *
        ");
        
        $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchObject(self::class);
    }
    
    public static function getAverageForRecipe($recipe_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(AVG(rating), 0) as avg_rating
            FROM ratings
            WHERE recipe_id = :recipe_id
        ");
        
        $stmt->bindParam(':recipe_id', $recipe_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
}