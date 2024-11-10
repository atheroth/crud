<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Models\User;

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // Получить всех пользователей
    public function show($id)
    {
        $user = $this->userModel->getById($id);

        if (!$user) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'User not found']);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'result' => $user], JSON_PRETTY_PRINT);
    }

    // Создать нового пользователя
    public function create()
    {
        $input = $_POST;

        if (!$input || !isset($input['full_name'], $input['role'], $input['efficiency'])) {
            // Установка сообщения об ошибке
            $_SESSION['alert'] = [
                'type' => 'danger',
                'message' => 'Invalid input. Please fill all fields correctly.'
            ];

            // Перенаправление обратно на форму
            header('Location: /user/add');
            exit;
        }

        $id = $this->userModel->create($input);

        // Установка сообщения об успешном добавлении
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => "User successfully added with ID: $id"
        ];

        // Перенаправление на главную страницу
        header('Location: /');
        exit;
    }

    // Обновить
    public function update($id)
    {
        // Используем данные из $_POST
        $input = $_POST;

        // Проверка: корректное тело запроса
        if (!$input || !is_array($input)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            return;
        }

        // Разрешённые поля для обновления
        $allowedFields = ['full_name', 'role', 'efficiency'];
        $updateFields = [];

        // Отбор переданных и разрешённых полей
        foreach ($allowedFields as $field) {
            if (isset($input[$field]) && $input[$field] !== '') {
                $updateFields[$field] = $field === 'efficiency'
                    ? (int)$input[$field]
                    : htmlspecialchars(trim($input[$field]));
            }
        }

        // Если нет полей для обновления, возвращаем ошибку
        if (empty($updateFields)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No valid fields to update']);
            return;
        }

        // Обновляем запись в базе данных
        $updated = $this->userModel->update($id, $updateFields);

        // Проверка результата обновления
        if (!$updated) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'User not found or update failed']);
            return;
        }

        // Перенаправление на главную страницу
        header('Location: /');
        exit;
    }



    public function showAddForm()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../views');
        $twig = new Environment($loader);

        echo $twig->render('add_user.html.twig', [
            'title' => 'Добавить пользователя',
        ]);
    }

    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Получение данных из POST-запроса
        $fullName = $_POST['full_name'] ?? null;
        $role = $_POST['role'] ?? null;
        $efficiency = $_POST['efficiency'] ?? null;

        // Валидация данных
        if (!$fullName || !$role || !$efficiency || !is_numeric($efficiency)) {
            http_response_code(400);
            echo "Некорректные данные. Убедитесь, что все поля заполнены.";
            return;
        }

        // Сохранение в базе данных
        $userModel = new \App\Models\User();
        $userModel->addUser($fullName, $role, (int)$efficiency);

        // Перенаправление или сообщение об успешном добавлении
        header('Location: /');
    }

    // Удалить пользователя
    public function delete($id)
    {
        $deleted = $this->userModel->delete($id);

        if (!$deleted) {
            http_response_code(404);
            echo "Пользователь не найден.";
            return;
        }

        header('Location: /');
        exit;
    }


    public function showEditForm($id)
    {
        $user = $this->userModel->getById($id);

        if (!$user) {
            http_response_code(404);
            echo "User not found";
            return;
        }

        // Инициализация Twig
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../views');
        $twig = new \Twig\Environment($loader);

        // Рендеринг формы редактирования
        echo $twig->render('edit_user.html.twig', [
            'title' => 'Редактировать пользователя',
            'user' => $user
        ]);
    }
}
