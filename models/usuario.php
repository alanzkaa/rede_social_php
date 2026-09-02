<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Cadastra um novo usuário no banco.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function cadastrarUsuario(string $nomeCompleto, string $email, string $senha, ?string $nomeUsuario, ?string $dataNascimento): bool|string
{
    $pdo = conectar();

    // Verifica se o e-mail já está cadastrado antes de tentar inserir.
    $sql = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    if ($stmt->fetch()) {
        return 'Este e-mail já está cadastrado.';
    }

    // Gera o hash da senha — nunca salvamos a senha em texto puro.
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome_completo, email, senha, nome_usuario, data_nascimento)
            VALUES (:nome_completo, :email, :senha, :nome_usuario, :data_nascimento)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nome_completo', $nomeCompleto);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':senha', $senhaHash);
    $stmt->bindValue(':nome_usuario', $nomeUsuario);
    $stmt->bindValue(':data_nascimento', $dataNascimento);

    return $stmt->execute();
}

/**
 * Busca um usuário pelo e-mail. Retorna o array com os dados do usuário,
 * ou null se não encontrar ninguém com esse e-mail.
 */
function buscarUsuarioPorEmail(string $email): ?array
{
    $pdo = conectar();

    $sql = "SELECT id, nome_completo, email, senha, nome_usuario FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

/**
 * Busca um usuário pelo ID. Retorna o array com os dados do usuário,
 * ou null se não encontrar (ex: ID inválido).
 */
function buscarUsuarioPorId(int $id): ?array
{
    $pdo = conectar();

    $sql = "SELECT id, nome_completo, email, nome_usuario, foto_perfil, data_nascimento, data_cadastro,
                   privacidade_postagens, aceita_solicitacoes,
                   notif_curtida, notif_comentario, notif_solicitacao_amizade, notif_amizade_aceita
            FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

/**
 * Atualiza os dados de perfil de um usuário.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function atualizarPerfil(int $id, string $nomeCompleto, ?string $nomeUsuario, ?string $dataNascimento): bool|string
{
    $pdo = conectar();

    // Se um nome de usuário foi informado, confere se outro usuário já não está usando.
    if ($nomeUsuario !== null) {
        $sql = "SELECT id FROM usuarios WHERE nome_usuario = :nome_usuario AND id != :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome_usuario', $nomeUsuario);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetch()) {
            return 'Este nome de usuário já está em uso.';
        }
    }

    $sql = "UPDATE usuarios
            SET nome_completo = :nome_completo,
                nome_usuario = :nome_usuario,
                data_nascimento = :data_nascimento
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':nome_completo', $nomeCompleto);
    $stmt->bindValue(':nome_usuario', $nomeUsuario);
    $stmt->bindValue(':data_nascimento', $dataNascimento);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Troca a senha de um usuário, exigindo a senha atual como confirmação.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function alterarSenha(int $usuarioId, string $senhaAtual, string $senhaNova): bool|string
{
    $pdo = conectar();

    $sql = "SELECT senha FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
        return 'Senha atual incorreta.';
    }

    if (strlen($senhaNova) < 6) {
        return 'A nova senha precisa ter pelo menos 6 caracteres.';
    }

    $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':senha', password_hash($senhaNova, PASSWORD_DEFAULT));
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Atualiza as preferências de privacidade do usuário.
 */
function atualizarPrivacidade(int $usuarioId, string $privacidadePostagens, bool $aceitaSolicitacoes): bool
{
    $pdo = conectar();

    $sql = "UPDATE usuarios
            SET privacidade_postagens = :privacidade, aceita_solicitacoes = :aceita
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':privacidade', $privacidadePostagens);
    $stmt->bindValue(':aceita', $aceitaSolicitacoes ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Atualiza as preferências de notificação do usuário (um booleano por tipo).
 */
function atualizarPreferenciasNotificacao(int $usuarioId, bool $curtida, bool $comentario, bool $solicitacaoAmizade, bool $amizadeAceita): bool
{
    $pdo = conectar();

    $sql = "UPDATE usuarios
            SET notif_curtida = :curtida,
                notif_comentario = :comentario,
                notif_solicitacao_amizade = :solicitacao,
                notif_amizade_aceita = :aceita
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':curtida', $curtida ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindValue(':comentario', $comentario ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindValue(':solicitacao', $solicitacaoAmizade ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindValue(':aceita', $amizadeAceita ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Exclui permanentemente a conta de um usuário, exigindo a senha como confirmação.
 * As tabelas relacionadas (posts, curtidas, comentários, amizades, notificações)
 * são apagadas automaticamente pelas foreign keys com ON DELETE CASCADE.
 * Retorna true em caso de sucesso, ou uma string com mensagem de erro.
 */
function excluirConta(int $usuarioId, string $senha): bool|string
{
    $pdo = conectar();

    $sql = "SELECT senha, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        return 'Senha incorreta.';
    }

    // Remove a foto de perfil do disco antes de apagar o registro do banco.
    if ($usuario['foto_perfil']) {
        $caminho = __DIR__ . '/../public/uploads/' . $usuario['foto_perfil'];
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }

    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);

    return $stmt->execute();
}
/**
 * Busca usuários pelo nome ou nome de usuário (para adicionar como amigo).
 * Exclui o próprio usuário logado do resultado.
 */
function buscarUsuarios(string $termo, int $idExcluido): array
{
    $pdo = conectar();

    $sql = "SELECT id, nome_completo, nome_usuario, foto_perfil
            FROM usuarios
            WHERE (nome_completo LIKE :termo OR nome_usuario LIKE :termo2)
              AND id != :id_excluido
            ORDER BY nome_completo
            LIMIT 20";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':termo', '%' . $termo . '%');
    $stmt->bindValue(':termo2', '%' . $termo . '%');
    $stmt->bindValue(':id_excluido', $idExcluido, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Salva a foto de perfil enviada via formulário (array vindo de $_FILES),
 * atualiza o caminho no banco e retorna true, ou uma string de erro.
 */
function atualizarFotoPerfil(int $usuarioId, array $arquivo): bool|string
{
    // UPLOAD_ERR_OK confirma que o upload não teve erro (arquivo ausente, tamanho excedido pelo PHP, etc).
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return 'Erro no envio do arquivo. Tente novamente.';
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
    ];

    // Detecta o tipo real do arquivo pelo conteúdo, não pela extensão informada
    // pelo navegador (extensão pode ser falsificada facilmente).
    $tipoReal = mime_content_type($arquivo['tmp_name']);

    if (!isset($tiposPermitidos[$tipoReal])) {
        return 'Formato inválido. Envie uma imagem JPG, PNG ou GIF.';
    }

    $tamanhoMaximo = 2 * 1024 * 1024; // 2 MB
    if ($arquivo['size'] > $tamanhoMaximo) {
        return 'A imagem precisa ter no máximo 2MB.';
    }

    // Gera um nome de arquivo único, evitando sobrescrever fotos de outros usuários
    // ou depender do nome original (que pode ter espaços, acentos, etc).
    $extensao = $tiposPermitidos[$tipoReal];
    $nomeArquivo = 'perfil_' . $usuarioId . '_' . uniqid() . '.' . $extensao;
    $caminhoDestino = __DIR__ . '/../public/uploads/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        return 'Não foi possível salvar a imagem no servidor.';
    }

    $pdo = conectar();
    $sql = "UPDATE usuarios SET foto_perfil = :foto WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':foto', $nomeArquivo);
    $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);

    return $stmt->execute();
}