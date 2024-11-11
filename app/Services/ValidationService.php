<?php

namespace App\Services;

class ValidationService
{
    private $errors = [];

    public function validateFullName($value)
    {
        $this->addError('full_name', Rules::required($value));
        $this->addError('full_name', Rules::maxLength($value, 50));
        $this->addError('full_name', Rules::regex($value, '/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', 'Полное имя должно содержать только буквы, пробелы и дефисы.'));
    }

    public function validateRole($value)
    {
        $this->addError('role', Rules::required($value)); // Проверяем, что значение указано.

        // Разрешаем буквы, цифры и пробелы, но не допускаем строки, состоящие только из цифр.
        if (!preg_match('/^(?!\d+$)[a-zA-Z0-9\s]+$/u', $value)) {
            $this->addError('role', 'Роль может содержать только буквы, цифры и пробелы, но не состоять из одних цифр.');
        }
    }


    public function validateEfficiency($value)
    {
        $this->addError('efficiency', Rules::required($value));
        $this->addError('efficiency', Rules::numeric($value));
        $this->addError('efficiency', Rules::between($value, 0, 100));
    }

    public function validate($data)
    {
        $this->validateFullName($data['full_name'] ?? null);
        $this->validateRole($data['role'] ?? null);
        $this->validateEfficiency($data['efficiency'] ?? null);

        return $this->errors;
    }

    private function addError($field, $error)
    {
        if ($error) {
            $this->errors[$field][] = $error;
        }
    }
}
