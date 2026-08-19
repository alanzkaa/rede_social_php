<?php
/**
 * Devolve o HTML de uma miniatura de foto de perfil (ou um placeholder,
 * se o usuário não tiver foto definida). Reaproveitado em várias páginas.
 */
function htmlFotoPerfil(?string $nomeArquivo, int $tamanho = 40): string
{
    if ($nomeArquivo) {
        $src = 'uploads/' . htmlspecialchars($nomeArquivo);
        return "<img src=\"{$src}\" alt=\"Foto de perfil\" width=\"{$tamanho}\" height=\"{$tamanho}\" style=\"border-radius:50%; object-fit:cover; vertical-align:middle;\">";
    }

    return "<span style=\"display:inline-block; width:{$tamanho}px; height:{$tamanho}px; background:#ddd; border-radius:50%; vertical-align:middle; text-align:center; line-height:{$tamanho}px; font-size:" . (int)($tamanho / 2) . "px;\">'-'</span>";
}