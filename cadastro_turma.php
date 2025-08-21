<?php
require 'includes/auth.php';
require 'config/db.php';
require 'includes/header.php';

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$sucesso = '';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $escola_id = $_POST['escola_id'];

    // Verifica se a turma já existe para a escola selecionada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM turmas WHERE nome = ? AND escola_id = ?");
    $stmt->execute([$nome, $escola_id]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $erro = "❌ Turma '" . htmlspecialchars($nome) . "' já existe para esta escola.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO turmas (nome, escola_id) VALUES (?, ?)");
        $stmt->execute([$nome, $escola_id]);
        $sucesso = "✅ Turma cadastrada com sucesso!";
    }
}
?>

<div class="container mt-4">
  <h2>👥 Cadastro de Turma</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Nome da Turma:</label>
      <input type="text" name="nome" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Escola:</label>
      <select name="escola_id" id="escola_select" class="form-control" required>
        <option value="">Selecione uma escola</option>
        <?php
        $escolas = $pdo->query("SELECT * FROM escolas")->fetchAll();
        foreach ($escolas as $escola) {
            echo "<option value='{$escola['id']}'>{$escola['nome']}</option>";
        }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>

  <!-- Seção para mostrar turmas existentes -->
  <div id="turmas_existentes" class="mt-4" style="display: none;">
    <h4>📚 Turmas Cadastradas nesta Escola</h4>
    <div id="lista_turmas" class="alert alert-info">
      <!-- As turmas serão carregadas aqui dinamicamente -->
    </div>
  </div>

  <?php if ($sucesso): ?>
    <div class="alert alert-success mt-3"><?= $sucesso ?></div>
  <?php endif; ?>

  <?php if ($erro): ?>
    <div class="alert alert-danger mt-3"><?= $erro ?></div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const escolaSelect = document.getElementById('escola_select');
    const turmasExistentes = document.getElementById('turmas_existentes');
    const listaTurmas = document.getElementById('lista_turmas');

    escolaSelect.addEventListener('change', function() {
        const escolaId = this.value;
        
        if (escolaId === '') {
            turmasExistentes.style.display = 'none';
            return;
        }

        // Mostrar loading
        listaTurmas.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando turmas...</div>';
        turmasExistentes.style.display = 'block';

        // Fazer requisição AJAX
        fetch(`get_turmas.php?escola_id=${escolaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    listaTurmas.innerHTML = `<div class="text-danger">❌ ${data.error}</div>`;
                    return;
                }

                if (data.length === 0) {
                    listaTurmas.innerHTML = '<div class="text-muted">📝 Nenhuma turma cadastrada para esta escola ainda.</div>';
                } else {
                    let html = '<div class="row">';
                    data.forEach(turma => {
                        html += `
                            <div class="col-md-4 mb-2">
                                <div class="badge bg-primary p-2 w-100">
                                    ${turma.nome}
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    listaTurmas.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                listaTurmas.innerHTML = '<div class="text-danger">❌ Erro ao carregar turmas. Tente novamente.</div>';
            });
    });
});
</script>

<?php require 'includes/footer.php'; ?>

