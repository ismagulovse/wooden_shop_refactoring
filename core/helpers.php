<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $key, ?string $message = null, string $type = 'success'): ?array
{
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }

    if ($message !== null) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $payload = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $payload;
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    $sessionToken = $_SESSION['_csrf'] ?? '';

    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(419);
        exit('CSRF token mismatch');
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function user_id(): ?int
{
    return $_SESSION['user']['id'] ?? null;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_auth(): void
{
    if (!is_logged_in()) {
        flash('auth', 'Войдите в аккаунт.', 'error');
        redirect('/login');
    }
}

function require_admin(): void
{
    require_auth();

    if (!is_admin()) {
        http_response_code(403);
        exit('Access denied');
    }
}

function paginate(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    return [
        'total' => $total,
        'page' => max(1, min($page, $pages)),
        'pages' => $pages,
        'per_page' => $perPage,
    ];
}

function money_format_local(float $amount): string
{
    return number_format($amount, 0, '.', ' ') . ' ₽';
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-zа-я0-9]+/ui', '-', $text);
    $text = trim((string) $text, '-');
    return $text ?: bin2hex(random_bytes(4));
}

function translate_order_status(string $status): string
{
    if ($status === 'new') {
        return 'Ожидает';
    } elseif ($status === 'processing') {
        return 'В обработке';
    } elseif ($status === 'confirmed') {
        return 'Подтвержден';
    } elseif ($status === 'shipped') {
        return 'Передан в доставку';
    } elseif ($status === 'delivered') {
        return 'Выполнен';
    } elseif ($status === 'cancelled') {
        return 'Отменен';
    }
    return $status;
}

function translate_payment_method(string $value): string
{
    return [
        'card' => 'Онлайн-оплата картой',
        'cash' => 'Наличными при получении',
    ][$value] ?? $value;
}

function translate_delivery_method(string $value): string
{
    return [
        'courier' => 'Курьерская доставка',
        'freight' => 'Грузовая доставка',
        'pickup' => 'Самовывоз',
    ][$value] ?? $value;
}
