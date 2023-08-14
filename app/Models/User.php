<?php

/**
 * Class User
 * Модель для работы с пользователями.
 */
class User
{
    private $db;

    /**
     * User constructor.
     * @param PDO $db Подключение к базе данных.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Регистрация нового пользователя.
     * @param string $username Имя пользователя.
     * @param string $email Email пользователя.
     * @param string $password Пароль пользователя.
     */
    public function register($username, $email, $password)
    {
        // Валидация данных перед регистрацией
        if (empty($username) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "Все поля должны быть заполнены"]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Некорректный формат email"]);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt  = $this->db->prepare($query);
        $stmt->execute([$username, $email, $hashedPassword]);
    }

    /**
     * Аутентификация пользователя.
     * @param string $username Имя пользователя.
     * @param string $password Пароль пользователя.
     * @return bool Возвращает true, если аутентификация успешна, иначе false.
     */
    public function login($username, $password)
    {
        $query = "SELECT id, password FROM users WHERE username = ?";
        $stmt  = $this->db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }

        return false;
    }

    /**
     * Выход пользователя из системы.
     */
    public function logout()
    {
        unset($_SESSION['user_id']);
    }

    /**
     * Проверка авторизации пользователя.
     * @return bool Возвращает true, если пользователь авторизован, иначе false.
     */
    public function isAuthorized()
    {
        return isset($_SESSION['user_id']);
    }
}
