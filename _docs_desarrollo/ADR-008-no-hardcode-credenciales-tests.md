# ADR-008 — No hardcodear credenciales en tests

**Fecha**: 2026-06-04
**Estado**: ✅ Aplicado
**Contexto**: Hito 003 — Sales + Inventory

---

## Contexto

Durante la implementación de los tests Feature de los módulos Sales e Inventory, se escribió código de la siguiente forma:

```php
$user = User::create([
    'codigo' => 'VEND-001',
    'username' => 'vendedor',
    'name' => 'Vendedor',
    'nombre_completo' => 'Vendedor Test',
    'email' => 'vendedor@test.com',
    'dni' => '12345678',
    'password' => bcrypt('vendedor123'),
    'activo' => true,
]);
$user->assignRole('Vendedor');

$this->token = $this->postJson('/api/auth/login', [
    'login' => 'vendedor',
    'password' => 'vendedor123',
])->json('token');
```

Esto se repitió en `SaleTest.php` (3 usuarios) e `InventoryTest.php` (1 usuario), totalizando **4 credenciales literales** y **4 llamadas innecesarias** al endpoint real de login.

## Problemas identificados

| # | Problema | Impacto |
|---|---|---|
| 1 | Contraseñas literales (`vendedor123`, `admin123`, `log123`, `alm123`) en código fuente | Riesgo de seguridad si se commitean a repositorio público |
| 2 | Duplicación de la estructura de User (~10 campos) en 4 lugares | Viola DRY, dificulta mantenimiento ante cambios de modelo |
| 3 | Llamadas reales a `/api/auth/login` desde cada test | Acopla el test a la API de auth + ejecuta hashing de bcrypt innecesario |
| 4 | Crecimiento cuadrático al añadir nuevos roles | Cada rol nuevo = 2 bloques de código más a duplicar |

## Decisión

Adoptar el siguiente patrón único para todos los tests Feature de la API:

### 1. Crear usuarios con `User::factory()`

```php
use Database\Factories\UserFactory;

// Usuario genérico
$user = User::factory()->create();

// Usuario con estado específico (e.g. factory ya define state admin())
$admin = User::factory()->admin()->create();
$admin->assignRole('Admin');
```

### 2. Autenticar con Sanctum token, no con login endpoint

```php
$token = $user->createToken('test')->plainTextToken;
```

### 3. Exponer helpers por rol en el test class

```php
private function asVendedor(): static
{
    return $this->withToken($this->vendedorToken);
}

private function asAdmin(): static
{
    return $this->withToken($this->adminToken);
}
```

### 4. Uso en los tests

```php
public function test_crear_venta(): void
{
    $this->asVendedor()
        ->postJson('/api/sales', $payload)
        ->assertCreated();
}
```

## Consecuencias

### Positivas

- **Seguridad**: cero contraseñas literales en `tests/`
- **DRY**: factory centraliza la estructura de User
- **Velocidad**: tests no ejecutan hashing de bcrypt 4 veces ni validan lógica de AuthController
- **Desacoplamiento**: tests de Sales/Inventory no dependen de la API de Auth (romper Auth no rompe otros tests)
- **Escalabilidad**: añadir un nuevo rol = 1 línea en factory + 1 método helper

### Negativas / Trade-offs

- **Sincronización**: si el modelo User cambia (nuevos campos obligatorios), el factory debe actualizarse
  - *Mitigación*: usar `User::factory()->state([...])` en lugar de tocar el factory base cuando sea muy específico
- **Tests E2E de Sanctum** no usan este patrón — ellos sí ejercitan el endpoint real de login con credenciales reales del seeder

## Excepciones documentadas

- Tests que **verifican la API de Auth** (`AuthControllerTest`, futuros) **sí** deben llamar a `/api/auth/login` porque ese es el sujeto bajo prueba.
- Tests E2E (Playwright) usan credenciales reales del seeder, pero no commitean passwords — usan variables de entorno.

## Archivos refactorizados

- `tests/Feature/SaleTest.php` (8 tests, 3 usuarios)
- `tests/Feature/InventoryTest.php` (2 tests, 1 usuario)

## Referencias

- AGENTS.md → sección "Testing (PHPUnit / Feature)"
- Laravel docs: [Database Testing — Factories](https://laravel.com/docs/12.x/database-testing#defining-model-factories)
- Laravel docs: [Sanctum — Testing](https://laravel.com/docs/12.x/sanctum#testing)
