<?php

require __DIR__ . '/../core/DB.php';
require __DIR__ . '/../Models/User.php';

/**
 * Class AuthController
 * Контроллер для аутентификации и регистрации пользователей.
 */
class AuthController
{
    private $db;

    /**
     * AuthController constructor.
     * Инициализация подключения к базе данных.
     */
    public function __construct()
    {
        $this->db = DB::getConnection();
    }

    /**
     * Обработка регистрации нового пользователя.
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $input = json_decode(file_get_contents('php://input'), true);
            $input = $_POST;

            $user = new User($this->db);

            $username = $input['username'];
            $email    = $input['email'];
            $password = $input['password'];

            $user->register($username, $email, $password);

            // Генерация токена
            $token = bin2hex(random_bytes(32));

            // Сохранение токена в сессии
            $_SESSION['token'] = $token;

            // Вернуть JSON-ответ об успешной регистрации
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Регистрация прошла успешно", "token" => $token]);
        } else {
            // Вернуть JSON-ответ с кодом ошибки и сообщением
            http_response_code(400);
            echo json_encode(["error" => "Неверный метод запроса"]);
        }
    }

    /**
     * Обработка аутентификации пользователя.
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $input = json_decode(file_get_contents('php://input'), true);
            $input = $_POST;

            $user = new User($this->db);

            $username = $input['username'];
            $password = $input['password'];

            if ($user->login($username, $password)) {
                // Генерация токена
                $token = bin2hex(random_bytes(32));

                // Сохранение токена в сессии
                $_SESSION['token'] = $token;

                // Вернуть JSON-ответ об успешной аутентификации
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(["message" => "Аутентификация прошла успешно", "token" => $token]);
            } else {
                // Вернуть JSON-ответ об ошибке аутентификации
                http_response_code(401);
                echo json_encode(["error" => "Ошибка аутентификации"]);
            }
        } else {
            // Вернуть JSON-ответ с кодом ошибки и сообщением
            http_response_code(400);
            echo json_encode(["error" => "Неверный метод запроса"]);
        }
    }

    /**
     * Обработка выхода пользователя из системы.
     */
    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User($this->db);
            $user->logout();
            // Вернуть JSON-ответ об успешном выходе из системы
            http_response_code(204);
            echo json_encode(["message" => "Выход из системы выполнен"]);
        } else {
            // Вернуть JSON-ответ с кодом ошибки и сообщением
            http_response_code(400);
            echo json_encode(["error" => "Неверный метод запроса"]);
        }
    }
}
