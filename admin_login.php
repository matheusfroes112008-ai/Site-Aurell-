<?php


session_start();
require_once('config.php'); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = "";
$mostrarFormRedef = false;


function enviarEmailRedefinicao($email, $token) {
   
    return true;
}


if (isset($_POST['acao']) && $_POST['acao'] === 'redefinir') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $mensagem = "<div class='alert error'>❌ Digite seu e-mail.</div>";
    } else {
        
        $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado && $resultado->num_rows > 0) {
                $token = bin2hex(random_bytes(32)); 
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour')); 
                
                
                $salvarToken = $conn->prepare("UPDATE admin SET token_reset = ?, token_expira = ? WHERE email = ?");
                if ($salvarToken) {
                    $salvarToken->bind_param("sss", $token, $expira, $email);
                    if ($salvarToken->execute() && enviarEmailRedefinicao($email, $token)) {
                        $mensagem = "<div class='alert success'>✅ Link de redefinição enviado para seu e-mail!</div>";
                    } else {
                        $mensagem = "<div class='alert error'>❌ Erro ao processar solicitação. Tente novamente.</div>";
                    }
                    $salvarToken->close();
                }
            } else {
                $mensagem = "<div class='alert error'>❌ E-mail não encontrado.</div>";
            }
            $stmt->close();
        }
    }
    $mostrarFormRedef = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    if ($usuario === "" || $senha === "") {
        $mensagem = "<div class='alert error'>❌ Preencha usuário e senha.</div>";
    } else {
        $sql = "SELECT * FROM admin WHERE usuario = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado && $resultado->num_rows > 0) {
                $admin = $resultado->fetch_assoc();
                $hashNoBanco = $admin['senha'];

                
                if (password_verify($senha, $hashNoBanco)) {
                    $_SESSION["admin_id"] = $admin["id_admin"];
                    $_SESSION["admin_nome"] = $admin["usuario"];
                    header("Location: painel.php");
                    exit;
                } elseif ($senha === $hashNoBanco) {
                    
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $up = $conn->prepare("UPDATE admin SET senha = ? WHERE id_admin = ?");
                    if ($up) {
                        $up->bind_param("si", $novoHash, $admin['id_admin']);
                        $up->execute();
                        $up->close();
                    }
                    $_SESSION["admin_id"] = $admin["id_admin"];
                    $_SESSION["admin_nome"] = $admin["usuario"];
                    header("Location: produtos.php");
                    exit;
                } else {
                    $mensagem = "<div class='alert error'>❌ Senha incorreta!</div>";
                }
            } else {
                $mensagem = "<div class='alert error'>❌ Administrador não encontrado!</div>";
            }
            $stmt->close();
        } else {
            $mensagem = "<div class='alert error'>❌ Erro no servidor. Tente novamente.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login Administrativo - AURELLÉ</title>
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
.hidden { display: none !important; }
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
            <i class="fas fa-crown mr-2 text-gold"></i>Login Administrativo
        </h2>

        <?= $mensagem ?>

        <?php if ($mostrarFormRedef): ?>
            <!-- Formulário de Redefinição de Senha -->
            <form method="POST" class="space-y-4" novalidate>
                <input type="email" name="email" placeholder="Seu e-mail cadastrado" required
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <input type="hidden" name="acao" value="redefinir">
                <button type="submit"
                    class="w-full bg-gold hover:bg-[var(--gold-hover)] text-white p-3 rounded font-semibold transition">
                    Enviar Link de Redefinição
                </button>
                <button type="button" onclick="window.location.href='admin_login.php'"
                    class="w-full border border-gold text-gold hover:bg-gold hover:text-white p-3 rounded font-semibold transition mt-2">
                    Voltar ao Login
                </button>
            </form>
        <?php else: ?>
            <!-- Formulário de Login -->
            <form method="POST" class="space-y-4" novalidate>
                <input type="text" name="usuario" placeholder="Usuário do administrador" required
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]"
                    value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                <input type="password" name="senha" placeholder="Senha" required
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-[var(--gold-light)]">
                <button type="submit"
                    class="w-full bg-gold hover:bg-[var(--gold-hover)] text-white p-3 rounded font-semibold transition">
                    Entrar
                </button>
            </form>

            <div class="text-center text-gray-600 mt-4 space-y-2">
                <p>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('resetForm').submit();" 
                       class="text-gold font-semibold hover:underline">
                       Esqueci minha senha
                    </a>
                </p>
                <p>
                    <a href="login.php" class="text-gold font-semibold hover:underline">← Voltar ao login de cliente</a>
                </p>
            </div>

            <!-- Form oculto para mostrar redefinição -->
            <form id="resetForm" method="POST" class="hidden">
                <input type="hidden" name="acao" value="mostrar_redefinir">
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
