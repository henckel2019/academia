<?php
// app/models/Aluno.php
class Aluno extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'alunos');
    }
}
