<?php
// app/models/Matricula.php
class Matricula extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'matriculas');
    }
}
