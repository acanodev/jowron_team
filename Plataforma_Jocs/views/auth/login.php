<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-container">
    <h2>Iniciar Sessió</h2>
    <div id="error-login"></div>

    <!-- VULNERABILITAT: Sense protecció CSRF -->
    <form method="POST" action="" data-json>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Contrasenya:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Entrar</button>
    </form>

    <p>No tens compte? <a href="/mvc/?route=/registre">Registra't aquí</a></p>
</div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (resposta.data.error) {
  document.getElementById('error-login').innerHTML = `<div class="error">${resposta.data.error}</div>`;
}

document.querySelectorAll('form[data-json]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form));
    const resultat = await window.appPost(form.action, body);
    if (!resultat.ok) {
      document.getElementById('error-login').innerHTML = `<div class="error">${resultat.data.error}</div>`;
      return;
    }
    if (resultat.meta.redirect) location.href = resultat.meta.redirect;
  });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
