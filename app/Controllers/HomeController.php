<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Models\User;

class HomeController
{
    public function index()
    {
        // Получение параметров из запроса
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Текущая страница
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Поле для сортировки
        $order = isset($_GET['order']) ? $_GET['order'] : 'desc'; // Направление сортировки (по умолчанию - убывание)

        $usersPerPage = 10; // Количество пользователей на одной странице

        // Получаем модель пользователей
        $userModel = new User();
        $users = $userModel->getPaginated($page, $usersPerPage, $sortBy, $order);
        $totalUsers = $userModel->getTotalUsers(); // Общее количество пользователей

        $totalPages = ceil($totalUsers / $usersPerPage); // Количество страниц

        // Инициализация Twig
        $loader = new FilesystemLoader(__DIR__ . '/../../views');
        $twig = new Environment($loader);

        // Рендеринг шаблона
        echo $twig->render('home.html.twig', [
            'title' => 'Список пользователей',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }
}
