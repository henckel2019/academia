<?php
// app/models/Plano.php
class Plano extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'planos');
    }
}
