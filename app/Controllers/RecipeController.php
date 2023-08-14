<?php

require __DIR__ . '/../core/DB.php';
require __DIR__ . '/../Models/Recipe.php';

/**
 * Class RecipeController
 * Контроллер для работы с рецептами через REST API.
 */
class RecipeController
{
    private $db;

    /**
     * RecipeController constructor.
     * Инициализация подключения к базе данных.
     */
    public function __construct()
    {
        $this->db = DB::getConnection();
    }

    /**
     * Получение списка рецептов.
     * Возвращает JSON-ответ со списком рецептов.
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
            $query = "SELECT * FROM recipes";
            $stmt  = $this->db->prepare($query);
            $stmt->execute();
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Возвращаем список рецептов в виде JSON
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($recipes);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Реализация получения конкретного рецепта по его идентификатору 
     */
    public function getByRecipeId($recipeId)
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        try {
            // Получение существующего рецепта из БД
            $recipe = Recipe::getById($recipeId, $this->db);

            // Возвращаем рецепт в виде JSON
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($recipe);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Реализация создания рецепта 
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
            // $recipeData = json_decode(file_get_contents('php://input'), true);
            $recipeData = $_POST;

            // Валидация данных
            if (!$this->validateRecipeData($recipeData)) {
                http_response_code(400);
                echo json_encode(["error" => "Неверные данные рецепта"]);
                return;
            }

            // Обработка фотографии
            $photoPath = $this->handlePhotoUpload();

            // Создание объекта Recipe и сохранение в БД
            $recipe = new Recipe($this->db);
            $recipe->setName($recipeData['name']);
            $recipe->setIngredients($recipeData['ingredients']);
            $recipe->setSteps($recipeData['steps']);
            $recipe->setPhoto($photoPath);
            $recipe->setUserId($_SESSION['user_id']);

            // Сохранение рецепта в БД
            $recipe->save();

            // Добавление ингредиентов к рецепту
            foreach ($recipeData['ingredients'] as $ingredientData) {
                $ingredientId = $ingredientData['id'];
                $amount       = $ingredientData['amount'];

                $recipe->addIngredient($ingredientId, $amount);
            }

            // Возвращаем успешный статус и ID созданного ингредиента
            http_response_code(201);
            echo json_encode(["id" => $recipe->getId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Реализация редактирования рецепта 
     */
    public function update($recipeId)
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        // Проверка владельца рецепта
        if (!$this->isRecipeOwner($recipeId)) {
            http_response_code(403);
            echo json_encode(["error" => "Доступ запрещен"]);
            return;
        }

        try {
            // $recipeData = json_decode(file_get_contents('php://input'), true);
            $recipeData = $_POST;

            // Получение существующего рецепта из БД
            $recipe = Recipe::getById($recipeId, $this->db);

            // Валидация данных
            if (!$this->validateRecipeData($recipeData)) {
                http_response_code(400); // Неверный запрос
                echo json_encode(["error" => "Неверные данные рецепта"]);
                return;
            }

            // Обновление данных рецепта
            $recipe->setName($recipeData['name']);
            $recipe->setIngredients($recipeData['ingredients']);
            $recipe->setSteps($recipeData['steps']);

            // Обработка фотографии
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->handlePhotoUpload();
                $recipe->setPhoto($photoPath);
            }

            // Сохранение обновлённого рецепта в БД
            $recipe->update();

            // Возвращаем обновлённый рецепт
            http_response_code(200); // OK
            header('Content-Type: application/json');
            echo json_encode($recipe);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Реализация удаления рецепта 
     */
    public function delete($recipeId)
    {
        // Проверка авторизации пользователя
        if (!$this->isUserAuthorized()) {
            http_response_code(401);
            echo json_encode(["error" => "Неавторизованный"]);
            return;
        }

        // Проверка владельца рецепта
        if (!$this->isRecipeOwner($recipeId)) {
            http_response_code(403);
            echo json_encode(["error" => "Доступ запрещен"]);
            return;
        }

        try {
            // Удаление рецепта из БД
            Recipe::deleteById($recipeId, $this->db);

            // Возвращаем успешный статус
            http_response_code(204);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /** 
     * Реализация удаления нескольких рецептов
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
            // Получение списка идентификаторов рецептов из тела запроса
            $recipeIds = json_decode(file_get_contents('php://input'), true);

            // Проверка наличия списка идентификаторов рецептов
            if (!isset($recipeIds) || !is_array($recipeIds)) {
                http_response_code(400);
                echo json_encode(["error" => "Отсутствует список рецептов для удаления"]);
                return;
            }

            foreach ($recipeIds as $recipeId) {
                // Проверка владельца рецепта
                if (!$this->isRecipeOwner($recipeId)) {
                    continue; // Пропустить рецепт, который не пользователя
                }

                // Удаление рецепта из БД
                Recipe::deleteById($recipeId, $this->db);
            }

            // Возвращаем успешный статус
            http_response_code(204);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка DB: " . $e->getMessage()]);
        }
    }

    /**
     * Проверка наличия всех обязательных полей для рецепта.
     * @param array $data Данные рецепта для валидации.
     * @return bool Возвращает true, если данные валидны, иначе false.
     */
    private function validateRecipeData($data)
    {
        if (
            isset($data['name']) && !empty($data['name']) &&
            isset($data['ingredients']) && is_array($data['ingredients']) && count($data['ingredients']) > 0 &&
            isset($data['steps']) && is_array($data['steps']) && count($data['steps']) > 0
        ) {
            return true; // Данные валидны
        } else {
            return false; // Данные невалидны
        }
    }

    /**
     * Обработка загрузки фотографии для рецепта.
     * @return string|null Возвращает путь к сохраненной фотографии или null в случае ошибки.
     */
    private function handlePhotoUpload()
    {
        try {
            $uploadDir    = '../public/uploads/';
            $uploadedFile = $_FILES['photo'];

            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            $maxFileSize  = 5 * 1024 * 1024; // 5 MB

            // Проверка типа файла и размера
            if (!in_array($uploadedFile['type'], $allowedTypes) || $uploadedFile['size'] > $maxFileSize) {
                http_response_code(400);
                echo json_encode(["error" => "Несоответствующий формат фотографии"]);
                exit;
            }

            $photoName = uniqid() . '_' . $uploadedFile['name'];
            $photoPath = $uploadDir . $photoName;

            if (!move_uploaded_file($uploadedFile['tmp_name'], $photoPath)) {
                http_response_code(500);
                echo json_encode(["error" => "Ошибка загрузки файла"]);
                exit;
            }

            return $photoPath;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Ошибка обработки файла: " . $e->getMessage()]);
            exit;
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

    /**
     * Проверяет, является ли текущий пользователь владельцем рецепта.
     *
     * @param int $recipeId Идентификатор рецепта.
     * @return bool Возвращает true, если пользователь является владельцем рецепта, иначе false.
     */
    private function isRecipeOwner($recipeId)
    {
        if (!isset($_SESSION['user_id'])) {
            return false; // Пользователь не авторизован
        }

        try {
            // Проверка владельца рецепта
            $query = "SELECT COUNT(*) FROM recipes WHERE id = :recipeId AND user_id = :userId";
            $stmt  = $this->db->prepare($query);
            $stmt->bindParam(':recipeId', $recipeId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            return $count > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
