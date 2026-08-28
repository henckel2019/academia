<?php
// app/models/Frequencia.php
class Frequencia extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'frequencias');
    }
}
