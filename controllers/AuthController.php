<?php

class AuthController extends BaseController
{
    public function showRegister(): void
    {
        if (is_logged_in()) {
            redirect('/profile');
        }

        $this->render('auth/register', ['title' => 'Регистрация']);
    }

    public function register(): void
    {
        verify_csrf();

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
        ];

        $errors = [];

        if ($data['first_name'] === '' || $data['last_name'] === '') {
            $errors[] = 'Укажите имя и фамилию.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email.';
        }
        if (!preg_match('/^[0-9\+\-\(\)\s]{8,20}$/', $data['phone'])) {
            $errors[] = 'Некорректный телефон.';
        }
        if (mb_strlen($data['password']) < 8) {
            $errors[] = 'Пароль должен быть не короче 8 символов.';
        }
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Пароли не совпадают.';
        }

        $userModel = new User();
        if ($userModel->findByEmail($data['email'])) {
            $errors[] = 'Пользователь с таким email уже существует.';
        }

        if ($errors) {
            flash('auth', implode(' ', $errors), 'error');
            redirect('/register');
        }

        $userId = $userModel->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        $user = $userModel->findById($userId);
        $this->setUserSession($user);

        flash('main', 'Регистрация успешна. Добро пожаловать в Wooden!');
        redirect('/profile');
    }

    public function showLogin(): void
    {
        if (is_logged_in()) {
            redirect('/profile');
        }

        $this->render('auth/login', ['title' => 'Вход']);
    }

    public function login(): void
    {
        verify_csrf();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('auth', 'Введите корректные учетные данные.', 'error');
            redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('auth', 'Неверный email или пароль.', 'error');
            redirect('/login');
        }

        if ($user['deleted_at'] !== null || $user['status'] !== 'active') {
            flash('auth', 'Учетная запись недоступна.', 'error');
            redirect('/login');
        }

        $this->setUserSession($user);

        flash('main', 'Вы успешно вошли.');
        redirect('/profile');
    }

    public function logout(): void
    {
        verify_csrf();
        unset($_SESSION['user']);
        flash('main', 'Вы вышли из аккаунта.');
        redirect('/');
    }

    private function setUserSession(array $user): void
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status'],
        ];
    }
}

