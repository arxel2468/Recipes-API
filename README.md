# Recipes API

A RESTful API for managing recipes with authentication, search, and rating features.

## Setup

1. Clone the repository
2. Run docker-compose:
   ```
   docker-compose up -d
   ```
3. Install dependencies:
   ```
   docker exec -it php_container composer install
   ```

## API Endpoints

### Authentication
- `POST /auth/login` - Login and get JWT token

### Recipes
- `GET /recipes` - List all recipes
- `POST /recipes` - Create a new recipe (Protected)
- `GET /recipes/{id}` - Get a specific recipe
- `PUT/PATCH /recipes/{id}` - Update a recipe (Protected)
- `DELETE /recipes/{id}` - Delete a recipe (Protected)
- `POST /recipes/{id}/rating` - Rate a recipe

### Search
- `GET /recipes/search` - Search for recipes

Search parameters:
- `name` - Search by recipe name
- `difficulty` - Filter by difficulty (1-3)
- `vegetarian` - Filter by vegetarian status (true/false)
- `min_rating` - Filter by minimum average rating
- `sort_by` - Sort by field (name, prep_time, difficulty)
- `sort_dir` - Sort direction (asc, desc)
- `limit` - Limit results
- `offset` - Pagination offset

## Authentication

All protected endpoints require a JWT token obtained by logging in.
Include token in the Authorization header:
```
Authorization: Bearer <token>
```

Default user credentials:
- Username: admin
- Password: password

## Data Structure

### Recipe
- id: Unique identifier
- name: Recipe name
- prep_time: Preparation time in minutes
- difficulty: Level 1-3
- vegetarian: Boolean
- avg_rating: Average rating (1-5)

### Rating
- recipe_id: Recipe identifier
- rating: 1-5 score

## Testing

Run unit tests:
```
docker exec -it php_container vendor/bin/phpunit
```