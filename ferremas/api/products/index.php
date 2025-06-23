<?php
require_once "../../config/database.php";
require_once "../../models/Product.php";
require_once "../BaseAPI.php";

class ProductAPI extends BaseAPI {
    private $product;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->product = new Product($db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $this->requireAuth();

        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $this->getProduct($_GET['id']);
                } else {
                    $this->getAllProducts();
                }
                break;
            case 'POST':
                $this->checkRole(['administrator', 'warehouse']);
                $this->createProduct();
                break;
            case 'PUT':
                $this->checkRole(['administrator', 'warehouse']);
                $this->updateProduct();
                break;
            case 'DELETE':
                $this->checkRole(['administrator']);
                $this->deleteProduct();
                break;
            default:
                $this->sendError("Method not allowed", 405);
                break;
        }
    }

    private function getAllProducts() {
        $stmt = $this->product->read();
        $products = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($products, $row);
        }
        
        $this->sendResponse($products);
    }

    private function getProduct($id) {
        $this->product->id = $id;
        
        if ($this->product->readOne()) {
            $this->sendResponse([
                "id" => $this->product->id,
                "code" => $this->product->code,
                "name" => $this->product->name,
                "description" => $this->product->description,
                "price" => $this->product->price,
                "stock" => $this->product->stock,
                "category_name" => $this->product->category_id,
                "created_at" => $this->product->created_at
            ]);
        } else {
            $this->sendError("Product not found", 404);
        }
    }

    private function createProduct() {
        $data = $this->getPostData();
        
        if (!isset($data['code']) || !isset($data['name']) || !isset($data['price'])) {
            $this->sendError("Missing required fields");
            return;
        }

        $this->product->code = $data['code'];
        $this->product->name = $data['name'];
        $this->product->description = $data['description'] ?? "";
        $this->product->price = $data['price'];
        $this->product->stock = $data['stock'] ?? 0;
        $this->product->category_id = $data['category_id'] ?? null;

        if ($this->product->create()) {
            $this->sendResponse(["message" => "Product created successfully"], 201);
        } else {
            $this->sendError("Unable to create product");
        }
    }

    private function updateProduct() {
        $data = $this->getPostData();
        
        if (!isset($data['id'])) {
            $this->sendError("Product ID is required");
            return;
        }

        $this->product->id = $data['id'];
        $this->product->name = $data['name'] ?? null;
        $this->product->description = $data['description'] ?? null;
        $this->product->price = $data['price'] ?? null;
        $this->product->stock = $data['stock'] ?? null;
        $this->product->category_id = $data['category_id'] ?? null;

        if ($this->product->update()) {
            $this->sendResponse(["message" => "Product updated successfully"]);
        } else {
            $this->sendError("Unable to update product");
        }
    }

    private function deleteProduct() {
        $data = $this->getPostData();
        
        if (!isset($data['id'])) {
            $this->sendError("Product ID is required");
            return;
        }

        $this->product->id = $data['id'];

        if ($this->product->delete()) {
            $this->sendResponse(["message" => "Product deleted successfully"]);
        } else {
            $this->sendError("Unable to delete product");
        }
    }
}

// Handle the request
$api = new ProductAPI();
$api->handleRequest();
?>
