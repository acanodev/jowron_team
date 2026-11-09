    </main>
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <a class="logo" href="/mvc/?route=/">ArJol<span>.cat</span></a>
                <p>Plataforma de videojocs · Institut Carles Vallbona</p>
            </div>
            <div class="footer-links">
                <a href="/mvc/?route=/">Inici</a>
                <a href="/mvc/?route=/jocs">Jocs</a>
                <a href="/mvc/?route=/ranking">Ranking</a>
            </div>
        </div>
        <p class="footer-copy">&copy; 2026 ArJol.cat - Institut Carles Vallbona. Tots els drets reservats.</p>
    </footer>

    <script>
        // Canvi de tema instantani + desat a la sessió del servidor
        (function () {
            var btn = document.getElementById('theme-toggle');
            if (!btn) return;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var root = document.documentElement;
                var next = root.getAttribute('data-theme') === 'fosc' ? 'clar' : 'fosc';
                root.setAttribute('data-theme', next);

                // Enllaç de reserva (sense JS) apunta ara al tema contrari
                var u = new URL(window.location.href);
                u.searchParams.set('tema', next === 'fosc' ? 'clar' : 'fosc');
                btn.setAttribute('href', u.search);

                // Desa la preferència a la sessió (ruta lleugera per evitar efectes secundaris)
                fetch('/mvc/?route=/&tema=' + next, { credentials: 'same-origin' }).catch(function () {});
            });
        })();
    </script>
</body>

</html>
