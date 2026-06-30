<?php

class Validator {
    private $errors = [];

    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
            return false;
        }
        return true;
    }

    public function validatePassword($password) {
        if (strlen($password) < 8) {
            $this->errors[] = 'Password must be at least 8 characters';
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[] = 'Password must contain uppercase letter';
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[] = 'Password must contain number';
            return false;
        }
        return true;
    }

    public function validatePhone($phone) {
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
            $this->errors[] = 'Invalid phone number format';
            return false;
        }
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function reset() {
        $this->errors = [];
    }
}
?>
