# Rede Social em PHP

Projeto de estudo: uma rede social construída **do zero, sem framework**, em PHP puro + MySQL, com o objetivo de aprender os fundamentos da linguagem e de desenvolvimento web (sessões, autenticação, relacionamento entre tabelas, upload de arquivos) antes de partir para frameworks.

## Preview

<img width="1876" height="892" alt="image" src="https://github.com/user-attachments/assets/653acb50-d9b0-4bc1-91d4-49a4ebb79d96" />


## Funcionalidades

- Cadastro e login de usuários (com senha em hash via `password_hash`)
- Edição de perfil, incluindo upload de foto
- Sistema de amizades (solicitar, aceitar, recusar, desfazer)
- Feed de postagens (dos amigos e do próprio usuário)
- Curtidas e comentários em postagens
- Exclusão de postagens e comentários (somente pelo autor)
- Visualização do perfil de outros usuários, com postagens visíveis apenas para amigos

## Tecnologias

- PHP puro (sem framework)
- MySQL (via PDO, com prepared statements)
- HTML, CSS e JavaScript puro (sem bibliotecas front-end)
- XAMPP como ambiente de desenvolvimento local

## Estrutura de pastas

```
rede_social/
├── config/          # conexão com o banco de dados (PDO)
├── includes/        # funções auxiliares reutilizáveis (autenticação, exibição)
├── models/          # funções de acesso a dados, uma por entidade
├── public/          # arquivos acessados pelo navegador (raiz do site)
│   ├── css/
│   ├── js/
│   └── uploads/     # fotos de perfil enviadas pelos usuários
└── sql/             # scripts SQL de criação das tabelas
```

## Como rodar localmente

1. Instale o [XAMPP](https://www.apachefriends.org/) (ou outro ambiente com PHP 8+ e MySQL).
2. Clone este repositório dentro da pasta `htdocs` do XAMPP:
   ```
   git clone https://github.com/alanzkaa/rede_social.git
   ```
3. Inicie o **Apache** e o **MySQL** pelo painel do XAMPP.
4. Abra o phpMyAdmin (`http://localhost/phpmyadmin`), crie o banco executando o conteúdo de `sql/schema.sql` na aba SQL — isso cria o banco `db_rede_social` e todas as tabelas de uma vez.
5. Acesse `http://localhost/rede_social/public/cadastro.php` para criar sua primeira conta.

## Banco de dados

O arquivo `sql/schema.sql` contém a criação completa do banco.

## Autor

Alan — [github.com/alanzkaa](https://github.com/alanzkaa)
