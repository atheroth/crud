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

    // Утилита для фильтрации полей в ответах
    private function filterFields(array $user): array
    {
        unset($user['created_at'], $user['updated_at']);
        return $user;
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

        $id = (int)$this->userModel->create($input);

        // Преобразованием id в целое число
        $id = (int) $id;

        http_response_code(201);
        echo json_encode(['success' => true, 'result' => ['id' => $id]], JSON_UNESCAPED_UNICODE);
    }

    // API получить пользователя по ID
    public function get($id)
    {
        header('Content-Type: application/json');

        if (empty($id) || !ctype_digit($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID должен быть положительным числом.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id = (int) $id;
        $user = $this->userModel->getById($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Пользователь не найден.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $user = $this->filterFields($user);

        http_response_code(200);
        echo json_encode(['success' => true, 'result' => ['user' => $user]], JSON_UNESCAPED_UNICODE);
    }

    // API UPDATE
    public function update($id)
    {
        header('Content-Type: application/json');

        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректный ID пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->userModel->exists($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Пользователь не найден.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Входные данные некорректны.'], JSON_UNESCAPED_UNICODE);
            return;
        }

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

        $updated = $this->userModel->update($id, $updateFields);

        if (!$updated) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Не удалось обновить данные пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'result' => [
                'id' => $id,
                'updated_fields' => $updateFields
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    // API получить список пользователей
    public function getAll()
    {
        header('Content-Type: application/json');

        $filters = ['role' => $_GET['role'] ?? null];
        $sortBy = $_GET['sort_by'] ?? 'id';
        $order = $_GET['order'] ?? 'asc';

        if (!in_array($order, ['asc', 'desc']) || !in_array($sortBy, ['id', 'full_name', 'role', 'efficiency'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректные параметры сортировки.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $users = $this->userModel->getFilteredAndSorted($filters, $sortBy, $order);

        if (empty($users)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Пользователи не найдены.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $filteredUsers = array_map([$this, 'filterFields'], $users);

        echo json_encode(['success' => true, 'result' => ['users' => $filteredUsers]], JSON_UNESCAPED_UNICODE);
    }

    // API DELETE
    public function delete($id)
    {
        header('Content-Type: application/json');

        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Некорректный ID пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!$this->userModel->exists($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Пользователь с указанным ID не найден.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $deletedUser = $this->userModel->getById($id);
        $deleted = $this->userModel->delete($id);

        if (!$deleted) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Не удалось удалить пользователя.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $deletedUser = $this->filterFields($deletedUser);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'result' => $deletedUser
        ], JSON_UNESCAPED_UNICODE);
    }

    // API DELETE ALL
    public function deleteAll()
    {
        header('Content-Type: application/json');

        // Удаляем всех пользователей
        $deleted = $this->userModel->deleteAll();

        if (!$deleted) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Не удалось удалить пользователей.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Успешное удаление
        http_response_code(200);
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    }
}
