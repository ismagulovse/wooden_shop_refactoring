<?php

class ProfileController extends BaseController
{
    public function index(): void
    {
        require_auth();

        $userModel = new User();
        $user = $userModel->findById((int) user_id());
        $orders = (new Order())->listByUser((int) user_id());
        $favoriteCount = (new Favorite())->count((int) user_id());

        $this->render('profile/index', [
            'title' => 'Личный кабинет',
            'user' => $user,
            'orders' => $orders,
            'favoriteCount' => $favoriteCount,
        ]);
    }

    public function updateProfile(): void
    {
        verify_csrf();
        require_auth();

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        if ($data['first_name'] === '' || $data['last_name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            flash('main', 'Проверьте данные профиля.', 'error');
            redirect('/profile');
        }

        $userModel = new User();
        $existing = $userModel->findByEmail($data['email']);
        if ($existing && (int) $existing['id'] !== (int) user_id()) {
            flash('main', 'Такой email уже используется.', 'error');
            redirect('/profile');
        }

        $userModel->updateProfile((int) user_id(), $data);

        $_SESSION['user']['first_name'] = $data['first_name'];
        $_SESSION['user']['last_name'] = $data['last_name'];
        $_SESSION['user']['email'] = $data['email'];

        flash('main', 'Профиль обновлен.');
        redirect('/profile');
    }

    public function updatePassword(): void
    {
        verify_csrf();
        require_auth();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';

        if (mb_strlen($new) < 8 || $new !== $confirm) {
            flash('main', 'Проверьте новый пароль.', 'error');
            redirect('/profile');
        }

        $userModel = new User();
        $user = $userModel->findById((int) user_id());
        if (!$user || !password_verify($current, $user['password_hash'])) {
            flash('main', 'Текущий пароль неверный.', 'error');
            redirect('/profile');
        }

        $userModel->updatePassword((int) user_id(), password_hash($new, PASSWORD_DEFAULT));
        flash('main', 'Пароль обновлен.');
        redirect('/profile');
    }

    public function cancelOrder(int $id): void
    {
        verify_csrf();
        require_auth();

        $ok = (new Order())->cancelByUser($id, (int) user_id());
        if ($ok) {
            flash('main', 'Заказ успешно отменён.');
        } else {
            flash('main', 'Отменить можно только новый заказ в течение 15 минут.', 'error');
        }

        redirect('/profile');
    }
}
