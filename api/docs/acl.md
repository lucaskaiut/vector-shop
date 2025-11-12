# Sistema de ACL

## Visão Geral
- O controle de acesso da API é baseado em perfis (profiles) associados às empresas e aos usuários.
- Cada perfil armazena uma lista de permissões pré-definidas no código.
- As permissões disponíveis abrangem operações relacionadas a usuários: visualizar, criar, editar, excluir e editar o próprio perfil.
- As verificações são centralizadas no serviço `AclService` e expostas tanto via middleware (`permission`) quanto por helper nos controllers (`authorizePermission`).

## Entidades Principais
- `profiles` (migration `2025_11_09_232910_create_profiles_table`)
  - Campos: `company_id`, `name`, `permissions` (JSON), `timestamps`.
  - Cada registro representa um perfil vinculado a uma empresa.
- `users` (migration `2025_11_09_232912_create_users_table`)
  - Inclui `company_id`, `profile_id`, dados básicos e colunas de autenticação.
  - Relações: `company()` e `profile()`.
- Modelos
  - `App\Modules\Acl\Domain\Models\Profile`: aplica validação e normalização da lista de permissões.
  - `App\Models\User`: pertence a `Profile` e `Company`, com carregamento do perfil em `UserService`.

## Permissões Disponíveis
As permissões são constantes do enum `App\Modules\Acl\Domain\Permission`:
- `user.view`
- `user.create`
- `user.update`
- `user.delete`
- `user.update_self`

> Novas permissões devem ser adicionadas ao enum. Não há rota ou lógica para criar permissões dinamicamente.

## Serviço de ACL
- Arquivo: `App\Modules\Acl\Domain\AclService`
- Responsabilidades:
  - `check(User $user, Permission|string $permission, array $context = [])`: retorna `true` quando o usuário possui a permissão para o contexto informado. Trata regras específicas, como `user.update_self` (compara o `target_user_id`).
  - `authorize(...)`: lança `AuthorizationException` se o usuário não estiver autorizado (usado nos controllers / middleware).
  - `getUserPermissions(User $user)`: devolve a lista deduplicada de permissões válidas do perfil atribuído ao usuário.
  - `availablePermissions()`: retorna todas as permissões disponíveis (útil para exibir no frontend).
- Registrado como singleton e alias `acl` em `AppServiceProvider`.

## Middleware de Permissões
- Arquivo: `App\Modules\Acl\Http\Middleware\EnsurePermission`
- Alias registrado em `bootstrap/app.php` (`'permission' => EnsurePermission::class`).
- Uso: `->middleware('permission:user.update|user.update_self')`
  - Aceita múltiplas permissões separadas por `,` ou `|`.
  - Para cada requisição, tenta encontrar uma permissão concedida ao usuário. Se nenhuma corresponder, retorna HTTP 403.
  - Passa ao serviço um contexto com `target_user_id` (quando disponível) para validar `user.update_self`.

## Controllers e Helper Manual
- Controllers herdam de `App\Modules\Core\Http\Controllers\CoreController` e podem chamar `authorizePermission($permission, $context)` para verificações extras.
- Esse helper obtém o usuário autenticado e delega a autorização ao `AclService`.

## Rotas Protegidas
- `routes/api.php` aplica o middleware `permission` aos recursos `users` e `profiles` dentro do grupo autenticado `auth:sanctum`.
  - Usuários:
    - `index` / `show`: `user.view`
    - `store`: `user.create`
    - `update`: `user.update` ou `user.update_self`
    - `destroy`: `user.delete`
  - Perfis:
    - `index`, `show`: `user.view`
    - `store`, `update`, `destroy`: `user.update`

## Fluxo de Autenticação
1. Usuário faz login (`POST /users/login`).
2. Após validar a senha, o `UserController` carrega a relação `profile` (`$user->load('profile')`).
3. O `UserResource` inclui informações do perfil e permissões na resposta.
4. Em requisições subsequentes com token válido, o middleware `permission` garante o acesso conforme o perfil.

## Extensão e Manutenção
- Para adicionar uma nova permissão:
  1. Criar a constante no enum `Permission`.
  2. Atualizar o frontend/documentação conforme necessário.
  3. Opcionalmente, ajustar o middleware das rotas relevantes.
- Para novos recursos protegidos:
  - Reutilizar `EnsurePermission` com as permissões adequadas.
  - Utilizar `authorizePermission` dentro de controllers quando a verificação não estiver diretamente ligada a uma rota.
- Migrations garantem que perfis e associações possam ser criadas via `php artisan migrate`.

## Comandos Úteis
- `php artisan migrate:fresh`: recria todas as tabelas com as migrations atualizadas (perfils e vínculo `profile_id` já incluído em `users`).
- `php artisan test`: recomenda-se para garantir que o ACL não quebre fluxos existentes após alterações.
