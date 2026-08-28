<?php
// app/models/Modalidade.php
class Modalidade extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'modalidades');
    }
}
