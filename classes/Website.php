<?php

class Website {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllWebsites() {
        $stmt = $this->pdo->query('SELECT * FROM websites ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function getWebsiteById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM websites WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getWebsiteByApiKey($api_key) {
        $stmt = $this->pdo->prepare('SELECT * FROM websites WHERE api_key = ?');
        $stmt->execute([$api_key]);
        return $stmt->fetch();
    }

    public function getTotalCount() {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM websites');
        return $stmt->fetchColumn();
    }

    public function getActiveCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM websites WHERE status = 'active'");
        return $stmt->fetchColumn();
    }

    public function getInactiveCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM websites WHERE status = 'inactive'");
        return $stmt->fetchColumn();
    }

    public function addWebsite($name, $domain, $daily_limit, $status) {
        $api_key = bin2hex(random_bytes(16)); // Generate simple random API key
        $stmt = $this->pdo->prepare('INSERT INTO websites (name, domain, api_key, daily_limit, status) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$name, $domain, $api_key, $daily_limit, $status]);
    }

    public function updateWebsite($id, $name, $domain, $daily_limit, $status) {
        $stmt = $this->pdo->prepare('UPDATE websites SET name = ?, domain = ?, daily_limit = ?, status = ? WHERE id = ?');
        return $stmt->execute([$name, $domain, $daily_limit, $status, $id]);
    }

    public function deleteWebsite($id) {
        $stmt = $this->pdo->prepare('DELETE FROM websites WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function toggleStatus($id, $new_status) {
        $stmt = $this->pdo->prepare('UPDATE websites SET status = ? WHERE id = ?');
        return $stmt->execute([$new_status, $id]);
    }
}
?>