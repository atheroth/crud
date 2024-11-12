<?php

namespace App\Models;

use PDO;

class User
{
    private $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $dbConfig = $config['database'];

        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
        $this->db = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Получить всех пользователей
    public function getAll()
    {
        $stmt = $this->db->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получить пользователя по ID
    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Создать нового пользователя
    public function create($data)
    {
        $stmt = $this->db->prepare('INSERT INTO users (full_name, role, efficiency) VALUES (:full_name, :role, :efficiency)');
        $stmt->execute([
            'full_name' => $data['full_name'],
            'role' => $data['role'],
            'efficiency' => $data['efficiency']
        ]);
        return $this->db->lastInsertId();
    }

    // Обновить пользователя по ID
    public function update($id, $data)
    {
        $stmt = $this->db->prepare('UPDATE users SET full_name = :full_name, role = :role, efficiency = :efficiency WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'full_name' => $data['full_name'],
            'role' => $data['role'],
            'efficiency' => $data['efficiency']
        ]);
    }

    // Удалить пользователя по ID
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }


    /**
     * Получить пользователей с учётом пагинации и сортировки
     *
     * @param int $page Номер страницы
     * @param int $usersPerPage Количество пользователей на странице
     * @param string $sortBy Поле для сортировки
     * @param string $order Направление сортировки (asc/desc)
     * @return array Массив пользователей
     */
    public function getPaginated($page, $usersPerPage, $sortBy, $order)
    {
        $offset = ($page - 1) * $usersPerPage;

        // Предотвращение SQL-инъекций
        $allowedSortFields = ['id', 'full_name', 'role', 'efficiency'];
        $allowedOrder = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'asc';
        }

        // SQL-запрос
        $query = "SELECT * FROM users ORDER BY $sortBy $order LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $usersPerPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Получить общее количество пользователей
     *
     * @return int Общее количество записей
     */
    public function getTotalUsers()
    {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM users');
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    //метод добавления User в БД
    public function addUser($fullName, $role, $efficiency)
    {
        $query = "INSERT INTO users (full_name, role, efficiency) VALUES (:full_name, :role, :efficiency)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':full_name', $fullName);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':efficiency', $efficiency, \PDO::PARAM_INT);
        $stmt->execute();
    }

    // Получить пользователя по имени
    public function getByName($name)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE full_name = :full_name');
        $stmt->execute(['full_name' => $name]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }



    public function getFilteredAndSorted(array $filters, $sortBy, $order)
    {
        $query = "SELECT * FROM users WHERE 1=1"; // Базовый запрос

        $params = [];

        // Добавление фильтрации
        if (!empty($filters['role'])) {
            $query .= " AND role = :role";
            $params['role'] = $filters['role'];
        }

        // Добавление сортировки
        $query .= " ORDER BY $sortBy $order";

        // Подготовка и выполнение запроса
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Проверка существования записи по ID
    public function exists($id)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0; // Вернет true, если запись существует
    }

    //Метод для очистки пользователей 
    public function deleteAll()
    {
        $sql = "DELETE FROM users";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
