# Filipino Cookbook API - Full API Reference

This document provides complete technical documentation for every available endpoint in the **Filipino Cookbook API**.

---

## Base URL

```
http://localhost/filipino-cookbook-api/public
```

---

## Authentication

All endpoints under `/api` require a Bearer token in the request header.

```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

Requests without a valid token receive:

**HTTP 401 Unauthorized**

```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

---

## Endpoints

---

### 1. Welcome / Health Check

**Endpoint:**
```
GET /
```

**Description:** Returns a welcome message. This is the only public endpoint and does not require authentication.

**Required Headers:** None

**Example Request:**
```
GET http://localhost/filipino-cookbook-api/public/
```

**Example Successful Response (HTTP 200):**
```json
{
  "message": "Welcome to the Secured Filipino Cookbook API",
  "note": "Use a valid Bearer token to access /api endpoints."
}
```

---

### 2. Get All Foods

**Endpoint:**
```
GET /api/foods
```

**Description:** Returns all Filipino food records stored in the database, including the category name, origin name, and a list of ingredients for each food.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

**Example Request:**
```
GET http://localhost/filipino-cookbook-api/public/api/foods
```

**Example Successful Response (HTTP 200):**
```json
[
  {
    "food_id": 1,
    "food_name": "Adobo",
    "category_name": "Main Dish",
    "origin_name": "Philippines",
    "instructions": "Marinate the meat with soy sauce, vinegar, garlic, bay leaves, and peppercorn. Simmer until the meat becomes tender and the sauce is reduced.",
    "ingredients": [
      "Bay leaves",
      "Chicken or pork",
      "Cooking oil",
      "Garlic",
      "Peppercorn",
      "Soy sauce",
      "Vinegar"
    ]
  }
]
```

**Example Error Response (HTTP 401):**
```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

---

### 3. Get Food by ID

**Endpoint:**
```
GET /api/foods/{id}
```

**Description:** Returns the full details of a single food record identified by its numeric ID, including its ingredients list.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

**Path Parameter:**

| Parameter | Type    | Required | Description            |
|-----------|---------|----------|------------------------|
| `id`      | integer | Yes      | The ID of the food item |

**Example Request:**
```
GET http://localhost/filipino-cookbook-api/public/api/foods/1
```

**Example Successful Response (HTTP 200):**
```json
{
  "food_id": 1,
  "food_name": "Adobo",
  "category_name": "Main Dish",
  "origin_name": "Philippines",
  "instructions": "Marinate the meat with soy sauce, vinegar, garlic, bay leaves, and peppercorn. Simmer until the meat becomes tender and the sauce is reduced.",
  "ingredients": [
    "Bay leaves",
    "Chicken or pork",
    "Cooking oil",
    "Garlic",
    "Peppercorn",
    "Soy sauce",
    "Vinegar"
  ]
}
```

**Example Error Response - Not Found (HTTP 404):**
```json
{
  "status": "error",
  "message": "Food not found"
}
```

**Example Error Response - Unauthorized (HTTP 401):**
```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

---

### 4. Search Foods by Name

**Endpoint:**
```
GET /api/foods/search/{name}
```

**Description:** Performs a partial (case-insensitive) search on food names. Returns all matching food records. This endpoint is an optional enhancement.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

**Path Parameter:**

| Parameter | Type   | Required | Description                                    |
|-----------|--------|----------|------------------------------------------------|
| `name`    | string | Yes      | The keyword or partial name to search for       |

**Example Request:**
```
GET http://localhost/filipino-cookbook-api/public/api/foods/search/adobo
```

**Example Successful Response (HTTP 200):**
```json
[
  {
    "food_id": 1,
    "food_name": "Adobo",
    "category_name": "Main Dish",
    "origin_name": "Philippines",
    "instructions": "Marinate the meat with soy sauce, vinegar, garlic, bay leaves, and peppercorn. Simmer until the meat becomes tender and the sauce is reduced."
  }
]
```

**Example Error Response - Missing Name (HTTP 400):**
```json
{
  "status": "error",
  "message": "Search name is required."
}
```

---

### 5. Get All Categories

**Endpoint:**
```
GET /api/categories
```

**Description:** Returns a list of all food categories available in the database.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

**Example Request:**
```
GET http://localhost/filipino-cookbook-api/public/api/categories
```

**Example Successful Response (HTTP 200):**
```json
[
  { "category_id": 1, "category_name": "Appetizer" },
  { "category_id": 2, "category_name": "Dessert" },
  { "category_id": 3, "category_name": "Grilled Dish" },
  { "category_id": 4, "category_name": "Main Dish" },
  { "category_id": 5, "category_name": "Noodle Dish" },
  { "category_id": 6, "category_name": "Soup" },
  { "category_id": 7, "category_name": "Vegetable Dish" }
]
```

---

### 6. Get All Ingredients

**Endpoint:**
```
GET /api/ingredients
```

