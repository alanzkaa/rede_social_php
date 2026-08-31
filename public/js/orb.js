// Fecha os menus suspensos da navbar (Orb e Notificações) ao clicar fora deles.
document.addEventListener('click', function (evento) {
    document.querySelectorAll('.orb-menu, .notif-menu').forEach(function (menu) {
        if (menu.hasAttribute('open') && !menu.contains(evento.target)) {
            menu.removeAttribute('open');
        }
    });
});