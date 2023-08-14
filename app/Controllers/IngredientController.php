<?php

require __DIR__ . '/../core/DB.php';
require __DIR__ . '/../Models/Ingredient.php';

/**
 * Class IngredientController
 * Контроллер для работы с ингредиентами через REST API.
 */
class IngredientController
{
    private $db;

    /**
     * IngredientController constructor.
     * Инициализация подключения к базе данных.
     */
    public function __construct()
    {
        $this->db = DB::getConnection();
    }

    /**
     * Получение списка ингредиентов.
     * Возвращает JSON-ответ со списком ингредиентов.
     */
    public function get()
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            $query = "SELECT * FROM ingredients";
            $stmt  = $this->db->prepare($query);
            $stmt->execute();
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Возвращаем список ингредиентов в виде JSON
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($ingredients);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /**
     * Создание ингредиента.
     * Возвращает JSON-ответ с ID созданного ингредиента или сообщение об ошибке.
     */
    public function create()
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            // $ingredientData = json_decode(file_get_contents('php://input'), true);
            $ingredientData = $_POST;

            // Валидация данных
            if (!$this->validateIngredientData($ingredientData)) {
                http_response_code(400);
                echo json_encode(["error" => "Неверные входные данные"]);
                return;
            }

            // Создание объекта Ingredient
            $ingredient = new Ingredient($this->db);
            $ingredient->setName($ingredientData['name']);
            $ingredient->setUnit($ingredientData['unit']);

            // Сохранение ингредиента в БД
            $ingredient->save();

            // Возвращаем успешный статус и ID созданного ингредиента
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(["id" => $ingredient->getId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /**
     * Редактирование ингредиента.
     * Возвращает JSON-ответ с сообщением об успехе или ошибке.
     */
    public function update($ingredientId)
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            // $ingredientData = json_decode(file_get_contents('php://input'), true);
            $ingredientData = $_POST;

            // Получение существующего ингредиента из БД
            $ingredient = Ingredient::getById($ingredientId, $this->db);

            // Валидация данных
            if (!$this->validateIngredientData($ingredientData)) {
                http_response_code(400);
                echo json_encode(["error" => "Неверные входные данные"]);
                return;
            }

            // Обновление данных ингредиента
            $ingredient->setName($ingredientData['name']);
            $ingredient->setUnit($ingredientData['unit']);

            // Сохранение обновленного ингредиента в БД
            $ingredient->update();

            // Возвращаем успешный статус
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($ingredient);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Просмотр ингредиента по ID 
     */
    public function getByIngredientId($ingredientId)
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            // Получение существующего ингредиента из БД
            $ingredient = Ingredient::getById($ingredientId, $this->db);

            // Возвращаем ингредиента в виде JSON
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($ingredient);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Удаление ингредиента по ID 
     */
    public function delete($ingredientId)
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            // Удаление ингредиента из БД
            Ingredient::deleteById($ingredientId, $this->db);

            // Возвращаем успешный статус
            http_response_code(204);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Удаление нескольких ингредиентов
     */
    public function deleteMultiple()
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            $ingredientIds = json_decode(file_get_contents('php://input'), true);
            // $ingredientIds = $_POST['ids'];

            foreach ($ingredientIds as $ingredientId) {
                Ingredient::deleteById($ingredientId, $this->db);
            }

            http_response_code(204);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /**
     * Валидация данных ингредиента.
     * @param array $data Данные ингредиента для валидации.
     * @return bool Возвращает true, если данные валидны, иначе false.
     */
    private function validateIngredientData($data)
    {
        if (
            isset($data['name']) && !empty($data['name']) &&
            isset($data['unit']) && !empty($data['unit'])
        ) {
            return true; // Данные валидны
        } else {
            return false; // Данные невалидны
        }
    }

    /**
     * Проверяет, авторизован ли текущий пользователь.
     *
     * @return bool Возвращает true, если пользователь авторизован, иначе false.
     */
    private function isUserAuthorized()
    {
        return isset($_SESSION['user_id']);
    }
}
