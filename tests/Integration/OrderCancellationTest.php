<?php

use PHPUnit\Framework\TestCase;

// Все тесты ниже интеграционные: вызываем настоящую модель Order и проверяем данные в базе.
// Для проверки создаём временные таблицы, поэтому существующие товары и заказы не меняются.
class OrderCancellationTest extends TestCase
{
    private $db;
    private $order;

    // PHPUnit запускает этот метод перед каждым тестом, чтобы подготовить данные заново.
    protected function setUp(): void
    {
        $this->db = db();

        // Копируем только структуру таблиц. В этом соединении модель будет работать с их временными копиями.
        foreach (['products', 'orders', 'order_items'] as $table) {
            $this->db->exec("CREATE TEMPORARY TABLE test_{$table} LIKE {$table}");
            $this->db->exec("ALTER TABLE test_{$table} RENAME TO {$table}");
        }

        // Останавливаем время в соединении с базой, чтобы во время теста срок отмены не сдвигался.
        $this->db->exec('SET timestamp = 1700000000');

        // На складе осталось 5 столов, ещё 2 находятся в заказе. После отмены должно стать 7.
        $this->db->exec("INSERT INTO products
            (id, category_id, name, slug, sku, price, stock, created_at, updated_at)
            VALUES (1, 1, 'Test table', 'test-table', 'TEST', 100, 5, NOW(), NOW())");

        // Заказу 14 минут 59 секунд, поэтому отмена ещё разрешена.
        $this->db->exec("INSERT INTO orders
            (id, user_id, status, customer_name, customer_phone, customer_email,
             delivery_address, payment_method, delivery_method, total_amount, created_at, updated_at)
            VALUES (1, 1, 'new', 'Test', '0000000000', 'test@example.invalid',
                    'Test address', 'cash', 'pickup', 200,
                    DATE_SUB(NOW(), INTERVAL 899 SECOND), NOW())");

        $this->db->exec("INSERT INTO order_items
            (order_id, product_id, product_name, price, quantity, total, created_at)
            VALUES (1, 1, 'Test table', 100, 2, 200, NOW())");

        $this->order = new Order();
    }

    // После каждого теста удаляем временные таблицы и возвращаем обычный ход времени.
    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        foreach (['order_items', 'orders', 'products'] as $table) {
            $this->db->exec("DROP TEMPORARY TABLE IF EXISTS {$table}");
            $this->db->exec("DROP TEMPORARY TABLE IF EXISTS test_{$table}");
        }
        $this->db->exec('SET timestamp = 0');
    }

    // Интеграционный тест: отменяем заказ до истечения 15 минут.
    // Проверяем статус и возврат 2 столов на склад. Затем пробуем отменить тот же заказ ещё раз.
    public function testCancelBeforeDeadline(): void
    {
        $this->assertTrue($this->order->cancelByUser(1, 1));
        $this->assertSame('cancelled', $this->db->query('SELECT status FROM orders WHERE id=1')->fetchColumn());
        $this->assertEquals(7, $this->db->query('SELECT stock FROM products WHERE id=1')->fetchColumn());

        // Повторная отмена не должна ещё раз вернуть товар на склад.
        $this->assertFalse($this->order->cancelByUser(1, 1));
        $this->assertEquals(7, $this->db->query('SELECT stock FROM products WHERE id=1')->fetchColumn());
    }

    // Интеграционный тест: пробуем отменить заказ ровно через 15 минут после создания.
    // Отмена должна быть запрещена, а статус заказа и остаток товара должны остаться прежними.
    public function testCannotCancelAtDeadline(): void
    {
        $this->db->exec('UPDATE orders SET created_at = DATE_SUB(NOW(), INTERVAL 900 SECOND) WHERE id=1');

        $this->assertFalse($this->order->cancelByUser(1, 1));
        $this->assertSame('new', $this->db->query('SELECT status FROM orders WHERE id=1')->fetchColumn());
        $this->assertEquals(5, $this->db->query('SELECT stock FROM products WHERE id=1')->fetchColumn());
    }

    // Интеграционный тест: пробуем отменить заказ через 15 минут 30 секунд после создания.
    // Проверяем, что заказ не отменился и товар не вернулся на склад.
    public function testCannotCancelAfterDeadline(): void
    {
        $this->db->exec('UPDATE orders SET created_at = DATE_SUB(NOW(), INTERVAL 930 SECOND) WHERE id=1');

        $this->assertFalse($this->order->cancelByUser(1, 1));
        $this->assertSame('new', $this->db->query('SELECT status FROM orders WHERE id=1')->fetchColumn());
        $this->assertEquals(5, $this->db->query('SELECT stock FROM products WHERE id=1')->fetchColumn());
    }
}
