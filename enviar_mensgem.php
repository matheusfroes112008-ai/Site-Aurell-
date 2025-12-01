<?php
// enviar_mensagem.php

// Dados de conexão
$host = "localhost";
$user = "root";
$password = "";
$database = "aurelle";

// Conectar ao banco
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $nome = trim($_POST['nome']) . ' ' . trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone'] ?? '');
    $assunto = trim($_POST['assunto']);
    $mensagem = trim($_POST['mensagem']);
    
    // Validar campos obrigatórios
    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        echo "<script>
            alert('Por favor, preencha todos os campos obrigatórios.');
            window.history.back();
        </script>";
        exit;
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
            alert('Por favor, insira um e-mail válido.');
            window.history.back();
        </script>";
        exit;
    }
    
    // Inserir no banco - usando a estrutura da sua tabela
    $sql = "INSERT INTO mensagens_contato (nome, email, telefone, assunto, mensagem, lida) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssss", $nome, $email, $telefone, $assunto, $mensagem);
        
        if ($stmt->execute()) {
            // Sucesso
            echo "<script>
                alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
                window.location.href = 'contato.html';
            </script>";
        } else {
            // Erro na execução
            echo "<script>
                alert('Erro ao enviar mensagem: " . addslashes($stmt->error) . "');
                window.history.back();
            </script>";
        }
        
        $stmt->close();
    } else {
        // Erro na preparação
        echo "<script>
            alert('Erro no sistema: " . addslashes($conn->error) . "');
            window.history.back();
        </script>";
    }
} else {
    // Se acessado diretamente, voltar para contato
    header("Location: contato.html");
    exit;
}

$conn->close();
?>