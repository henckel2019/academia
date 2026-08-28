<?php
// app/models/Usuario.php
class Usuario extends BaseModel {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'usuarios');
    }
}
