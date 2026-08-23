// Fecha o menu suspenso do Orb (barra de navegação) ao clicar fora dele.
document.addEventListener('click', function (evento) {
    var menu = document.querySelector('.orb-menu');
    if (menu && menu.hasAttribute('open') && !menu.contains(evento.target)) {
        menu.removeAttribute('open');
    }
});