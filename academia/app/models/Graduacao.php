<?php
// app/models/Graduacao.php
class Graduacao extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'graduacoes');
    }
}
