<!DOCTYPE html>
<html lang="ca" data-theme="fosc">

<head>
    <script>document.documentElement.dataset.theme = localStorage.getItem('tema') || 'fosc';</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Videojocs</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <script>
    window.appFetch = async (url, options = {}) => {
      const res = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: { Accept: 'application/json', ...(options.headers || {}) },
      });
      let cos;
      try { cos = await res.json(); } catch (e) {
        cos = { ok: false, data: { error: 'Resposta no vàlida' }, meta: {} };
      }
      if (res.status === 401 && cos.meta && cos.meta.redirect) location.href = cos.meta.redirect;
      return cos;
    };

    window.appPost = async (url, body, method = 'POST') => window.appFetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });

    window.appForm = async (form) => window.appFetch(form.action, {
      method: form.method || 'POST',
      body: new FormData(form),
    });

    window.renderLayout = (meta) => {
      const menu = document.getElementById('menu-principal');
      if (menu) {
        menu.innerHTML = `
          <a href="/mvc/?route=/">Inici</a>
          <a href="/mvc/?route=/jocs">Jocs</a>
          ${meta.loggedIn ? `
            <a href="/mvc/?route=/ranking">Ranking</a>
            <a href="/mvc/?route=/amics">Amics</a>
            <a href="/mvc/?route=/estadistiques">Estadístiques</a>
            <a href="/mvc/?route=/badges">Assoliments</a>
            ${meta.potGestionarJocs ? '<a href="/mvc/?route=/gestio-jocs">Gestió jocs</a>' : ''}
            <!-- VULNERABILITAT: XSS -->
            <a class="menu-user" href="/mvc/?route=/perfil">Hola, ${meta.nomUsuari}</a>
            <a class="menu-cta menu-cta-outline" href="/mvc/?route=/logout">Sortir</a>` : `
            <a href="/mvc/?route=/login">Accedir</a>
            <a class="menu-cta" href="/mvc/?route=/registre">Registrar-se</a>`}
          <a id="theme-toggle" class="theme-toggle" href="#" title="Canviar tema dia/nit" aria-label="Canviar tema dia/nit">
            <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
              <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
            </svg>
            <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
              <circle cx="12" cy="12" r="4" fill="currentColor"/>
              <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
            </svg>
          </a>`;
      }
      document.documentElement.dataset.theme = localStorage.getItem('tema') || 'fosc';
    };

    window.renderFlash = (meta) => {
      const node = document.getElementById('app-flash');
      if (!node) return;
      const flash = meta && meta.flash;
      node.innerHTML = flash
        ? `<div class="${flash.tipus === 'error' ? 'error' : 'missatge-ok'}">${flash.text}</div>`
        : '';
    };

    window.mostraError = (resposta) => {
      if (resposta.ok) return false;
      const text = (resposta.data && resposta.data.error) || 'Hi ha hagut un error';
      window.renderFlash({ flash: { tipus: 'error', text } });
      return true;
    };
    </script>
</head>

<body>
    <header class="site-header">
        <nav class="site-nav">
            <a class="logo" href="/mvc/?route=/">ArJol<span>.cat</span></a>
            <div class="menu" id="menu-principal"></div>
        </nav>
    </header>
    <main>
        <div id="app-flash"></div>
