# PropertyRegistration

> **Idiomas:** [English](README.md) | Português (BR)

Sistema de cadastro de imóveis com Laravel 12, Vue 3, Inertia.js (SSR), Jetstream, PrimeVue e suporte a múltiplos idiomas.

---

## Stack

| Camada       | Tecnologia                          |
|--------------|-------------------------------------|
| Backend      | Laravel 12, PHP 8.2+                |
| Auth         | Laravel Jetstream (Fortify + Sanctum) |
| Frontend     | Vue 3, Inertia.js v2 (SSR)          |
| UI           | PrimeVue 4 (tema Aura)              |
| Estilo       | Tailwind CSS v3                     |
| i18n         | laravel-vue-i18n                    |
| Banco        | MariaDB (driver `mariadb`)          |
| Auditoria    | owen-it/laravel-auditing            |
| Relatórios   | barryvdh/laravel-dompdf             |
| Build        | Vite 7 + SSR                        |

> **Observação:** o edital do projeto cita **Vuetify**; a biblioteca de UI implementada é o **PrimeVue**, integrada em todo o frontend.

---

## Requisitos

- PHP 8.2+ (upload de documentos: recomendado `upload_max_filesize` ≥ 16M e `post_max_size` ≥ 20M)
- Node.js 18+
- MariaDB 10.6+ (ou MySQL 8.0+ compatível)
- Composer 2.x

---

## Instalação

### 1. Clonar e instalar dependências PHP

```bash
git clone https://github.com/Amanda-Ros4/PropertyRegistration.git
cd PropertyRegistration
composer install
```

Repositório: [github.com/Amanda-Ros4/PropertyRegistration](https://github.com/Amanda-Ros4/PropertyRegistration)

### 2. Ambiente

**Linux / macOS / Git Bash:**

```bash
cp .env.example .env
php artisan key:generate
```

**Windows (PowerShell ou CMD):**

```powershell
copy .env.example .env
php artisan key:generate
```

Configure banco e idioma no `.env`:

```env
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=property_registration
DB_USERNAME=root
DB_PASSWORD=sua_senha

VITE_APP_NAME="PropertyReg"
```

### 3. Criar o banco de dados

Certifique-se de que o MariaDB está em execução e crie o banco:

```sql
CREATE DATABASE property_registration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Para desenvolvimento local sem MariaDB, use SQLite (veja comentários no `.env.example`).

### 4. Dependências Node

```bash
npm install
```

### 5. Migrações e seed

```bash
php artisan migrate
php artisan db:seed
```

### 6. Assets

Produção:

```bash
npm run build
```

Desenvolvimento:

```bash
npm run dev
```

### 7. Servidor

```bash
php artisan serve
```

Ou todos os processos juntos (inclui limites de upload PHP para documentos de imóveis):

```bash
composer dev
```

Acesse `http://localhost:8000` e faça login com uma conta do seed. **O cadastro público está desabilitado** — usuários são criados por administradores.

---

## Perfis de acesso

| Perfil                  | Módulo usuários | Auditoria | Pessoas / imóveis      | Relatórios        |
|-------------------------|:---------------:|:---------:|------------------------|:-----------------:|
| Administrador TI        | Total           | Sim       | **Todos os registros** | Sim               |
| Administrador Sistema   | Só atendentes   | Sim       | **Somente os próprios**| Somente os próprios |
| Atendente               | Sem acesso      | Não       | Somente os próprios    | Somente os próprios |

### Detalhes por perfil

- **Administrador TI** — Acesso total; edita e-mail e perfil de usuários; vê todos os dados.
- **Administrador Sistema** — Cria e edita **apenas atendentes**; não cria nem edita administradores TI; gerencia usuários e auditoria; pessoas e imóveis ficam limitados ao próprio `user_id`.
- **Atendente** — Cadastra e consulta apenas os próprios registros; sem acesso a usuários ou auditoria.

### Regras gerais

- Usuários **não podem ser excluídos** — apenas ativados ou desativados. Contas novas precisam **verificar o e-mail** antes de acessar o sistema (link enviado na criação ou quando o Administrador TI altera o e-mail).
- Pessoas e imóveis **podem ser excluídos** pela interface, com confirmação. Pessoa vinculada a imóveis **não pode** ser excluída enquanto houver vínculos.
- **Área e situação** do imóvel só mudam por **averbação**.
- CPF **bloqueado após a criação** do usuário (Administrador TI pode alterar e-mail e perfil).

---

## Módulos

- **Usuários** — cadastro, edição, ativação/desativação; verificação de e-mail; sem exclusão
- **Pessoas** — cadastro, consulta com filtros, exclusão (com confirmação; bloqueada se houver imóveis)
- **Imóveis** — cadastro, documentos, averbações (área e situação), exclusão com confirmação
- **Averbações** — única forma de alterar área e status do imóvel
- **Relatórios PDF** — sintético e individual, com tradução por idioma
- **Auditoria** — listagem, filtros e detalhes (Laravel Auditing; apenas Admin TI e Admin Sistema)

---

## Multi-tenancy

Registros de `Person` e `Property` pertencem a um `user_id`. Atendentes e Administradores Sistema veem apenas os próprios dados. O **Administrador TI** enxerga todos os registros via `canAccessAllRecords()`.

---

## Internacionalização

Idiomas: **Português** (`pt_BR`), **English** (`en`), **Español** (`es`).

Arquivos em `lang/pt_BR.json`, `lang/en.json` e `lang/es.json`.

O seletor de idioma na aplicação persiste a escolha no cookie `app_locale`.

---

## Verificação de e-mail

Usuários do sistema devem verificar o endereço de e-mail (Laravel Fortify + Jetstream) antes de acessar as rotas autenticadas.

E-mails de contribuintes em **Pessoas** são validados como únicos e conformes à RFC; em produção, também há verificação DNS (MX) (`App\Support\EmailValidation`).

- Na **criação de usuário**, o link de verificação é enviado automaticamente.
- Quando o **Administrador TI** altera o e-mail de um usuário, a verificação é resetada e um novo link é enviado.
- Contas do seed já vêm verificadas para desenvolvimento local.

Para testar localmente sem SMTP, use o driver de log no `.env`:

```env
MAIL_MAILER=log
```

As mensagens de verificação aparecem em `storage/logs/laravel.log`.

---

## Testes

Os testes automatizados usam SQLite em memória (configurado em `phpunit.xml`):

```bash
php artisan test
```

---

## Credenciais (após seed)

| Perfil                  | E-mail                | Senha    |
|-------------------------|-----------------------|----------|
| Administrador TI        | ti@example.com        | password |
| Administrador Sistema   | admin@example.com     | password |
| Atendente               | atendente@example.com | password |

---

## Licença

MIT — veja [composer.json](composer.json).
