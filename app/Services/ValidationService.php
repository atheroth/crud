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
        $allowedRoles = ['admin', 'editor', 'viewer', 'developer', 'manager'];
        $this->addError('role', Rules::required($value));
        $this->addError('role', !in_array($value, $allowedRoles) ? 'Недопустимая роль.' : null);
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
