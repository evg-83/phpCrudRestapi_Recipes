<?php

/**
 * Класс Ingredient представляет сущность "Ингредиент" и предоставляет методы для работы с ним в базе данных.
 */
class Ingredient implements JsonSerializable
{
    private $db;
    private $id;
    private $name;
    private $unit;

    /**
     * Конструктор класса Ingredient.
     *
     * @param PDO $db Объект подключения к базе данных.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Установка идентификатора ингредиента.
     *
     * @param int $id Идентификатор ингредиента.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Получение идентификатора ингредиента.
     *
     * @return int Идентификатор ингредиента.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Установка названия ингредиента.
     *
     * @param string $name Название ингредиента.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Установка единицы измерения ингредиента.
     *
     * @param string $unit Единица измерения ингредиента.
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * Сохранение ингредиента в базе данных.
     */
    public function save()
    {
        $query = "INSERT INTO ingredients (name, unit) VALUES (:name, :unit)";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':unit', $this->unit);
        $stmt->execute();
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Обновление данных ингредиента в базе данных.
     */
    public function update()
    {
        $query = "UPDATE ingredients SET name = :name, unit = :unit WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':unit', $this->unit);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    /**
     * Получение ингредиента по его идентификатору из базы данных.
     *
     * @param int $ingredientId Идентификатор ингредиента.
     * @param PDO $db Объект подключения к базе данных.
     * @return Ingredient Объект ингредиента.
     */
    public static function getById($ingredientId, $db)
    {
        $query = "SELECT * FROM ingredients WHERE id = :id";
        $stmt  = $db->prepare($query);
        $stmt->bindParam(':id', $ingredientId);
        $stmt->execute();
        $ingredientData = $stmt->fetch(PDO::FETCH_ASSOC);

        $ingredient = new Ingredient($db);
        $ingredient->setId($ingredientData['id']);
        $ingredient->setName($ingredientData['name']);
        $ingredient->setUnit($ingredientData['unit']);

        return $ingredient;
    }

    /**
     * Удаление ингредиента из базы данных.
     */
    public function delete()
    {
        $query = "DELETE FROM ingredients WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    /**
     * Удаление нескольких ингредиентов из базы данных.
     */
    public static function deleteById($ingredientId, $db)
    {
        $ingredient = Ingredient::getById($ingredientId, $db);

        // Удаление связей с рецептами
        $query = "DELETE FROM recipe_ingredients WHERE ingredient_id = :ingredientId";
        $stmt  = $db->prepare($query);
        $stmt->bindParam(':ingredientId', $ingredientId);
        $stmt->execute();

        // Удаление ингредиента из БД
        $ingredient->delete();
    }

    /**
     * Метод для сериализации объекта в JSON.
     * @return array Данные для сериализации.
     */
    public function jsonSerialize()
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'unit' => $this->unit,
        ];
    }
}
