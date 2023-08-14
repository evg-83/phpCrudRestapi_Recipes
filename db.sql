-- Создание таблицы для хранения пользователей (для аутентификации и авторизации)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Создание таблицы для хранения рецептов
CREATE TABLE recipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    ingredients JSON NOT NULL,
    steps JSON NOT NULL,
    photo VARCHAR(255) NOT NULL,
    user_id INT NOT NULL, -- Идентификатор пользователя, создавшего рецепт
    FOREIGN KEY (user_id) REFERENCES users(id) -- Связь с таблицей пользователей
);

-- Создание таблицы для хранения ингредиентов
CREATE TABLE ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    unit VARCHAR(50) NOT NULL
);

-- Создание таблицы для связи между рецептами и ингредиентами
CREATE TABLE recipe_ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL, -- Количество ингредиента в рецепте
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
);
