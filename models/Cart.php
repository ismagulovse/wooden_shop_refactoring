<?php

class Cart extends BaseModel
{
    private function getOrCreateCartId(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT id FROM carts WHERE user_id = :uid LIMIT 1');
        $stmt->execute(['uid' => $userId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }

        $insert = $this->db->prepare('INSERT INTO carts (user_id, created_at, updated_at) VALUES (:uid, NOW(), NOW())');
        $insert->execute(['uid' => $userId]);
        return (int) $this->db->lastInsertId();
    }

    public function add(int $userId, int $productId, int $qty): void
    {
        $cartId = $this->getOrCreateCartId($userId);
        $qty = max(1, $qty);

        $stmt = $this->db->prepare('SELECT id, quantity FROM cart_items WHERE cart_id=:cart_id AND product_id=:product_id LIMIT 1');
        $stmt->execute(['cart_id' => $cartId, 'product_id' => $productId]);
        $row = $stmt->fetch();

        if ($row) {
            $up = $this->db->prepare('UPDATE cart_items SET quantity=:q,updated_at=NOW() WHERE id=:id');
            $up->execute(['q' => ((int) $row['quantity']) + $qty, 'id' => $row['id']]);
        } else {
            $ins = $this->db->prepare('INSERT INTO cart_items (cart_id,product_id,quantity,created_at,updated_at) VALUES (:cart_id,:product_id,:q,NOW(),NOW())');
            $ins->execute(['cart_id' => $cartId, 'product_id' => $productId, 'q' => $qty]);
        }

        $this->touch($cartId);
    }

    public function items(int $userId): array
    {
        $cartId = $this->getOrCreateCartId($userId);

        $stmt = $this->db->prepare('SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.price, p.stock, (SELECT image_path FROM product_images WHERE product_id=p.id AND deleted_at IS NULL ORDER BY sort_order LIMIT 1) as image FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = :cart_id AND p.deleted_at IS NULL AND p.is_active=1 ORDER BY ci.created_at DESC');
        $stmt->execute(['cart_id' => $cartId]);
        return $stmt->fetchAll();
    }

    public function updateItem(int $userId, int $itemId, int $qty): void
    {
        $cartId = $this->getOrCreateCartId($userId);
        $qty = max(1, $qty);

        $stmt = $this->db->prepare('UPDATE cart_items SET quantity=:q,updated_at=NOW() WHERE id=:id AND cart_id=:cart_id');
        $stmt->execute(['q' => $qty, 'id' => $itemId, 'cart_id' => $cartId]);

        $this->touch($cartId);
    }

    public function removeItem(int $userId, int $itemId): void
    {
        $cartId = $this->getOrCreateCartId($userId);
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE id=:id AND cart_id=:cart_id');
        $stmt->execute(['id' => $itemId, 'cart_id' => $cartId]);
        $this->touch($cartId);
    }

    public function clear(int $userId): void
    {
        $cartId = $this->getOrCreateCartId($userId);
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id=:cart_id');
        $stmt->execute(['cart_id' => $cartId]);
        $this->touch($cartId);
    }

    public function total(int $userId): float
    {
        $items = $this->items($userId);
        $sum = 0.0;
        foreach ($items as $item) {
            $sum += ((float) $item['price']) * ((int) $item['quantity']);
        }
        return $sum;
    }

    private function touch(int $cartId): void
    {
        $stmt = $this->db->prepare('UPDATE carts SET updated_at=NOW() WHERE id=:id');
        $stmt->execute(['id' => $cartId]);
    }
}

