<?php

/**
 * Class _404Controller
 * Контроллер для обработки случая, когда запрашиваемая страница не найдена.
 */
class _404Controller
{
    /**
     * Метод для обработки случая, когда запрашиваемая страница не найдена.
     * Выводит JSON-ответ с кодом ошибки 404 и сообщением "Страница не найдена".
     */
    public function index()
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Страница не найдена"]);
    }
}
