<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "aurelle";


$conn = new mysqli($host, $usuario, $senha, $banco);


if ($conn->connect_error) {
    die("❌ Falha na conexão com o banco de dados: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    die("Erro ao definir charset UTF-8: " . $conn->error);
}
?>