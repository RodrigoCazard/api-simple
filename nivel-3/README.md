# Nivel 3 — API de productos con Laravel 13

> Los nombres propios y elementos de la API de Laravel, como `FormRequest`,
> `middleware`, `Eloquent`, `vendor` y las claves de configuración,
> se conservan en inglés porque así están definidos por el framework. Todos los
> comentarios y las explicaciones educativas de este proyecto están en español.

> [!CAUTION]
> Este nivel fue creado con ayuda de inteligencia artificial y puede contener
> errores, decisiones discutibles o explicaciones incompletas. No uses este
> código como reemplazo de un curso ni lo copies sin entenderlo. Si decidís
> trabajar con Laravel, investigá, probá y aprendé por tu cuenta usando primero
> la [documentación oficial](https://laravel.com/docs/13.x).

Este nivel vuelve a construir la API de productos con **Laravel 13**. La meta
no es esconder lo que ocurría en los niveles anteriores, sino reconocer cómo
un framework ofrece soluciones estándar para esas tareas.

## Qué conserva y qué cambia

Se mantienen registro, login, roles, CRUD de productos, venta con control de
stock y MySQL. Las rutas de API llevan el prefijo `/api`, que es la convención
del proyecto Laravel actual.

| Niveles anteriores | Laravel |
|---|---|
| `index.php` y Router propio | `routes/api.php` |
| validadores propios | clases `FormRequest` |
| repository con PDO | modelos Eloquent |
| DTO sencillo | arreglo devuelto por `validated()` |
| middleware propio | middleware de Laravel y `auth:sanctum` |
| JWT creado por la aplicación | sesión y cookies administradas por Sanctum |
| `database.sql` | migraciones y seeders |
| `try/catch` del index | manejo central de excepciones en `bootstrap/app.php` |

Este nivel evita agregar repositories, DTOs, Resources y services propios. No
son conceptos incorrectos, pero para una primera API ocultarían el recorrido
básico de Laravel. El Form Request valida la entrada, `validated()` devuelve los
campos aceptados y Eloquent trabaja directamente con la base de datos.

## Por dónde empezar a leer

No intentes aprender todas las carpetas de Laravel al mismo tiempo. Para seguir
el CRUD de productos, leé estos archivos en este orden:

1. `routes/api.php`: relaciona cada URL con un método. El login y el registro
   están en `routes/web.php` porque necesitan la sesión y la protección CSRF.
2. `app/Http/Controllers/ProductController.php`: ejecuta cada operación.
3. `app/Http/Requests/StoreProductRequest.php`: valida los datos recibidos.
4. `app/Models/Product.php`: representa un producto de la base.
5. `database/migrations/0001_01_01_000003_create_products_table.php`: crea su tabla.

Después podés repetir el recorrido con `AuthController`, el modelo `User` y
Sanctum. Las carpetas `vendor`, `config` y `storage` no son el punto de partida.

## Cómo viaja una petición

```text
HTTP → routes/api.php → middleware → FormRequest → controller
                                                   ↓
                                            modelo Eloquent
                                                   ↓
                                                  MySQL
                                                   ↓
                                             respuesta JSON
```

- La ruta decide qué controller atiende.
- El middleware revisa la sesión, el CSRF, los límites de pedidos y el rol.
- El Form Request valida exclusivamente la entrada HTTP.
- El controller coordina y arma la respuesta.
- El controller realiza el CRUD y las pocas reglas de negocio del ejercicio.
- Eloquent consulta la base y convierte modelos y colecciones a JSON.

En una aplicación más grande se pueden separar services y Resources cuando la
cantidad de reglas o campos realmente lo justifique. Acá se prioriza que el
alumno pueda seguir una petición sin saltar por demasiados archivos.

## Forma rápida: Docker

Desde la raíz del repositorio seguí el README principal. Cuando los servicios
estén levantados, este nivel queda disponible en:

- Inicio: <http://localhost:8003>
- Productos: <http://localhost:8003/api/productos>
- Salud de Laravel: <http://localhost:8003/up>

Docker ejecuta las migraciones al iniciar. Los seeders con las cuentas y los
cinco productos de prueba se ejecutan solamente cuando `APP_ENV` no es
`production`.

## Levantarlo sin Docker

Requisitos: PHP 8.3 o superior, Composer y MySQL.

```powershell
cd nivel-3
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8003
```

El `.env.example` espera el MySQL del compose en `localhost:3307`. Si usás otro
MySQL, cambiá `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y
`DB_PASSWORD` antes de migrar.

`php artisan` es la consola de Laravel. `migrate` aplica cambios versionados de
base de datos; `--seed` agrega datos educativos.

## Usuarios exclusivamente de prueba

| Email | Contraseña | Rol |
|---|---|---|
| `admin@utu.edu.uy` | `admin123` | admin |
| `alumno@utu.edu.uy` | `alumno123` | usuario |

Estas cuentas son públicas porque forman parte de un ejercicio. Eliminá los
seeders y creá cuentas reales antes de publicar una aplicación.

## Endpoints

| Método | Ruta | Acceso | Acción |
|---|---|---|---|
| GET | `/sanctum/csrf-cookie` | público | preparar cookies y protección CSRF |
| POST | `/api/registro` | público | registrar usuario |
| POST | `/api/login` | público | iniciar la sesión y recibir la cookie |
| POST | `/api/logout` | autenticado | invalidar la sesión |
| GET | `/api/perfil` | autenticado | ver perfil |
| GET | `/api/productos` | público | listar; acepta `?categoria=audio` |
| GET | `/api/productos/{id}` | público | ver producto |
| POST | `/api/productos` | autenticado | crear producto |
| PUT | `/api/productos/{id}` | autenticado | modificar campos enviados |
| DELETE | `/api/productos/{id}` | admin | borrar si no tiene stock |
| POST | `/api/productos/{id}/vender` | autenticado | descontar stock |

"Público" significa que no exige iniciar sesión. El registro y el login siguen
exigiendo un CSRF válido porque modifican el estado de la aplicación.

Todas las peticiones JSON deberían mandar:

```http
Accept: application/json
Content-Type: application/json
```

## Autenticación SPA con Sanctum

Este nivel no crea JWT ni devuelve un token en el JSON. Usa la autenticación
SPA oficial de Sanctum, basada en la sesión de Laravel.

El orden correcto es:

1. El frontend pide `GET /sanctum/csrf-cookie`.
2. Laravel envía `XSRF-TOKEN` y una cookie llamada `nivel_3_session`.
3. El frontend manda `POST /api/login` con el header `X-XSRF-TOKEN`.
4. Laravel verifica las credenciales y regenera la sesión.
5. El navegador manda la cookie automáticamente en los siguientes pedidos.
6. `auth:sanctum` recupera al usuario desde esa sesión.
7. El logout invalida la sesión y genera un nuevo token CSRF.

La cookie `nivel_3_session` es `HttpOnly`: JavaScript no puede leerla. Laravel
la crea mediante el header HTTP `Set-Cookie`; no se llama manualmente a
`setcookie()`. En cambio, `XSRF-TOKEN` no es `HttpOnly` porque el frontend debe
copiarlo al header `X-XSRF-TOKEN`. Ese segundo valor no inicia una sesión: solo
demuestra que la petición proviene del frontend que recibió las cookies.

Las cookies viajan automáticamente, por eso también podrían enviarse desde un
sitio malicioso. La comprobación CSRF evita que ese sitio pueda ejecutar un
`POST`, `PUT` o `DELETE` válido en nombre del usuario.

El código `419` significa que falta el CSRF o que la sesión venció. El frontend
debe volver a pedir `/sanctum/csrf-cookie` y, si la sesión ya no existe, mostrar
nuevamente el login.

### Ejemplo de frontend con Axios

```javascript
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost:8003';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

await axios.get('/sanctum/csrf-cookie');

await axios.post('/api/login', {
    email: 'admin@utu.edu.uy',
    clave: 'admin123',
});

const perfil = await axios.get('/api/perfil');
```

`withCredentials` permite enviar cookies entre los puertos 5173 y 8003.
`withXSRFToken` copia automáticamente `XSRF-TOKEN` al header correspondiente.
El frontend no guarda credenciales en `localStorage` ni conoce la cookie de
sesión.

El frontend autorizado se define con `FRONTEND_ORIGIN`. Sanctum además exige
que el frontend figure en `SANCTUM_STATEFUL_DOMAINS`. En este ejemplo ambos
usan `localhost`; para producción deben compartir el mismo dominio principal,
usar HTTPS y configurar `LARAVEL_SESSION_SECURE=true`.

### Probar el flujo desde PowerShell

Este ejemplo hace lo mismo que el navegador: conserva cookies y manda el CSRF.

```powershell
$api = 'http://localhost:8003'
$origin = 'http://localhost:5173'
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Invoke-WebRequest `
    -Uri "$api/sanctum/csrf-cookie" `
    -Headers @{ Origin = $origin; Accept = 'application/json' } `
    -WebSession $session | Out-Null

$csrfCookie = $session.Cookies.GetCookies([Uri]$api) |
    Where-Object Name -eq 'XSRF-TOKEN'
$csrf = [Uri]::UnescapeDataString($csrfCookie.Value)

$headers = @{
    Origin = $origin
    Accept = 'application/json'
    'X-XSRF-TOKEN' = $csrf
}

$body = @{
    email = 'admin@utu.edu.uy'
    clave = 'admin123'
} | ConvertTo-Json

Invoke-RestMethod `
    -Uri "$api/api/login" `
    -Method Post `
    -Headers $headers `
    -ContentType 'application/json' `
    -Body $body `
    -WebSession $session

Invoke-RestMethod `
    -Uri "$api/api/perfil" `
    -Headers @{ Origin = $origin; Accept = 'application/json' } `
    -WebSession $session
```

## Desarrollo y producción

```env
APP_ENV=development
APP_DEBUG=true
```

En desarrollo, `/` muestra rutas, cuentas de prueba y la advertencia sobre IA.
En producción, `/` devuelve solamente un estado mínimo. Para publicar:

```env
APP_ENV=production
APP_DEBUG=false
```

Además hay que cambiar claves, retirar usuarios demo, usar HTTPS, configurar
CORS, revisar permisos, logs, copias de seguridad y límites de tráfico. Cambiar
dos variables no convierte automáticamente el ejemplo en una aplicación lista
para producción.

## Archivos escritos para este nivel

- `routes/api.php`: tabla de rutas.
- `app/Http/Controllers`: entrada y salida HTTP.
- `app/Http/Requests`: validación básica por endpoint.
- `app/Models`: modelos Eloquent.
- `app/Http/Middleware/EnsureAdmin.php`: control del rol admin.
- `config/sanctum.php`: dominios autorizados para usar la sesión.
- `config/cors.php`: permite cookies solamente desde el frontend configurado.
- `config/session.php`: opciones de la cookie `HttpOnly` y la sesión.
- `database/migrations`: estructura de tablas. Su
  [README](database/migrations/README.md) explica los números que ordenan cada
  migración y por qué algunas comienzan con `0001_01_01`.
- `database/seeders`: datos educativos.
- `tests/Feature/ApiTest.php`: pruebas automáticas de la API.
- `bootstrap/app.php`: alias de middleware y errores JSON globales.

Laravel contiene muchos otros archivos generados. No hace falta memorizarlos,
pero tampoco hay que borrarlos al azar: consultá qué función cumple cada uno.

## Probar y revisar calidad

```powershell
php artisan test
vendor/bin/pint --test
php artisan route:list
```

- `test` ejecuta pruebas aisladas con SQLite en memoria.
- Pint revisa el estilo oficial de Laravel.
- `route:list` muestra las rutas que Laravel registró realmente.

También podés ejecutar en orden las peticiones de [peticiones.http](peticiones.http)
con la extensión REST Client de VS Code.

## Qué investigar por cuenta propia

Este ejemplo es un punto de partida. Leé y practicá al menos:

1. [estructura de Laravel](https://laravel.com/docs/13.x/structure);
2. [rutas](https://laravel.com/docs/13.x/routing);
3. [validación](https://laravel.com/docs/13.x/validation);
4. [Eloquent](https://laravel.com/docs/13.x/eloquent);
5. [migraciones](https://laravel.com/docs/13.x/migrations);
6. [Sanctum](https://laravel.com/docs/13.x/sanctum);
7. [testing](https://laravel.com/docs/13.x/http-tests).

La mejor forma de aprender no es aceptar que el código “parece correcto”:
cambiá una regla, rompé una prueba, leé el error, buscá en la documentación y
volvé a implementarla.
