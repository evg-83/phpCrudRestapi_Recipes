<?php

session_start();

require __DIR__ . '/../app/core/App.php';

ini_set('display_errors', 1);

// Создание экземпляра класса App, который инициализирует маршрутизацию приложения
$app = new App();

$app->loadController();