<?php
require_once "../../config/database.php";
require_once "../../models/User.php";
require_once "../BaseAPI.php";

class AuthAPI extends BaseAPI {
    private $user;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->user = new User($db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'register':
                $this->register();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                $this->sendError("Invalid action", 400);
                break;
        }
    }

    private function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError("Method not allowed", 405);
            return;
        }

        $data = $this->getPostData();
        
        if (!isset($data['username']) || !isset($data['password'])) {
            $this->sendError("Username and password are required");
            return;
        }

        $this->user->username = $data['username'];
        $this->user->password = $data['password'];

        if ($this->user->login()) {
            session_start();
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['role'] = $this->user->getRole();
            
            $this->sendResponse([
                "message" => "Login successful",
                "user_id" => $this->user->id,
                "role" => $_SESSION['role']
            ]);
        } else {
            $this->sendError("Invalid credentials", 401);
        }
    }

    private function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError("Method not allowed", 405);
            return;
        }

        $data = $this->getPostData();
        
        if (!isset($data['username']) || !isset($data['password']) || !isset($data['email'])) {
            $this->sendError("Missing required fields");
            return;
        }

        // Check if username already exists
        $this->user->username = $data['username'];
        if ($this->user->usernameExists()) {
            $this->sendError("Username already exists");
            return;
        }

        $this->user->password = $data['password'];
        $this->user->email = $data['email'];
        $this->user->role_id = $data['role_id'] ?? 2; // Default to seller role if not specified

        if ($this->user->create()) {
            $this->sendResponse([
                "message" => "User registered successfully"
            ], 201);
        } else {
            $this->sendError("Unable to register user");
        }
    }

    private function logout() {
        session_start();
        session_destroy();
        $this->sendResponse(["message" => "Logout successful"]);
    }
}

// Handle the request
$api = new AuthAPI();
$api->handleRequest();
?>
