## Service (`App\Modules\Core\Domain\Serivce`)

### Visão geral
`Serivce` é a camada base que concentra operações CRUD padronizadas para todos os módulos da API. Ele recebe um `Model` e, opcionalmente, um `QueryFilter`, expondo helpers para:
- Paginação e listagem com filtros dinâmicos (`paginate`, `list`, `findOne`, etc.).
- Encapsular `create`, `update`, `delete`, garantindo uso consistente de `fill`/`save`.
- Carregar relacionamentos padrões via `$with`.
- Aplicar filtros reutilizáveis (`QueryFilter`) sem duplicar lógica em controllers.

```php
class ProductService extends Serivce
{
    protected array $with = ['company', 'category'];

    public function __construct(Product $product, ProductFilter $filter)
    {
        parent::__construct($product, $filter);
    }
}
```

### Fluxos suportados
- **Paginação**: `paginate($filters, $perPage)` usa `QueryFilter` quando disponível, permitindo endpoints de listagem com filtros `?name=foo`.
- **Listagem simples**: `list($filters)` para combos/autocompletes.
- **Busca individual**: `find`, `findOrFail`, `findOne`, `findOneOrFail`.
- **Persistência**: `create`/`update` delegam para `save`, mantendo `fill` + `save` + `refresh`.
- **Remoção**: `delete` apenas invoca `delete()` no modelo.

### Reaproveitando regras com `syncRelationCommands`
Quando o frontend envia coleções relacionadas (ex.: opções de um atributo) com comandos explícitos (`id`, `delete`), use `syncRelationCommands()` para aplicar a regra de criar/atualizar/remover registros filhos sem duplicar código entre services.

Assinatura:
```php
protected function syncRelationCommands(
    Model $parent,
    string $relationName,
    array $commands,
    array $allowedFields = [],
    ?callable $preparePayload = null
): void
```

Parâmetros:
- `parent`: modelo principal já carregado.
- `relationName`: nome da relação no modelo (ex.: `options`, `items`).
- `commands`: array de payloads enviados pelo frontend (`['id' => 10, 'value' => 'X']`).
- `allowedFields`: lista de campos permitidos quando não é passado um callback.
- `preparePayload`: callback opcional para normalizar/validar o payload (`fn(array $payload, bool $isUpdate)`).

Contrato esperado para cada item na coleção:
- **Criação**: objeto sem `id`. Campos válidos serão usados para `create`.
- **Atualização**: objeto com `id`. Pelo menos um campo deve acompanhar para atualização.
- **Remoção**: objeto com `id` e `delete = true`. O registro só é excluído se essa flag for enviada.

Exemplo aplicado no `AttributeService`:
```php
$this->syncRelationCommands(
    $attribute,
    'options',
    $options,
    ['value', 'order'],
    fn (array $payload, bool $isUpdate) => $this->prepareOptionPayload($payload, $isUpdate)
);
```

Boas práticas:
- Use `allowedFields` para garantir que apenas colunas previstas sejam persistidas.
- Sempre valide o contrato na `FormRequest` (IDs existentes, flag `delete`, campos obrigatórios).
- Prefira callbacks quando precisar ajustar tipos (ex.: converter `order` para inteiro) ou aplicar defaults.

### Quando estender o Service
- **Regra específica**: sobrescreva `create`/`update` quando precisar aplicar lógica antes/depois da persistência (ex.: gerar slug, sincronizar relações).
- **Carregamento padrão**: ajuste `$with` para relacionamentos necessários em responses (`Resource` aproveita o eager loading).
- **Filtros customizados**: utilize `QueryFilter` para manter as regras de busca centralizadas.
- **Sincronização de relacionamentos**: chame `syncRelationCommands` sempre que o domínio tiver coleções aninhadas com edição explícita pelo frontend.

Seguindo esses padrões, novos módulos herdam comportamento consistente, mantendo controllers enxutos e regras reutilizáveis entre domínios.

