<?php
session_start();
require_once('config.php');

$mensagem = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verificar token
    $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE token_reset = ? AND token_expira > NOW()");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado && $resultado->num_rows > 0) {
            $admin = $resultado->fetch_assoc();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $novaSenha = $_POST['nova_senha'] ?? '';
                $confirmarSenha = $_POST['confirmar_senha'] ?? '';
                
                if (empty($novaSenha) || empty($confirmarSenha)) {
                    $mensagem = "<div class='alert error'>❌ Preencha todos os campos.</div>";
                } elseif ($novaSenha !== $confirmarSenha) {
                    $mensagem = "<div class='alert error'>❌ As senhas não correspondem.</div>";
                } elseif (strlen($novaSenha) < 8) {
                    $mensagem = "<div class='alert error'>❌ A senha deve ter no mínimo 8 caracteres.</div>";
                } else {
                    // Atualizar senha
                    $hashNova = password_hash($novaSenha, PASSWORD_DEFAULT);
                    $atualizarSenha = $conn->prepare("UPDATE admin SET senha = ?, token_reset = NULL, token_expira = NULL WHERE id_admin = ?");
                    
                    if ($atualizarSenha) {
                        $atualizarSenha->bind_param("si", $hashNova, $admin['id_admin']);
                        if ($atualizarSenha->execute()) {
                            $mensagem = "<div class='alert success'>✅ Senha atualizada com sucesso! <a href='admin_login.php' class='underline'>Clique aqui para fazer login</a>.</div>";
                        } else {
                            $mensagem = "<div class='alert error'>❌ Erro ao atualizar senha. Tente novamente.</div>";
                        }
                        $atualizarSenha->close();
                    }
                }
            }
        } else {
            $mensagem = "<div class='alert error'>❌ Link de redefinição inválido ou expirado.</div>";
        }
        $stmt->close();
    }
} else {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha - AURELLÉ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Montserrat:wght@400;500&display=swap');
        :root { --gold-light: #d9b24d; --gold-hover: #c49a38; }
        body { font-family: 'Montserrat', sans-serif; background-color: #fafafa; }
        .alert { padding: 0.75rem; border-radius: 0.5rem; text-align: center; margin-bottom: 1rem; font-weight: 500; }
        .alert.error { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert.success { background-color: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
        .title-font { font-family: 'Playfair Display', serif; color: #000000 !important; }
        .text-gold { color: var(--gold-light); }
        .bg-gold { background-color: var(--gold-light); }
        .bg-gold:hover { background-color: var(--gold-hover); }
        .login-container { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding-top: 6rem; background-color: #f9fafb; }
    </style>
</head>
<body class="bg-gray-50">
    <header class="fixed w-full bg-white shadow-md z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="index.html" class="title-font text-2xl font-bold text-black">AURELLÉ</a>
            <nav class="hidden md:flex space-x-8">
                <a href="index.html" class="text-gray-700 hover:text-gold font-medium">Início</a>
                <a href="colecoes.html" class="text-gray-700 hover:text-gold font-medium">Coleções</a>
                <a href="relogios.html" class="text-gray-700 hover:text-gold font-medium">Relógios</a>
                <a href="sobre.html" class="text-gray-700 hover:text-gold font-medium">Sobre Nós</a>
                <a href="contato.html" class="text-gray-700 hover:text-gold font-medium">Contato</a>
            </nav>
            <div class="flex items-center space-x-4">
                <a href="produtos.php" class="text-gold"><i class="fas fa-user-shield"></i></a>
            </div>
        </div>
    </header>

    <div class="login-container px-4">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-gold mb-6 text-center">
                <i class="fas fa-key mr-2 text-gold"></i>Redefinir Senha
            </h2>

            <?= $mensagem ?>

            <?php if (!str_contains($mensagem, 'sucesso')): ?>
            <form method="POST" class="space-y-4" novalidate>
                <div>
                    <input type="password" name="nova_senha" placeholder="Nova senha" required
                        class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]">
                    <p class="text-sm text-gray-500 mt-1">Mínimo de 8 caracteres</p>
                </div>
                
                <input type="password" name="confirmar_senha" placeholder="Confirmar nova senha" required
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]">
                
                <button type="submit"
                    class="w-full bg-gold hover:bg-[var(--gold-hover)] text-white p-3 rounded font-semibold transition">
                    Salvar Nova Senha
                </button>
            </form>
            <?php endif; ?>

            <p class="text-center text-gray-600 mt-4">
                <a href="admin_login.php" class="text-gold font-semibold hover:underline">← Voltar ao login</a>
            </p>
        </div>
    </div>
</body>
</html>