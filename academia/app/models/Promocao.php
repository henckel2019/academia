<?php
// app/models/Promocao.php
class Promocao extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'promocoes');
    }
}
