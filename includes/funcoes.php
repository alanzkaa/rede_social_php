<?php
/**
 * Devolve o HTML de uma miniatura de foto de perfil (ou um avatar com a
 * inicial do nome, se o usuário não tiver foto definida). Reaproveitado
 * em várias páginas — por isso fica em includes/, não em models/.
 */
function htmlFotoPerfil(?string $nomeArquivo, int $tamanho = 40, string $nome = ''): string
{
    $estilo = "width:{$tamanho}px; height:{$tamanho}px;";

    if ($nomeArquivo) {
        $src = 'uploads/' . htmlspecialchars($nomeArquivo);
        return "<img src=\"{$src}\" alt=\"Foto de perfil\" class=\"avatar\" style=\"{$estilo}\">";
    }

    $inicial = $nome !== '' ? mb_strtoupper(mb_substr(trim($nome), 0, 1)) : '?';
    $inicial = htmlspecialchars($inicial);
    $fonte = (int) round($tamanho * 0.45);

    return "<span class=\"avatar avatar--initials\" style=\"{$estilo} font-size:{$fonte}px;\">{$inicial}</span>";
}

/**
 * Devolve o texto legível de uma notificação, a partir do tipo e do nome
 * de quem causou a ação.
 */
function textoNotificacao(string $tipo, string $atorNome): string
{
    $nome = htmlspecialchars($atorNome);

    switch ($tipo) {
        case 'curtida':
            return "{$nome} curtiu sua postagem";
        case 'comentario':
            return "{$nome} comentou na sua postagem";
        case 'solicitacao_amizade':
            return "{$nome} te enviou uma solicitação de amizade";
        case 'amizade_aceita':
            return "{$nome} aceitou sua solicitação de amizade";
        default:
            return "{$nome} interagiu com você";
    }
}

/**
 * Devolve para onde a notificação deve levar ao ser clicada.
 * Curtida/comentário levam pro próprio perfil (onde o post aparece);
 * notificações de amizade levam pro perfil de quem causou a ação.
 */
function linkNotificacao(string $tipo, int $atorId): string
{
    if ($tipo === 'solicitacao_amizade' || $tipo === 'amizade_aceita') {
        return 'perfil.php?id=' . $atorId;
    }

    return 'perfil.php';
}