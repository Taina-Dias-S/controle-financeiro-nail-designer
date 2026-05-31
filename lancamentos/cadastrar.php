<?php
require_once '../config/app.php';
require_once '../includes/proteger.php';
require_once '../config/conexao.php';

$tipoSelecionado = $_GET['tipo'] ?? '';
$origemSelecionada = $_GET['origem'] ?? '';

if (!in_array($tipoSelecionado, ['entrada', 'saida'])) {
    $tipoSelecionado = '';
}

if (!in_array($origemSelecionada, ['negocio', 'pessoal'])) {
    $origemSelecionada = '';
}

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

$sqlFormas = "
    SELECT * 
    FROM formas_pagamento 
    WHERE ativo = 1 
    ORDER BY nome ASC
";
$stmt = $pdo->query($sqlFormas);
$formas_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <span class="small text-muted">Novo registro</span>
            <h1>Lançamento</h1>
        </div>

        <a href="index.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>

    <section class="section-box">
        <form method="POST" action="salvar.php" class="app-form">

            <div class="mb-3">
                <label class="form-label">Data</label>
                <input 
                    type="date" 
                    name="data_lancamento" 
                    class="form-control form-control-lg" 
                    value="<?php echo date('Y-m-d'); ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo</label>
             <select name="tipo" id="tipo" class="form-select form-select-lg" required>
    <option value="">Selecione</option>
    <option value="entrada" <?php echo $tipoSelecionado === 'entrada' ? 'selected' : ''; ?>>
        Entrada
    </option>
    <option value="saida" <?php echo $tipoSelecionado === 'saida' ? 'selected' : ''; ?>>
        Saída
    </option>
</select>
            </div>

            <div class="mb-3">
                <label class="form-label">Origem</label>
              <select name="origem" id="origem" class="form-select form-select-lg" required>
    <option value="">Selecione</option>
    <option value="negocio" <?php echo $origemSelecionada === 'negocio' ? 'selected' : ''; ?>>
        Negócio
    </option>
    <option value="pessoal" <?php echo $origemSelecionada === 'pessoal' ? 'selected' : ''; ?>>
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
                    <option value="">Selecione uma categoria</option>

                    <?php foreach ($categorias as $categoria): ?>
                        <option 
                            value="<?php echo $categoria['id']; ?>"
                            data-tipo="<?php echo $categoria['tipo']; ?>"
                            data-origem="<?php echo $categoria['origem']; ?>"
                            hidden
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
                        <option value="<?php echo $forma['id']; ?>">
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
                ></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
                Salvar lançamento
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

function filtrarCategorias() {
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

    categoriaSelect.value = '';
}

tipoSelect.addEventListener('change', filtrarCategorias);
origemSelect.addEventListener('change', filtrarCategorias);
filtrarCategorias();

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