<?php

require_once __DIR__ . '/../services/OrderPricing.php';

class CheckoutController extends BaseController
{
    public function index(): void
    {
        require_auth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            verify_csrf();
            $_SESSION['checkout_form'] = array_filter($_POST, static fn($v) => is_string($v));
            unset($_SESSION['checkout_form']['_csrf']);
        }

        $cart = new Cart();
        $items = $cart->items((int) user_id());
        if (!$items) {
            flash('main', 'Корзина пуста.', 'error');
            redirect('/cart');
        }

        $user = (new User())->findById((int) user_id());
        $form = $_SESSION['checkout_form'] ?? [];
        try {
            $quote = (new \Wooden\OrderPricing())->calculate($items, $form);
        } catch (DomainException $e) {
            flash('main', $e->getMessage(), 'error');
            $quote = (new \Wooden\OrderPricing())->calculate($items, []);
        }
        $now = new DateTimeImmutable('now');
        $delivery = $now->modify('+7 days');
        $orderDateText = $now->format('d') . '.' . $now->format('m') . '.' . $now->format('Y') . ' ' . $now->format('H') . ':' . $now->format('i');
        $deliveryDateText = $delivery->format('d') . '.' . $delivery->format('m') . '.' . $delivery->format('Y') . ' ' . $delivery->format('H') . ':' . $delivery->format('i');

        $this->render('checkout/index', [
            'title' => 'Оформление заказа',
            'items' => $items,
            'total' => $quote['total'] / 100,
            'quote' => $quote,
            'form' => $form,
            'user' => $user,
            'previewOrderDateText' => $orderDateText,
            'previewDeliveryDateText' => $deliveryDateText,
            'supplier' => [
                'name' => 'Wooden Shop',
                'contact' => '+7 (900) 000-00-00',
                'email' => 'hello@wooden.local',
                'address' => 'г. Ханты-Мансийск, ул. Чехова, 16',
            ],
            'breadcrumbs' => [
                ['title' => 'Главная', 'url' => '/'],
                ['title' => 'Корзина', 'url' => '/cart'],
                ['title' => 'Оформление', 'url' => '/checkout'],
            ],
        ]);
    }

    public function place(): void
    {
        verify_csrf();
        require_auth();

        $_SESSION['checkout_form'] = array_filter($_POST, static fn($v) => is_string($v));
        unset($_SESSION['checkout_form']['_csrf']);
        $posted = $_SESSION['checkout_form'];
        $payload = [
            'customer_name' => trim($posted['customer_name'] ?? ''),
            'customer_phone' => trim($posted['customer_phone'] ?? ''),
            'customer_email' => trim($posted['customer_email'] ?? ''),
            'delivery_address' => trim($posted['delivery_address'] ?? ''),
            'comment' => trim($posted['comment'] ?? ''),
            'payment_method' => trim($posted['payment_method'] ?? ''),
            'delivery_method' => trim($posted['delivery_method'] ?? ''),
        ];

        if ($payload['customer_name'] === '' || $payload['customer_phone'] === '' || $payload['delivery_address'] === '' || !filter_var($payload['customer_email'], FILTER_VALIDATE_EMAIL) || !in_array($payload['payment_method'], ['card', 'cash'], true)) {
            flash('main', 'Проверьте корректность данных для заказа.', 'error');
            redirect('/checkout');
        }

        $cart = new Cart();
        $items = $cart->items((int) user_id());
        if (!$items) {
            flash('main', 'Корзина пуста.', 'error');
            redirect('/cart');
        }

        $orderModel = new Order();

        try {
            $orderId = $orderModel->createFromCart((int) user_id(), $payload, $_POST);
            unset($_SESSION['checkout_form']);
            flash('main', 'Заказ успешно оформлен.');
            redirect('/checkout/success/' . $orderId);
        } catch (DomainException $e) {
            flash('main', $e->getMessage(), 'error');
            redirect('/checkout');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            flash('main', 'Не удалось оформить заказ. Проверьте наличие товаров и применение миграции услуг.', 'error');
            redirect('/checkout');
        }
    }

    public function success(int $id): void
    {
        require_auth();

        $order = (new Order())->find($id);
        if (!$order || (int) $order['user_id'] !== (int) user_id()) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $orderDate = new DateTimeImmutable($order['created_at']);
        $deliveryDate = $orderDate->modify('+7 days');
        $orderDateText = $orderDate->format('d') . '.' . $orderDate->format('m') . '.' . $orderDate->format('Y') . ' ' . $orderDate->format('H') . ':' . $orderDate->format('i');
        $deliveryDateText = $deliveryDate->format('d') . '.' . $deliveryDate->format('m') . '.' . $deliveryDate->format('Y') . ' ' . $deliveryDate->format('H') . ':' . $deliveryDate->format('i');

        $this->render('checkout/success', [
            'title' => 'Чек заказа #' . $id,
            'order' => $order,
            'orderDateText' => $orderDateText,
            'deliveryDateText' => $deliveryDateText,
            'supplier' => [
                'name' => 'Wooden Shop',
                'contact' => '+7 (900) 000-00-00',
                'email' => 'hello@wooden.local',
                'address' => 'г. Ханты-Мансийск, ул. Чехова, 16',
            ],
        ]);
    }
}
