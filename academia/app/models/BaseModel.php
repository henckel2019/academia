<?php

// app/models/BaseModel.php
class BaseModel
{
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function all(string $order = 'id ASC'): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY {$order}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn ($f) => ":{$f}", $fields);
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ") RETURNING {$this->primaryKey}";
        $stmt = $this->pdo->prepare($sql);
        $this->executeStatement($stmt, $data);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool
    {
        $fields = array_keys($data);
        $set = implode(',', array_map(fn ($f) => "{$f} = :{$f}", $fields));
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        return $this->executeStatement($this->pdo->prepare($sql), $data);
    }

    private function executeStatement(PDOStatement $statement, array $data): bool
    {
        foreach ($data as $key => $value) {
            $type = match (true) {
                is_bool($value) => PDO::PARAM_BOOL,
                is_int($value) => PDO::PARAM_INT,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue(':' . $key, $value, $type);
        }
        return $statement->execute();
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id")->execute(['id' => $id]);
    }
}
