<?php
session_start();

require_once 'config/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['nome_negocio'] = $usuario['nome_negocio'];

            header("Location: dashboard.php");
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body class="login-body">

    <div class="login-wrapper">
        <div class="login-card">

            <div class="login-logo">
                <div class="login-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <h1>Controle Financeiro</h1>
                <p>Organize suas entradas, gastos e lucro mensal de forma simples.</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger small">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control form-control-lg" 
                        placeholder="Digite seu e-mail"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input 
                        type="password" 
                        name="senha" 
                        class="form-control form-control-lg" 
                        placeholder="Digite sua senha"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 btn-login">
                    Entrar
                </button>

            </form>

            <div class="login-footer">
                <span>Sistema desenvolvido para controle financeiro simples.</span>
            </div>

        </div>
    </div>

</body>
</html>