**Description:** Returns a list of all ingredients stored in the database, sorted alphabetically.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

**Example Request:**
```
GET http://localhost/filipino-cookbook-api/public/api/ingredients
```

**Example Successful Response (HTTP 200):**
```json
[
  { "ingredient_id": 1,  "ingredient_name": "Annatto oil" },
  { "ingredient_id": 2,  "ingredient_name": "Bagoong" },
  { "ingredient_id": 3,  "ingredient_name": "Banana blossom" }
]
```

---

### 7. Add a New Food

**Endpoint:**
```
POST /api/foods
```

**Description:** Creates a new Filipino food record in the database. Optionally links existing ingredients to the new food record.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**

| Field           | Type         | Required | Description                                        |
|-----------------|--------------|----------|----------------------------------------------------|
| `food_name`     | string       | Yes      | Name of the food                                   |
| `category_id`   | integer      | Yes      | ID of the food category                            |
| `origin_id`     | integer      | Yes      | ID of the regional origin                          |
| `instructions`  | string       | Yes      | Step-by-step cooking instructions                  |
| `ingredient_ids`| integer[]    | No       | Array of ingredient IDs to link to this food        |

**Example Request Body:**
```json
{
  "food_name": "Sisig",
  "category_id": 1,
  "origin_id": 4,
  "instructions": "Grill and chop pork face, ears, and liver. Sizzle on a hot plate with onion, chili, and calamansi.",
  "ingredient_ids": [26, 40, 15, 10]
}
```

**Example Successful Response (HTTP 201):**
```json
{
  "status": "success",
  "message": "Food added successfully."
}
```

**Example Error Response - Missing Fields (HTTP 400):**
```json
{
  "status": "error",
  "message": "Missing required fields: food_name, category_id, origin_id, instructions"
}
```

**Example Error Response - Unauthorized (HTTP 401):**
```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

---

### 8. Delete a Food

**Endpoint:**
```
DELETE /api/foods/{id}
```

**Description:** Permanently deletes a food record from the database. Associated rows in `food_ingredients` are automatically removed via `ON DELETE CASCADE`.

**Required Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

**Path Parameter:**

| Parameter | Type    | Required | Description              |
|-----------|---------|----------|--------------------------|
| `id`      | integer | Yes      | The ID of the food to delete |

**Example Request:**
```
DELETE http://localhost/filipino-cookbook-api/public/api/foods/1
```

**Example Successful Response (HTTP 200):**
```json
{
  "status": "success",
  "message": "Food deleted successfully."
}
```

**Example Error Response - Not Found (HTTP 404):**
```json
{
  "status": "error",
  "message": "Food not found."
}
```

---

## HTTP Status Code Reference

| Status Code | Meaning                              |
|-------------|--------------------------------------|
| 200         | Request completed successfully       |
| 201         | Resource created successfully        |
| 400         | Invalid request or missing parameter |
| 401         | Missing or invalid authentication    |
| 403         | Access is not allowed                |
| 404         | Requested resource was not found     |
| 429         | Too many requests                    |
| 500         | Internal server error                |

---

## Sample Data

The database is pre-loaded with **15 Filipino foods** across 7 categories and 4 regional origins.

### Categories
| ID | Category Name   |
|----|-----------------|
| 1  | Appetizer       |
| 2  | Dessert         |
| 3  | Grilled Dish    |
| 4  | Main Dish       |
| 5  | Noodle Dish     |
| 6  | Soup            |
| 7  | Vegetable Dish  |

### Origins
| ID | Origin Name     |
|----|-----------------|
| 1  | Bacolod         |
| 2  | Bicol Region    |
| 3  | Ilocos Region   |
| 4  | Philippines     |

### Foods
| ID | Food Name        | Category      | Origin         |
|----|------------------|---------------|----------------|
| 1  | Adobo            | Main Dish     | Philippines    |
| 2  | Sinigang         | Soup          | Philippines    |
| 3  | Kare-Kare        | Main Dish     | Philippines    |
| 4  | Tinola           | Soup          | Philippines    |
| 5  | Bicol Express    | Main Dish     | Bicol Region   |
| 6  | Pinakbet         | Vegetable Dish| Ilocos Region  |
| 7  | Laing            | Vegetable Dish| Bicol Region   |
| 8  | Menudo           | Main Dish     | Philippines    |
| 9  | Afritada         | Main Dish     | Philippines    |
| 10 | Pancit Canton    | Noodle Dish   | Philippines    |
| 11 | Lumpiang Shanghai| Appetizer     | Philippines    |
| 12 | Lechon Kawali    | Main Dish     | Philippines    |
| 13 | Chicken Inasal   | Grilled Dish  | Bacolod        |
| 14 | Bulalo           | Soup          | Philippines    |
| 15 | Halo-Halo        | Dessert       | Philippines    |

---

*Filipino Cookbook API - Full API Reference Documentation*
