<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// ─────────────────────────────────────────────
// Configuration
// ─────────────────────────────────────────────
define('API_TOKEN', 'dmmmsu-cookbook-token-2026');

define('DB_HOST', 'localhost');
define('DB_NAME', 'filipino_cookbook_api');
define('DB_USER', 'root');
define('DB_PASS', '');

// ─────────────────────────────────────────────
// Database Connection (PDO)
// ─────────────────────────────────────────────
function getDB(): PDO {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USER,
                DB_PASS
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $conn;
}

// ─────────────────────────────────────────────
// Token Validation Helper
// ─────────────────────────────────────────────
function validateToken(Request $request, Response $response): ?Response {
    $authHeader = $request->getHeaderLine('Authorization');
    $expectedHeader = 'Bearer ' . API_TOKEN;

    if (empty($authHeader) || $authHeader !== $expectedHeader) {
        $response->getBody()->write(json_encode([
            'status'  => 'error',
            'message' => 'Unauthorized access. Valid API token is required.'
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    return null; // token is valid
}

// ─────────────────────────────────────────────
// Slim App Setup
// ─────────────────────────────────────────────
$app = AppFactory::create();

// Base path for XAMPP subdirectory
$app->setBasePath('/filipino-cookbook-api/public');

// Error middleware
$app->addErrorMiddleware(true, true, true);

// ─────────────────────────────────────────────
// PUBLIC ROUTE — No token required
// GET /
// ─────────────────────────────────────────────
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to the Secured Filipino Cookbook API',
        'note'    => 'Use a valid Bearer token to access /api endpoints.'
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// ─────────────────────────────────────────────
// SECURED ROUTE — GET /api/foods
// Retrieve all Filipino food records with ingredients
// ─────────────────────────────────────────────
$app->get('/api/foods', function (Request $request, Response $response) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $db = getDB();

        // Fetch all foods
        $stmt = $db->query(
            "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
             FROM foods f
             JOIN categories c ON f.category_id = c.category_id
             JOIN origins o    ON f.origin_id   = o.origin_id
             ORDER BY f.food_id"
        );
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach ingredients to each food
        $ingStmt = $db->prepare(
            "SELECT i.ingredient_name
             FROM ingredients i
             JOIN food_ingredients fi ON i.ingredient_id = fi.ingredient_id
             WHERE fi.food_id = :id
             ORDER BY i.ingredient_name"
        );

        foreach ($foods as &$food) {
            $ingStmt->bindParam(':id', $food['food_id'], PDO::PARAM_INT);
            $ingStmt->execute();
            $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        unset($food);

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// SECURED ROUTE — GET /api/foods/search/{name}
// Search food records by name (must be before /api/foods/{id})
// ─────────────────────────────────────────────
$app->get('/api/foods/search/{name}', function (Request $request, Response $response, array $args) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $search = $args['name'] ?? '';

        if (empty($search)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Search name is required.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = getDB();
        $stmt = $db->prepare(
            "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
             FROM foods f
             JOIN categories c ON f.category_id = c.category_id
             JOIN origins o    ON f.origin_id   = o.origin_id
             WHERE f.food_name LIKE :search
             ORDER BY f.food_name"
        );
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($foods));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// SECURED ROUTE — GET /api/foods/{id}
// Retrieve one food by ID (with ingredients)
// ─────────────────────────────────────────────
$app->get('/api/foods/{id}', function (Request $request, Response $response, array $args) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $food_id = (int) $args['id'];
        $db = getDB();

        $stmt = $db->prepare(
            "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
             FROM foods f
             JOIN categories c ON f.category_id = c.category_id
             JOIN origins o    ON f.origin_id   = o.origin_id
             WHERE f.food_id = :id"
        );
        $stmt->bindParam(':id', $food_id, PDO::PARAM_INT);
        $stmt->execute();
        $food = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$food) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Food not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Fetch ingredients
        $ingStmt = $db->prepare(
            "SELECT i.ingredient_name
             FROM ingredients i
             JOIN food_ingredients fi ON i.ingredient_id = fi.ingredient_id
             WHERE fi.food_id = :id
             ORDER BY i.ingredient_name"
        );
        $ingStmt->bindParam(':id', $food_id, PDO::PARAM_INT);
        $ingStmt->execute();
        $food['ingredients'] = $ingStmt->fetchAll(PDO::FETCH_COLUMN);

        $response->getBody()->write(json_encode($food));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// SECURED ROUTE — GET /api/categories
// Retrieve all food categories
// ─────────────────────────────────────────────
$app->get('/api/categories', function (Request $request, Response $response) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $db = getDB();
        $stmt = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_id");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($categories));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// SECURED ROUTE — GET /api/ingredients
// Retrieve all ingredients
// ─────────────────────────────────────────────
$app->get('/api/ingredients', function (Request $request, Response $response) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $db = getDB();
        $stmt = $db->query("SELECT ingredient_id, ingredient_name FROM ingredients ORDER BY ingredient_name");
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($ingredients));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// SECURED ROUTE — POST /api/foods
// Add a new Filipino food record
// ─────────────────────────────────────────────
$app->post('/api/foods', function (Request $request, Response $response) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        // Validate required fields
        if (empty($data['food_name']) || empty($data['category_id']) ||
            empty($data['origin_id'])  || empty($data['instructions'])) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Missing required fields: food_name, category_id, origin_id, instructions'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = getDB();

        // Compute next food_id manually (table has no AUTO_INCREMENT)
        $maxStmt = $db->query("SELECT COALESCE(MAX(food_id), 0) + 1 FROM foods");
        $newFoodId = (int) $maxStmt->fetchColumn();

        // Insert new food
        $insertStmt = $db->prepare(
            "INSERT INTO foods (food_id, food_name, category_id, origin_id, instructions)
             VALUES (:food_id, :food_name, :category_id, :origin_id, :instructions)"
        );
        $insertStmt->bindParam(':food_id',      $newFoodId,            PDO::PARAM_INT);
        $insertStmt->bindParam(':food_name',    $data['food_name'],    PDO::PARAM_STR);
        $insertStmt->bindParam(':category_id',  $data['category_id'],  PDO::PARAM_INT);
        $insertStmt->bindParam(':origin_id',    $data['origin_id'],    PDO::PARAM_INT);
        $insertStmt->bindParam(':instructions', $data['instructions'], PDO::PARAM_STR);
        $insertStmt->execute();

        // $newFoodId already set above

        // Attach ingredients if provided
        if (!empty($data['ingredient_ids']) && is_array($data['ingredient_ids'])) {
            $ingStmt = $db->prepare(
                "INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (:food_id, :ingredient_id)"
            );
            foreach ($data['ingredient_ids'] as $ingredientId) {
                $ingStmt->bindParam(':food_id',       $newFoodId,    PDO::PARAM_INT);
                $ingStmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                $ingStmt->execute();
            }
        }

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Food added successfully.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// SECURED ROUTE — DELETE /api/foods/{id}
// Delete a food record by ID
// ─────────────────────────────────────────────
$app->delete('/api/foods/{id}', function (Request $request, Response $response, array $args) {
    $denied = validateToken($request, $response);
    if ($denied) return $denied;

    try {
        $food_id = (int) $args['id'];
        $db = getDB();

        // Check if food exists
        $checkStmt = $db->prepare("SELECT food_id FROM foods WHERE food_id = :id");
        $checkStmt->bindParam(':id', $food_id, PDO::PARAM_INT);
        $checkStmt->execute();

        if (!$checkStmt->fetch()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Food not found.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Delete food (food_ingredients rows are removed by ON DELETE CASCADE)
        $delStmt = $db->prepare("DELETE FROM foods WHERE food_id = :id");
        $delStmt->bindParam(':id', $food_id, PDO::PARAM_INT);
        $delStmt->execute();

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Food deleted successfully.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ─────────────────────────────────────────────
// Run the app
// ─────────────────────────────────────────────
$app->run();
