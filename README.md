### Инструкция по запуску проекта

1. Скачивание и настройка проекта

#
Скачайте проект с GitHub в нужную папку (например, C:/OpenServer/domains/crud).

#
 Убедитесь, что в папке есть файл composer.json для установки зависимостей и все необходимые исходные файлы проекта.

2. Установка зависимостей через Composer

#
Откройте терминал (например, в Open Server нажмите "Консоль").
Перейдите в папку проекта: cd C:/OpenServer/domains/crud

# 
Установите зависимости: composer install


3. Настройка базы данных

#
Создайте базу данных в MySQL (через phpMyAdmin или вручную) с нужным именем, например, crud_db.

#
Примените структуру таблицы из файла users.sql (должен быть в проекте). Выполните этот SQL-запрос в phpMyAdmin или консоли MySQL:

CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `full_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `role` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `efficiency` int(3) NOT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;


4. Настройка конфигурации

#
Убедитесь, что файл config.php настроен для подключения к вашей базе данных:


return [
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'crud_db', // Имя вашей базы данных
        'user' => 'root',    // Пользователь MySQL
        'password' => '',    // Пароль MySQL
        'charset' => 'utf8mb4',
    ]
];

5. Запуск

Перейдите по адресу http://localhost/crud для веб версии

API доступно по адресу http://localhost/crud/api


6. Документация

#
Документация по API содержится в файле API_DOC.md

#
Файд QA.MD содержит ответы на возможные вопросы, а также тезизы, возникшие во время разработки

