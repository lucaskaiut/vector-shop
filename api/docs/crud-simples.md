# Guia para Criar um CRUD Simples

## Objetivo
Este documento descreve o passo a passo para adicionar um novo módulo com CRUD simples na API, respeitando as convenções adotadas (Service genérico, filtros `QueryFilter`, resources, requests e integrações com ACL). O exemplo assume uma entidade fictícia `Product`.

## Passo 1: Definir Estrutura de Dados
1. Criar a tabela na pasta `database/migrations`.
   - Nome sugerido: `YYYY_MM_DD_HHMMSS_create_products_table.php`.
   - Campos padrão: `id`, `company_id` (se aplicável), atributos do domínio, timestamps.
   - Incluir relacionamentos necessários, como `foreignId('company_id')->constrained('companies')`.

2. Criar o modelo Eloquent em `app/Modules/Product/Domain/Models/Product.php`.
   - Utilizar `$guarded = []` ou `$fillable` conforme necessidade.
   - Declarar relacionamentos com `Company`, `User` ou outros modelos.
   - Quando precisar armazenar coleções relacionadas (ex.: opções de um atributo), prefira tabelas auxiliares com modelos dedicados em vez de colunas `json`, garantindo integrações consistentes.
3. Se houver campos com valores limitados (status, tipos, etc.), crie um enum PHP em `app/Modules/<Modulo>/<Entity>/Domain/Enums`.
   - Utilize métodos auxiliares como `values()` para reaproveitar as opções em validações (`Rule::in(Enum::values())`) e resources.
   - Caso o frontend precise de labels específicas, exponha um método `label()` no enum para centralizar as traduções.

## Passo 2: Service e Filtros
1. Criar a pasta `app/Modules/Product/Domain`.
2. Criar `ProductService` estendendo `App\Modules\Core\Domain\Serivce`.
   - Injete o modelo e, opcionalmente, um filtro específico.
   - Configure `$with` para relações que precisam ser carregadas automaticamente.
   - Para sincronizar coleções relacionadas (ex.: itens filhos enviados com `id` e `delete`), utilize o helper `syncRelationCommands()` disponível em `Serivce`, mantendo a regra reaproveitável em outros domínios.

```php
class ProductService extends Serivce
{
    protected array $with = ['company'];

    public function __construct(Product $product, ProductFilter $filter)
    {
        parent::__construct($product, $filter);
    }
}
```

3. Implementar `ProductFilter` em `app/Modules/Product/Domain/Filters/ProductFilter.php` estendendo `QueryFilter`.
   - Crie métodos com o nome da chave recebida (`name`, `category_id`, etc.).
   - Utilize utilitários herdados, como `applyLike`.

```php
class ProductFilter extends QueryFilter
{
    public function name(Builder $query, string $value): void
    {
        $this->applyLike($query, 'name', $value);
    }
}
```

> O filtro é automaticamente aplicado pelo `Serivce`, permitindo uso em listagens (`index`, `list`, `findOne`).

## Passo 3: Requests e Resources
1. Criar `ProductRequest` em `app/Modules/Product/Http/Requests`.
   - Estender `FormRequest` e definir `rules()` com validações para `store` e `update`.
   - Incluir regras de relacionamento (ex.: `exists:companies,id`).
   - Para relacionamentos aninhados, adote o padrão: entradas com `id` atualizam registros existentes, `id` + `delete=true` removem explicitamente o item e objetos sem `id` representam novos registros. Evite exclusões automáticas sem o comando do frontend.

2. Criar `ProductResource` e `ProductCollection` em `app/Modules/Product/Http/Resources`.
   - `toArray` deve mapear todos os campos relevantes.
   - `ProductCollection` pode apenas definir `$collects = ProductResource::class`.

## Passo 4: Controller
1. Criar `ProductController` em `app/Modules/Product/Http/Controllers`, estendendo `CoreController`.
   - No construtor, integre `ProductService`, resource, collection e request.
   - Exemplos de chamadas adicionais: usar `authorizePermission` para regras específicas.

```php
class ProductController extends CoreController
{
    public function __construct(ProductService $service)
    {
        parent::__construct(
            $service,
            ProductResource::class,
            ProductCollection::class,
            ProductRequest::class
        );
    }
}
```

## Passo 5: ACL
1. Determinar quais permissões o módulo irá utilizar. Exemplo: reutilizar permissões existentes ou adicionar novas ao enum `Permission` (se necessário).
2. Atualizar as rotas com middleware `permission` (ver passo seguinte).
3. Caso precise de validações específicas (ex.: usuário só pode editar itens da própria empresa), use `authorizePermission` no controller ou crie lógica no service.

## Passo 6: Rotas
Adicionar as rotas em `routes/api.php` dentro do grupo autenticado.

```php
Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('products', ProductController::class)
        ->middleware([
            'index' => 'permission:product.view',
            'show' => 'permission:product.view',
            'store' => 'permission:product.create',
            'update' => 'permission:product.update',
            'destroy' => 'permission:product.delete',
        ]);
});
```

> Certifique-se de que as permissões existam no enum `Permission` e estejam atribuídas aos perfis correspondentes.

## Passo 7: Dependências e Registro
1. Caso o módulo tenha observadores, middlewares ou providers, registre-os em `AppServiceProvider` ou `bootstrap/app.php`.
2. Se precisar de bindings no container (ex.: serviços auxiliares), faça em `register()` do provider correspondente.

## Passo 8: Seeder/Factory (Opcional)
- Criar `ProductFactory` em `database/factories` para geração de dados em testes.
- Criar `ProductSeeder` em `database/seeders` para popular dados iniciais.

## Passo 9: Testes
- Criar testes de integração em `tests/Feature/Modules/Product` cobrindo rotas básicas (index, store, update, destroy) com usuários autenticados e permissões corretas.
- Utilizar `php artisan test` para validar.

## Passo 10: Documentação e Verificação Final
1. Atualizar a documentação (se necessário) com a existência do novo módulo.
2. Executar `php artisan migrate` ou `php artisan migrate:fresh` se houver novas migrations.
3. Verificar lint (`./vendor/bin/pint`) e testes (`php artisan test`).
4. Validar respostas das rotas com ferramentas como Postman ou Insomnia.

## Resumo das Abstrações Envolvidas
- **Service (`Serivce`)**: centraliza operações básicas e aplicação de filtros.
- **QueryFilter**: encapsula filtros de lista e permite reuso em múltiplas rotas.
- **CoreController**: provê endpoints REST padrão (`index`, `show`, `store`, `update`, `destroy`) e helper `authorizePermission` para verificações ACL.
- **Middleware `permission`**: realiza checagem das permissões antes da execução da rota.
- **Resources/Collections**: padronizam a resposta JSON.
- **Requests**: validam dados de entrada com regras consistentes.

Seguindo estes passos, o novo CRUD ficará alinhado com os padrões de filtros, ACL e abstrações existentes na aplicação.
