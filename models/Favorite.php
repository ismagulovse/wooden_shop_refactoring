<?php

class Favorite extends BaseModel
{
    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT f.id as favorite_id, p.*, (SELECT image_path FROM product_images WHERE product_id=p.id AND deleted_at IS NULL ORDER BY sort_order LIMIT 1) as image FROM favorites f INNER JOIN products p ON p.id=f.product_id WHERE f.user_id=:uid AND p.deleted_at IS NULL AND p.is_active=1 ORDER BY f.created_at DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare('INSERT INTO favorites (user_id, product_id, created_at) VALUES (:uid,:pid,NOW())');
        try {
            $stmt->execute(['uid' => $userId, 'pid' => $productId]);
        } catch (PDOException $e) {
        }
    }

    public function remove(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare('DELETE FROM favorites WHERE user_id=:uid AND product_id=:pid');
        $stmt->execute(['uid' => $userId, 'pid' => $productId]);
    }

    public function count(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM favorites WHERE user_id=:uid');
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function productIdsByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT product_id FROM favorites WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll();

        return array_map(static fn(array $row): int => (int) $row['product_id'], $rows);
    }
}

