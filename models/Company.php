<?php
class Company
{
    public function __construct(private PDO $db) {}

    public function create(int $userId, string $name, string $address, string $contactPerson, string $contactEmail): int
    {
        $stmt = $this->db->prepare('INSERT INTO partner_companies (user_id, name, address, contact_person, contact_email) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $name, $address, $contactPerson, strtolower(trim($contactEmail))]);
        return (int)$this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query('SELECT pc.*, u.id user_id_key, u.email, u.is_active FROM partner_companies pc JOIN users u ON u.id = pc.user_id ORDER BY pc.name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT pc.*, u.email user_email FROM partner_companies pc JOIN users u ON u.id = pc.user_id WHERE pc.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM partner_companies WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function count(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM partner_companies')->fetchColumn();
    }
}
