# API Academia Garagem Aço

Documentação inicial para integração do app Android e serviços externos.

## Base URL local
- `http://localhost/academia/public/`

## Autenticação
- Login: `POST /login.php`
- Logout: `GET /logout`

## Rotas principais
- `GET /dashboard`
- `GET /alunos`
- `GET /modalidades`
- `GET /turmas`
- `GET /graduacoes`
- `GET /frequencia`
- `GET /financeiro`
- `GET /promocoes`
- `GET /usuarios`

## Observações
- O sistema usa sessão PHP tradicional.
- Para o app Android, considerar uma API REST futura.
- Integração financeira/PIX será adicionada posteriormente.
