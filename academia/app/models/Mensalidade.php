<?php
// app/models/Mensalidade.php
class Mensalidade extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'mensalidades');
    }
}
