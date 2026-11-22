<?php

class AdminUserController extends AdminBaseController
{
    public function index(): void
    {
        $users = (new User())->list(true);
        $this->renderAdmin('admin/users/index', ['title' => 'Пользователи', 'users' => $users]);
    }

    public function show(int $id): void
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $this->renderAdmin('admin/users/show', ['title' => 'Пользователь', 'user' => $user]);
    }

    public function updateRoleAndStatus(int $id): void
    {
        verify_csrf();

        $status = ($_POST['status'] ?? '') === 'blocked' ? 'blocked' : 'active';
        $role = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'customer';

        (new User())->updateRoleAndStatus($id, $role, $status);
        flash('main', 'Данные пользователя сохранены.');

        redirect('/admin/users/' . $id);
    }

    public function delete(int $id): void
    {
        verify_csrf();
        (new User())->softDelete($id);
        flash('main', 'Пользователь удален.');
        redirect('/admin/users');
    }

    public function restore(int $id): void
    {
        verify_csrf();
        (new User())->restore($id);
        flash('main', 'Пользователь восстановлен.');
        redirect('/admin/users');
    }
}