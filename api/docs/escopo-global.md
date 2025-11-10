# Escopo Global de `company_id`

## Visão geral
O escopo global é aplicado automaticamente a todas as instâncias de modelos do Eloquent que possuam a coluna `company_id`. Ele garante que consultas retornem apenas registros pertencentes à empresa associada ao contexto atual da aplicação.

## Componentes principais
- `App\Scopes\CompanyScope`: adiciona o critério `where company_id = empresa atual` sempre que possível.
- `App\Providers\AppServiceProvider`: registra o escopo durante o boot da aplicação, avaliando cada model carregado.
- `App\Modules\Company\Domain\CompanyRegistry`: mantém a empresa ativa, exposta pelo alias de container `company`.

## Fluxo de funcionamento
1. Durante o processo de boot, o `AppServiceProvider` registra um callback `Model::booted`.
2. Para cada model carregado, o callback verifica se a tabela existe e possui a coluna `company_id`.
3. Atendendo aos critérios, o `CompanyScope` é associado ao model.
4. Ao executar consultas, o escopo chama `app('company')->getCompany()` para obter a empresa ativa.
5. Caso não haja empresa registrada ou o binding não esteja disponível, o escopo é ignorado, mantendo a consulta original.

## Pré-requisitos
- A aplicação precisa registrar uma empresa via `CompanyRegistry::registerCompany`.
- A tabela do model deve conter a coluna `company_id`.
- As migrations correspondentes devem estar aplicadas para que o `Schema` reconheça a coluna.

## Situações especiais
- Se o binding `company` ainda não estiver disponível (por exemplo, em contextos de console ou testes), nenhum filtro adicional é aplicado.
- Quando `getCompany()` lança exceção por ausência de empresa, o escopo aborta silenciosamente para evitar que a aplicação quebre.

## Personalização
- Para ignorar o escopo em uma consulta específica, utilize métodos padrão do Eloquent, como `withoutGlobalScope(CompanyScope::class)`.
- Caso um model precise de lógica adicional, métodos como `booted()` no próprio model podem coexistir com o escopo global sem conflitos.


