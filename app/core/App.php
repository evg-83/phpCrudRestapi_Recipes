<?php

/**
 * Класс для загрузки и выполнения контроллеров и методов веб-приложения.
 */
class App
{
    private $controller = 'AuthController';
    private $method     = '';

    /**
     * Разбивает URL на отдельные сегменты.
     *
     * @return array Массив с сегментами URL.
     */
    private function splitUrl()
    {
        // Получаем URL из GET-параметра 'url' или используем 'auth' по умолчанию
        $URL = $_GET['url'] ?? 'auth';

        // Разбиваем URL на сегменты
        $URL = explode('/', trim($URL, '/'));

        return $URL;
    }

    /**
     * Загружает контроллер и вызывает соответствующий метод.
     *
     * @return void
     */
    public function loadController()
    {
        // Получаем сегменты URL
        $URL = $this->splitUrl();
        
        // Формируем имя файла для подключения контроллера
        $filename = '../app/Controllers/' . ucfirst($URL[0]) . 'Controller.php';
        
        if (file_exists($filename)) {
            // Подключаем файл контроллера
            require $filename;

            // Устанавливаем имя контроллера
            $this->controller = ucfirst($URL[0]) . 'Controller';

            // Удаляем первый сегмент (имя контроллера) из массива
            unset($URL[0]);
        } else {
            $filename = '../app/Controllers/_404Controller.php';

            require $filename;
            
            $this->controller = '_404Controller';
        }

        // Создаем экземпляр контроллера
        $controller = new $this->controller;

        
        if (!empty($URL[1])) {
            if (method_exists($controller, $URL[1])) {
                // Если указан метод в URL, устанавливаем его
                $this->method = $URL[1];
                // Удаляем второй сегмент (имя метода) из массива
                unset($URL[1]);
            }
        }

        // Вызываем метод контроллера с передачей оставшихся сегментов URL в качестве аргументов
        call_user_func_array([$controller, $this->method], $URL);
        
    }
}
