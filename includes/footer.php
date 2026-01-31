</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const btnToggle = document.getElementById('sidebarToggle');
        const btnClose = document.getElementById('sidebarClose');

        // Función para abrir
        function openSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (overlay) overlay.classList.add('show');
        }

        // Función para cerrar
        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        }

        // Eventos (Listeners)
        if (btnToggle) btnToggle.addEventListener('click', openSidebar);
        if (btnClose) btnClose.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar); // Cerrar al tocar afuera

        // Cerrar menú automáticamente al hacer clic en un enlace (Solo móvil)
        const navLinks = sidebar ? sidebar.querySelectorAll('.nav-link') : [];
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });
    });
</script>

</body>

</html>