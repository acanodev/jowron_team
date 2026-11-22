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
        (function () {
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('#theme-toggle');
                if (!btn) return;
                e.preventDefault();
                let root = document.documentElement;
                let next = root.getAttribute('data-theme') === 'fosc' ? 'clar' : 'fosc';
                root.setAttribute('data-theme', next);
                localStorage.setItem('tema', next);
            });
        })();
    </script>
</body>

</html>
