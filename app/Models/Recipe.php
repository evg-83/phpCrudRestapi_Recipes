<?php

/**
 * Класс Recipe представляет сущность "Рецепт" и предоставляет методы для работы с ним в базе данных.
 */
class Recipe implements JsonSerializable
{
    private $db;
    private $id;
    private $name;
    private $ingredients;
    private $steps;
    private $photo;
    private $userId;

    /**
     * Конструктор класса Recipe.
     *
     * @param PDO $db Объект подключения к базе данных.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Установка идентификатора рецепта.
     *
     * @param int $id Идентификатор рецепта.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Получение идентификатора рецепта.
     *
     * @return int Идентификатор рецепта.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Установка названия рецепта.
     *
     * @param string $name Название рецепта.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Установка списка ингредиентов рецепта.
     *
     * @param array $ingredients Список ингредиентов рецепта.
     */
    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;
    }

    /**
     * Установка списка шагов приготовления рецепта.
     *
     * @param array $steps Список шагов приготовления рецепта.
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;
    }

    /**
     * Установка фотографии рецепта.
     *
     * @param string $photo Путь к фотографии рецепта.
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * Устанавливает идентификатор пользователя, связанного с рецептом.
     *
     * @param int $userId Идентификатор пользователя.
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Сохранение рецепта в базе данных.
     */
    public function save()
    {
        // Реализация сохранения рецепта в БД
        $query = "INSERT INTO recipes (name, ingredients, steps, photo, user_id) VALUES (:name, :ingredients, :steps, :photo, :user_id)";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindValue(':ingredients', json_encode($this->ingredients));
        $stmt->bindValue(':steps', json_encode($this->steps));
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':user_id', $this->userId);
        $stmt->execute();
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Обновление данных рецепта в базе данных.
     */
    public function update()
    {
        // Реализация обновления рецепта в БД
        $query = "UPDATE recipes SET name = :name, ingredients = :ingredients, steps = :steps, photo = :photo WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindValue(':ingredients', json_encode($this->ingredients));
        $stmt->bindValue(':steps', json_encode($this->steps));
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    /**
     * Получение рецепта по его идентификатору из базы данных.
     *
     * @param int $recipeId Идентификатор рецепта.
     * @param PDO $db Объект подключения к базе данных.
     * @return Recipe Объект рецепта.
     */
    public static function getById($recipeId, $db)
    {
        $query = "SELECT * FROM recipes WHERE id = :id";
        $stmt  = $db->prepare($query);
        $stmt->bindParam(':id', $recipeId);
        $stmt->execute();
        $recipeData = $stmt->fetch(PDO::FETCH_ASSOC);

        $recipe = new Recipe($db);
        $recipe->setId($recipeData['id']);
        $recipe->setName($recipeData['name']);
        $recipe->setIngredients(json_decode($recipeData['ingredients'], true));
        $recipe->setSteps(json_decode($recipeData['steps'], true));
        $recipe->setPhoto($recipeData['photo']);
        $recipe->setUserId($recipeData['user_id']);

        return $recipe;
    }

    /** 
     * Реализация добавления ингредиента к рецепту в таблицу recipe_ingredients 
     */
    public function addIngredient($ingredientId, $amount)
    {
        $query = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, amount) VALUES (:recipe_id, :ingredient_id, :amount)";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':recipe_id', $this->id);
        $stmt->bindParam(':ingredient_id', $ingredientId);
        $stmt->bindParam(':amount', $amount);
        $stmt->execute();
    }

    /**
     * Удаление рецепта из базы данных.
     */
    public function delete()
    {
        $query = "DELETE FROM recipes WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    public static function deleteById($recipeId, $db)
    {
        $recipe = Recipe::getById($recipeId, $db);

        // Удаление связанных данных: ингредиентов и шагов рецепта
        $queryDeleteIngredients = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipeId";
        $stmtDeleteIngredients  = $db->prepare($queryDeleteIngredients);
        $stmtDeleteIngredients->bindParam(':recipeId', $recipeId);
        $stmtDeleteIngredients->execute();

        // Удаление рецепта из БД
        $queryDeleteRecipe = "DELETE FROM recipes WHERE id = :recipeId";
        $stmtDeleteRecipe  = $db->prepare($queryDeleteRecipe);
        $stmtDeleteRecipe->bindParam(':recipeId', $recipeId);
        $stmtDeleteRecipe->execute();
    }

    /**
     * Метод для сериализации объекта в JSON.
     * @return array Данные для сериализации.
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'ingredients' => $this->ingredients,
            'steps'       => $this->steps,
            'photo'       => $this->photo,
            'userId'      => $this->userId,
        ];
    }
}
