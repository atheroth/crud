<?php

namespace App\Controllers;

use App\Services\ValidationService;
use App\Models\User;

class ApiUserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }
    // API Создать 
    public function create()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректный формат входных данных.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $validationService = new ValidationService();
        $errors = $validationService->validate($input);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'result' => ['error' => $errors]], JSON_UNESCAPED_UNICODE);
            return;
        }

        $existingUser = $this->userModel->getByName($input['full_name']);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(['success' => false, 'result' => ['error' => 'Пользователь с таким именем уже существует.']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id = $this->userModel->create($input);

        http_response_code(201);
        echo json_encode(['success' => true, 'result' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
    }

    // API получить пользователя по ID
    public function get($id)
    {
        header('Content-Type: application/json');

        // Валидация ID
        if (empty($id)) {
            http_response_code(400); // Код ошибки: Bad Request
            echo json_encode(['success' => false, 'error' => 'ID пользователя обязателен.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!ctype_digit($id)) {
            http_response_code(400); // Код ошибки: Bad Request
            echo json_encode(['success' => false, 'error' => 'ID должен быть положительным числом.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Преобразование ID в целое число (дополнительная защита)
        $id = (int) $id;

        // Получение пользователя из базы данных
        $user = $this->userModel->getById($id);

        if (!$user) {
            http_response_code(404); // Код ошибки: Not Found
            echo json_encode(['success' => false, 'error' => 'Пользователь не найден.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Успешный ответ
        http_response_code(200);
        echo json_encode(['success' => true, 'result' => ['user' => $user]], JSON_UNESCAPED_UNICODE);
    }


    // API UPDATE
    public function update($id)
    {
        header('Content-Type: application/json');

        // Валидация ID
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректный ID пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Проверка существования пользователя
        if (!$this->userModel->exists($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Пользователь не найден.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Получение данных из тела запроса
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Входные данные некорректны.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Допустимые поля для обновления
        $allowedFields = ['full_name', 'role', 'efficiency'];
        $updateFields = [];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[$field] = htmlspecialchars(trim($input[$field]));
            }
        }

        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Нет допустимых данных для обновления.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Обновление пользователя
        $updated = $this->userModel->update($id, $updateFields);

        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Не удалось обновить данные пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Успешное обновление
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'result' => [
                'id' => $id,
                'updated_fields' => $updateFields
            ]
        ], JSON_UNESCAPED_UNICODE);
    }


    // API /get 
    public function getAll()
    {
        header('Content-Type: application/json');

        // Получение параметров запроса
        $filters = [
            'role' => $_GET['role'] ?? null,
        ];
        $sortBy = $_GET['sort_by'] ?? 'id'; // Поле сортировки, по умолчанию 'id'
        $order = $_GET['order'] ?? 'asc';  // Направление сортировки, по умолчанию 'asc'

        // Проверка направления сортировки
        if (!in_array($order, ['asc', 'desc'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid order parameter.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Проверка поля сортировки
        $allowedSortFields = ['id', 'full_name', 'role', 'efficiency', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid sort_by parameter.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Получение пользователей с фильтрацией и сортировкой
        $users = $this->userModel->getFilteredAndSorted($filters, $sortBy, $order);

        if (empty($users)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No users found.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Возврат данных
        echo json_encode(['success' => true, 'result' => ['users' => $users]], JSON_UNESCAPED_UNICODE);
    }

    // API DELETE
    public function delete($id)
    {
        header('Content-Type: application/json');

        // Валидация ID
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректный ID пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Проверка существования пользователя
        if (!$this->userModel->exists($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Пользователь с указанным ID не найден.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Удаление пользователя
        $deletedUser = $this->userModel->getById($id); // Получаем данные пользователя перед удалением
        $deleted = $this->userModel->delete($id);

        if (!$deleted) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Не удалось удалить пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Успешное удаление
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'result' => [
                'id' => $deletedUser['id'],
                'full_name' => $deletedUser['full_name'],
                'role' => $deletedUser['role'],
                'efficiency' => $deletedUser['efficiency']
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}
