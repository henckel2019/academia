<?php
// app/models/Turma.php
class Turma extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'turmas');
    }
}
