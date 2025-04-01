<?php
namespace App\Models;

use App\Utils\Database;
use PDO;

class Recipe
{
    public $id;
    public $name;
    public $prep_time;
    public $difficulty;
    public $vegetarian;
    public $created_at;
    public $updated_at;
    public $avg_rating;
    
    public static function all($limit = 10, $offset = 0)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, COALESCE(AVG(rt.rating), 0) as avg_rating
            FROM recipes r
            LEFT JOIN ratings rt ON r.id = rt.recipe_id
            GROUP BY r.id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }
    
    public static function find($id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, COALESCE(AVG(rt.rating), 0) as avg_rating
            FROM recipes r
            LEFT JOIN ratings rt ON r.id = rt.recipe_id
            WHERE r.id = :id
            GROUP BY r.id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchObject(self::class);
    }
    
    public static function create($data)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO recipes (name, prep_time, difficulty, vegetarian)
            VALUES (:name, :prep_time, :difficulty, :vegetarian)
            RETURNING *
        ");
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':prep_time', $data['prep_time'], PDO::PARAM_INT);
        $stmt->bindParam(':difficulty', $data['difficulty'], PDO::PARAM_INT);
        $stmt->bindParam(':vegetarian', $data['vegetarian'], PDO::PARAM_BOOL);
        $stmt->execute();
        
        return $stmt->fetchObject(self::class);
    }
    
    public static function update($id, $data)
    {
        $db = Database::getConnection();
        $sql = "UPDATE recipes SET updated_at = CURRENT_TIMESTAMP";
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'prep_time', 'difficulty', 'vegetarian'])) {
                $sql .= ", $key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        $sql .= " WHERE id = :id RETURNING *";
        $params[':id'] = $id;
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => &$val) {
            $type = PDO::PARAM_STR;
            if (is_int($val)) $type = PDO::PARAM_INT;
            if (is_bool($val)) $type = PDO::PARAM_BOOL;
            $stmt->bindParam($key, $val, $type);
        }
        
        $stmt->execute();
        return $stmt->fetchObject(self::class);
    }
    
    public static function delete($id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM recipes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public static function search($params)
    {
        $db = Database::getConnection();
        $sql = "
            SELECT r.*, COALESCE(AVG(rt.rating), 0) as avg_rating
            FROM recipes r
            LEFT JOIN ratings rt ON r.id = rt.recipe_id
            WHERE 1=1
        ";
        $queryParams = [];
        
        if (isset($params['name'])) {
            $sql .= " AND r.name ILIKE :name";
            $queryParams[':name'] = '%' . $params['name'] . '%';
        }
        
        if (isset($params['difficulty'])) {
            $sql .= " AND r.difficulty = :difficulty";
            $queryParams[':difficulty'] = $params['difficulty'];
        }
        
        if (isset($params['vegetarian'])) {
            $sql .= " AND r.vegetarian = :vegetarian";
            $queryParams[':vegetarian'] = filter_var($params['vegetarian'], FILTER_VALIDATE_BOOLEAN);
        }
        
        if (isset($params['min_rating'])) {
            $sql .= " HAVING COALESCE(AVG(rt.rating), 0) >= :min_rating";
            $queryParams[':min_rating'] = $params['min_rating'];
        }
        
        $sql .= " GROUP BY r.id";
        
        if (isset($params['sort_by']) && in_array($params['sort_by'], ['name', 'prep_time', 'difficulty'])) {
            $sortDirection = isset($params['sort_dir']) && strtoupper($params['sort_dir']) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY r." . $params['sort_by'] . " " . $sortDirection;
        }
        
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;
        $offset = isset($params['offset']) ? intval($params['offset']) : 0;
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $queryParams[':limit'] = $limit;
        $queryParams[':offset'] = $offset;
        
        $stmt = $db->prepare($sql);
        foreach ($queryParams as $key => &$val) {
            $type = PDO::PARAM_STR;
            if (is_int($val)) $type = PDO::PARAM_INT;
            if (is_bool($val)) $type = PDO::PARAM_BOOL;
            $stmt->bindParam($key, $val, $type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}