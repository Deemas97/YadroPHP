<?php
namespace App\Controller;

use Core\Controller\ControllerRendering;
use Core\Controller\ControllerResponseInterface;
use Core\Service\Renderer;

class ErrorController extends ControllerRendering
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
        $this->renderer->enableCaching(false);
    }

    public function error400(): ControllerResponseInterface
    {
        return $this->render('error/400.html.php', [
            'title' => 'Некорректный запрос (400) | ФКРВИ Саратов',
            'description' => 'Синтаксическая ошибка в запросе',
            'active' => null
        ], 400);
    }

    public function error401(): ControllerResponseInterface
    {
        return $this->render('error/401.html.php', [
            'title' => '401 - Не авторизован',
            'description' => 'Требуется авторизация для доступа к странице',
            'active' => null
        ], 401);
    }

    public function error402(): ControllerResponseInterface
    {
        return $this->render('error/402.html.php', [
            'title' => '402 - Требуется оплата',
            'description' => 'Необходима оплата для доступа к ресурсу',
            'active' => null
        ], 402);
    }

    public function error403(): ControllerResponseInterface
    {
        return $this->render('error/403.html.php', [
            'title' => '403 - Доступ запрещен',
            'description' => 'У вас нет прав для доступа к этой странице',
            'active' => null
        ], 403);
    }

    public function error404(): ControllerResponseInterface
    {
        return $this->render('error/404.html.php', [
            'title' => 'Страница не найдена (404) | ФКРВИ Саратов',
            'description' => 'Запрашиваемая страница не существует',
            'active' => null
        ], 404);
    }

    public function error405(): ControllerResponseInterface
    {
        return $this->render('error/405.html.php', [
            'title' => '405 - Метод не разрешен',
            'description' => 'Используемый метод запроса не поддерживается',
            'active' => null
        ], 405);
    }

    public function error406(): ControllerResponseInterface
    {
        return $this->render('error/406.html.php', [
            'title' => '406 - Неприемлемый ответ',
            'description' => 'Сервер не может найти ответ, соответствующий требованиям',
            'active' => null
        ], 406);
    }

    public function error414(): ControllerResponseInterface
    {
        return $this->render('error/414.html.php', [
            'title' => '414 - URI слишком длинный',
            'description' => 'Запрашиваемый URI превышает допустимую длину',
            'active' => null
        ], 414);
    }

    public function error500(): ControllerResponseInterface
    {
        return $this->render('error/500.html.php', [
            'title' => 'Ошибка сервера (500) | ФКРВИ Саратов',
            'description' => 'На сервере произошла непредвиденная ошибка',
            'active' => null
        ], 500);
    }
}