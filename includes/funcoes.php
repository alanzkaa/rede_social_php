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