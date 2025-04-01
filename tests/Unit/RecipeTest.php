<?php
namespace Tests\Unit;

use App\Models\Recipe;
use PDO;
use PHPUnit\Framework\TestCase;

class RecipeTest extends TestCase
{
    private static $pdo;
    
    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create test tables
        self::$pdo->exec("
            CREATE TABLE recipes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                prep_time INTEGER NOT NULL,
                difficulty INTEGER NOT NULL CHECK (difficulty BETWEEN 1 AND 3),
                vegetarian INTEGER NOT NULL DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        self::$pdo->exec("
            CREATE TABLE ratings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                recipe_id INTEGER NOT NULL,
                rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
            )
        ");
        
        // Insert test data
        self::$pdo->exec("
            INSERT INTO recipes (name, prep_time, difficulty, vegetarian) VALUES
            ('Pasta Carbonara', 30, 2, 0),
            ('Vegetable Curry', 45, 2, 1),
            ('Simple Salad', 15, 1, 1)
        ");
        
        self::$pdo->exec("
            INSERT INTO ratings (recipe_id, rating) VALUES
            (1, 5),
            (1, 4),
            (2, 3),
            (3, 5)
        ");
    }
    
    protected function setUp(): void
    {
        // Mock the Database class to return our SQLite connection
        $this->createMock('App\Utils\Database')
            ->method('getConnection')
            ->willReturn(self::$pdo);
    }
    
    public function testFindReturnsRecipe()
    {
        $recipe = Recipe::find(1);
        $this->assertEquals('Pasta Carbonara', $recipe->name);
        $this->assertEquals(30, $recipe->prep_time);
        $this->assertEquals(2, $recipe->difficulty);
        $this->assertEquals(0, $recipe->vegetarian);
    }
    
    public function testCreateRecipe()
    {
        $data = [
            'name' => 'Test Recipe',
            'prep_time' => 20,
            'difficulty' => 1,
            'vegetarian' => true
        ];
        
        $recipe = Recipe::create($data);
        $this->assertEquals('Test Recipe', $recipe->name);
        $this->assertEquals(20, $recipe->prep_time);
        $this->assertEquals(1, $recipe->difficulty);
        $this->assertEquals(true, $recipe->vegetarian);
    }
    
    public function testUpdateRecipe()
    {
        $data = [
            'name' => 'Updated Recipe',
            'prep_time' => 25
        ];
        
        $recipe = Recipe::update(1, $data);
        $this->assertEquals('Updated Recipe', $recipe->name);
        $this->assertEquals(25, $recipe->prep_time);
    }
    
    public function testDeleteRecipe()
    {
        $result = Recipe::delete(3);
        $this->assertTrue($result);
        
        $recipe = Recipe::find(3);
        $this->assertNull($recipe);
    }
    
    public function testSearchRecipes()
    {
        $params = ['vegetarian' => true];
        $recipes = Recipe::search($params);
        
        $this->assertCount(2, $recipes);
        $this->assertTrue($recipes[0]->vegetarian);
        $this->assertTrue($recipes[1]->vegetarian);
    }
}