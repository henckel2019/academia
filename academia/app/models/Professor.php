<?php
// app/models/Professor.php
class Professor extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'professores');
    }
}
