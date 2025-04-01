<?php
namespace App\Controllers;

use App\Models\Recipe;
use App\Models\Rating;

class RecipeController
{
    public function index()
    {
        $limit = $_GET['limit'] ?? 10;
        $offset = $_GET['offset'] ?? 0;
        return [
            'data' => Recipe::all($limit, $offset),
            'meta' => [
                'limit' => (int)$limit,
                'offset' => (int)$offset
            ]
        ];
    }
    
    public function show($id)
    {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            http_response_code(404);
            return ['error' => 'Recipe not found'];
        }
        
        return ['data' => $recipe];
    }
    
    public function store($data)
    {
        $requiredFields = ['name', 'prep_time', 'difficulty', 'vegetarian'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                return ['error' => "Missing required field: $field"];
            }
        }
        
        if ($data['difficulty'] < 1 || $data['difficulty'] > 3) {
            http_response_code(400);
            return ['error' => 'Difficulty must be between 1 and 3'];
        }
        
        try {
            $recipe = Recipe::create($data);
            http_response_code(201);
            return ['data' => $recipe];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to create recipe: ' . $e->getMessage()];
        }
    }
    
    public function update($id, $data)
    {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            http_response_code(404);
            return ['error' => 'Recipe not found'];
        }
        
        if (isset($data['difficulty']) && ($data['difficulty'] < 1 || $data['difficulty'] > 3)) {
            http_response_code(400);
            return ['error' => 'Difficulty must be between 1 and 3'];
        }
        
        try {
            $updated = Recipe::update($id, $data);
            return ['data' => $updated];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to update recipe: ' . $e->getMessage()];
        }
    }
    
    public function destroy($id)
    {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            http_response_code(404);
            return ['error' => 'Recipe not found'];
        }
        
        try {
            Recipe::delete($id);
            http_response_code(204);
            return null;
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to delete recipe: ' . $e->getMessage()];
        }
    }
    
    public function rate($id, $data)
    {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            http_response_code(404);
            return ['error' => 'Recipe not found'];
        }
        
        if (!isset($data['rating'])) {
            http_response_code(400);
            return ['error' => 'Rating is required'];
        }
        
        $rating = (int)$data['rating'];
        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            return ['error' => 'Rating must be between 1 and 5'];
        }
        
        try {
            Rating::create($id, $rating);
            $avgRating = Rating::getAverageForRecipe($id);
            return [
                'data' => [
                    'recipe_id' => (int)$id,
                    'avg_rating' => (float)$avgRating
                ]
            ];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to rate recipe: ' . $e->getMessage()];
        }
    }
    
    public function search($params)
    {
        try {
            $recipes = Recipe::search($params);
            return [
                'data' => $recipes,
                'meta' => [
                    'count' => count($recipes),
                    'filters' => $params
                ]
            ];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => 'Search failed: ' . $e->getMessage()];
        }
    }
}