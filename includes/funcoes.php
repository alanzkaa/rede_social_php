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
        case 'mensagem':
            return "{$nome} te enviou uma mensagem";
        default:
            return "{$nome} interagiu com você";
    }
}

/**
 * Devolve para onde a notificação deve levar ao ser clicada.
 * Curtida/comentário levam pro próprio perfil (onde o post aparece);
 * notificações de amizade e mensagem levam pro perfil/conversa com quem causou a ação.
 */
function linkNotificacao(string $tipo, int $atorId): string
{
    if ($tipo === 'solicitacao_amizade' || $tipo === 'amizade_aceita') {
        return 'perfil.php?id=' . $atorId;
    }

    if ($tipo === 'mensagem') {
        return 'conversa.php?com=' . $atorId;
    }

    return 'perfil.php';
}

/**
 * Confere se um usuário está "online" — teve atividade nos últimos
 * $limiteMinutos minutos. Sem WebSocket, essa é a forma realista de
 * aproximar presença: baseada na última vez que a pessoa carregou uma página.
 */
function usuarioEstaOnline(?string $ultimaAtividade, int $limiteMinutos = 5): bool
{
    if ($ultimaAtividade === null) {
        return false;
    }

    $minutosPassados = (time() - strtotime($ultimaAtividade)) / 60;

    return $minutosPassados <= $limiteMinutos;
}

/**
 * Devolve o HTML de uma bolinha de status (verde = online, cinza = offline),
 * pra sobrepor no canto do avatar.
 */
function htmlStatusOnline(?string $ultimaAtividade): string
{
    $classe = usuarioEstaOnline($ultimaAtividade) ? 'status-dot--online' : 'status-dot--offline';

    return "<span class=\"status-dot {$classe}\"></span>";
}

/**
 * Devolve o texto "Online agora" ou "Visto por último em dd/mm/aaaa hh:mm",
 * pra exibir junto do nome em perfis e conversas.
 */
function textoUltimaAtividade(?string $ultimaAtividade): string
{
    if ($ultimaAtividade === null) {
        return 'Ainda não esteve online';
    }

    if (usuarioEstaOnline($ultimaAtividade)) {
        return 'Online agora';
    }

    return 'Visto por último em ' . date('d/m/Y H:i', strtotime($ultimaAtividade));
}

/**
 * Envolve um avatar (já pronto, vindo de htmlFotoPerfil) com a bolinha de
 * status no canto — os dois precisam ficar dentro do mesmo "wrapper"
 * posicionado, por isso essa função existe em vez de só concatenar os dois.
 */
function htmlAvatarComStatus(string $htmlAvatar, ?string $ultimaAtividade): string
{
    return '<span class="avatar-wrapper">' . $htmlAvatar . htmlStatusOnline($ultimaAtividade) . '</span>';
}