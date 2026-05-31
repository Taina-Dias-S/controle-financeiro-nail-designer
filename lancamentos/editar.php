<?php
require_once '../config/app.php';
require_once '../includes/proteger.php';
require_once '../config/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Busca o lançamento
$sql = "
    SELECT *
    FROM lancamentos
    WHERE id = :id
    AND id_usuario = :id_usuario
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $id,
    ':id_usuario' => $usuario_id
]);

$lancamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lancamento) {
    header("Location: index.php");
    exit;
}

// Busca categorias
$sqlCategorias = "
    SELECT * 
    FROM categorias 
    WHERE ativo = 1 
    ORDER BY 
        FIELD(tipo, 'entrada', 'saida'),
        FIELD(origem, 'negocio', 'pessoal', 'ambos'),
        nome ASC
";
$stmt = $pdo->query($sqlCategorias);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca formas de pagamento
$sqlFormas = "
    SELECT * 
    FROM formas_pagamento 
    WHERE ativo = 1 
    ORDER BY nome ASC
";
$stmt = $pdo->query($sqlFormas);
$formas_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);

function valorBr($valor) {
    return number_format($valor, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include '../includes/head.php'; ?>
</head>
<body class="app-body">

<main class="app-container">

    <div class="app-header">
        <div>
            <span class="small text-muted">Editar registro</span>
            <h1>Lançamento</h1>
        </div>

        <a href="index.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>

    <section class="section-box">
        <form method="POST" action="atualizar.php" class="app-form">

            <input type="hidden" name="id" value="<?php echo $lancamento['id']; ?>">

            <div class="mb-3">
                <label class="form-label">Data</label>
                <input 
                    type="date" 
                    name="data_lancamento" 
                    class="form-control form-control-lg" 
                    value="<?php echo htmlspecialchars($lancamento['data_lancamento']); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" id="tipo" class="form-select form-select-lg" required>
                    <option value="">Selecione</option>
                    <option value="entrada" <?php echo $lancamento['tipo'] === 'entrada' ? 'selected' : ''; ?>>
                        Entrada
                    </option>
                    <option value="saida" <?php echo $lancamento['tipo'] === 'saida' ? 'selected' : ''; ?>>
                        Saída
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Origem</label>
                <select name="origem" id="origem" class="form-select form-select-lg" required>
                    <option value="">Selecione</option>
                    <option value="negocio" <?php echo $lancamento['origem'] === 'negocio' ? 'selected' : ''; ?>>
                        Negócio
                    </option>
                    <option value="pessoal" <?php echo $lancamento['origem'] === 'pessoal' ? 'selected' : ''; ?>>
                        Pessoal
                    </option>
                </select>

                <small class="text-muted">
                    Use "Negócio" para materiais, taxas e custos do trabalho. Use "Pessoal" para despesas da vida pessoal.
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoria</label>
                <select name="id_categoria" id="id_categoria" class="form-select form-select-lg" required>
                    <option value="">Selecione</option>

                    <?php foreach ($categorias as $categoria): ?>
                        <option 
                            value="<?php echo $categoria['id']; ?>"
                            data-tipo="<?php echo $categoria['tipo']; ?>"
                            data-origem="<?php echo $categoria['origem']; ?>"
                            <?php echo (int)$lancamento['id_categoria'] === (int)$categoria['id'] ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($categoria['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Forma de pagamento</label>
                <select name="id_forma_pagamento" class="form-select form-select-lg">
                    <option value="">Selecione</option>

                    <?php foreach ($formas_pagamento as $forma): ?>
                        <option 
                            value="<?php echo $forma['id']; ?>"
                            <?php echo (int)$lancamento['id_forma_pagamento'] === (int)$forma['id'] ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($forma['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Valor</label>
                <input 
                    type="text" 
                    name="valor" 
                    class="form-control form-control-lg money-input" 
                    placeholder="0,00"
                    inputmode="numeric"
                    value="<?php echo valorBr($lancamento['valor']); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea 
                    name="descricao" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Ex: Alongamento em gel, compra de esmaltes..."
                ><?php echo htmlspecialchars($lancamento['descricao'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
                Salvar alterações
            </button>

        </form>
    </section>

</main>

<?php include '../includes/menu.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const tipoSelect = document.getElementById('tipo');
const origemSelect = document.getElementById('origem');
const categoriaSelect = document.getElementById('id_categoria');
const categoriaAtual = "<?php echo (int)$lancamento['id_categoria']; ?>";

function filtrarCategorias(manterSelecionada = false) {
    const tipo = tipoSelect.value;
    const origem = origemSelect.value;

    Array.from(categoriaSelect.options).forEach(option => {
        if (option.value === '') {
            option.hidden = false;
            return;
        }

        const optionTipo = option.getAttribute('data-tipo');
        const optionOrigem = option.getAttribute('data-origem');

        const tipoOk = optionTipo === tipo;
        const origemOk = optionOrigem === origem || optionOrigem === 'ambos';

        option.hidden = !(tipoOk && origemOk);
    });

    if (manterSelecionada) {
        categoriaSelect.value = categoriaAtual;
    } else {
        categoriaSelect.value = '';
    }
}

tipoSelect.addEventListener('change', () => filtrarCategorias(false));
origemSelect.addEventListener('change', () => filtrarCategorias(false));

filtrarCategorias(true);

const moneyInput = document.querySelector('.money-input');

moneyInput.addEventListener('input', function () {
    let valor = this.value.replace(/\D/g, '');

    if (valor === '') {
        this.value = '';
        return;
    }

    valor = (parseInt(valor, 10) / 100).toFixed(2);
    valor = valor.replace('.', ',');
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    this.value = valor;
});
</script>

</body>
</html>