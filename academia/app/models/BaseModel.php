<?php
// app/models/BaseModel.php
class BaseModel {
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(PDO $pdo, string $table) {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function all(string $order = 'id ASC'): array {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY {$order}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":{$f}", $fields);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = array_keys($data);
        $set = implode(',', array_map(fn($f) => "{$f} = :{$f}", $fields));
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        return $this->pdo->prepare($sql)->execute($data);
    }

    public function delete(int $id): bool {
        return $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id")->execute(['id' => $id]);
    }
}
