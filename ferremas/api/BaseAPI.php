<?php
class BaseAPI {
    protected function sendResponse($data, $status = 200) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($status);
        echo json_encode($data);
    }

    protected function sendError($message, $status = 400) {
        $this->sendResponse(["error" => $message], $status);
    }

    protected function getPostData() {
        return json_decode(file_get_contents("php://input"), true);
    }

    protected function requireAuth() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError("Unauthorized access", 401);
            exit();
        }
        return $_SESSION['user_id'];
    }

    protected function checkRole($allowedRoles) {
        if (!isset($_SESSION['role'])) {
            $this->sendError("Role not found", 403);
            exit();
        }
        
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            $this->sendError("Insufficient permissions", 403);
            exit();
        }
    }
}
?>
