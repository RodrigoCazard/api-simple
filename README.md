# API REST en PHP — proyecto para aprender

Una API de productos (login con JWT, roles, CRUD completo y MySQL con PDO),
armada dos veces con distinto nivel de herramientas. Cada carpeta es
una app **completamente independiente** en código — copiá cualquiera,
importale su `database.sql` a un MySQL y anda sola.

| Nivel | Carpeta | Cómo enruta | Autenticación y entrada |
|---|---|---|---|
| 1 | [nivel-1/](nivel-1) | `switch` gigante en `index.php` | JWT en `Authorization`; validación en controllers |
| 2 | [nivel-2/](nivel-2) | clase `Router` + tabla de rutas | cookie HttpOnly; validators y DTOs |

La idea es recorrerlos en orden: en nivel 1, `index.php` muestra de forma
explícita cómo se elige cada controller; nivel 2 muestra por qué conviene sacar
el enrutado, la autenticación y la validación a componentes específicos cuando
la aplicación empieza a crecer.

Cada carpeta tiene su propio README con el detalle de cómo levantarla, sus
endpoints y las decisiones propias de ese nivel. El README de
[nivel-2](nivel-2/README.md) profundiza en las capas, los DTOs, los validators,
las cookies y el middleware.

## Levantar todo con Docker

Con Docker Desktop abierto no hace falta instalar PHP, Composer ni MySQL en la
computadora.

### 1. Descargar el proyecto

Si todavía no lo tenés:

```powershell
git clone https://github.com/RodrigoCazard/api-simple.git
cd api-simple
```

Todos los comandos siguientes se ejecutan desde esa carpeta raíz, donde está
`compose.yaml`.

### 2. Crear la configuración local

```powershell
Copy-Item .env.docker.example .env
```

Generá una clave aleatoria desde PowerShell:

```powershell
$bytes = [byte[]]::new(32)
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
[BitConverter]::ToString($bytes).Replace('-', '').ToLower()
```

Copiá el resultado después de `SECRET_KEY=` dentro del nuevo archivo `.env`:

```env
SECRET_KEY=aca_va_la_clave_generada
```

No dejes esa variable vacía. El `.env` contiene la configuración local y Git lo
ignora para evitar que se publiquen secretos.

### 3. Construir y levantar los containers

```powershell
docker compose up --build
```

La primera ejecución descarga las imágenes, instala las dependencias con
Composer y crea las tablas con sus datos de ejemplo. Cuando aparezcan los logs
de Apache y MySQL, quedan disponibles:

| Servicio | Dirección desde la computadora |
|---|---|
| Nivel 1 | <http://localhost:8001> |
| Nivel 2 | <http://localhost:8002> |
| MySQL | `localhost:3307` |

Los containers se comunican con MySQL mediante el nombre interno `database`;
por eso no se cambia `DB_HOST` manualmente.

### 4. Comprobar que estén funcionando

Dejá la terminal anterior abierta y abrí otra en la raíz del proyecto:

```powershell
docker compose ps
```

Deberían aparecer `database`, `nivel-1` y `nivel-2` con estado `Up`; la base
también debería indicar `healthy`.

Abrí estas direcciones en el navegador:

- <http://localhost:8001> — ayuda de nivel 1.
- <http://localhost:8001/productos> — cinco productos desde nivel 1.
- <http://localhost:8002> — ayuda de nivel 2.
- <http://localhost:8002/productos> — cinco productos desde nivel 2.

Los usuarios iniciales son:

| Email | Contraseña | Rol |
|---|---|---|
| `admin@utu.edu.uy` | `admin123` | `admin` |
| `alumno@utu.edu.uy` | `alumno123` | `usuario` |

## Probar nivel 2: cookie HttpOnly

La opción más sencilla es abrir [peticiones.http](nivel-2/peticiones.http) en
VS Code con la extensión **REST Client**.

Como Docker publica nivel 2 en el puerto 8002, cambiá la variable inicial por:

```http
@url = http://localhost:8002
```

Después ejecutá los pedidos en este orden:

1. `LOGIN como administrador`.
2. `MI PERFIL`.
3. Cualquier creación, modificación o venta de productos.
4. `LOGOUT`.
5. `MI PERFIL sin iniciar sesión`, que ahora debe responder `401`.

REST Client conserva automáticamente la cookie creada por el login. El JWT no
aparece en el JSON ni se copia manualmente porque está dentro de una cookie
`HttpOnly`.

También se puede comprobar desde PowerShell:

```powershell
$body = @{
    email = 'admin@utu.edu.uy'
    clave = 'admin123'
} | ConvertTo-Json

Invoke-RestMethod `
    -Uri 'http://localhost:8002/login' `
    -Method Post `
    -ContentType 'application/json' `
    -Body $body `
    -SessionVariable apiSession

Invoke-RestMethod `
    -Uri 'http://localhost:8002/perfil' `
    -WebSession $apiSession
```

`-SessionVariable` guarda la cookie recibida y `-WebSession` la vuelve a enviar.

## Probar nivel 1: Bearer token

Nivel 1 devuelve el JWT en el JSON del login. Este ejemplo lo guarda en una
variable y después lo manda mediante `Authorization`:

```powershell
$body = @{
    email = 'admin@utu.edu.uy'
    clave = 'admin123'
} | ConvertTo-Json

$login = Invoke-RestMethod `
    -Uri 'http://localhost:8001/login' `
    -Method Post `
    -ContentType 'application/json' `
    -Body $body

$headers = @{
    Authorization = "Bearer $($login.datos.token)"
}

Invoke-RestMethod `
    -Uri 'http://localhost:8001/perfil' `
    -Headers $headers
```

La última respuesta debe contener los datos de `admin@utu.edu.uy`.

## Detener o reiniciar el entorno

Para detener y eliminar los containers y la red:

```powershell
docker compose down
```

Los datos quedan guardados en un volumen. Para borrar también la base y hacer
que `database.sql` vuelva a ejecutarse desde cero:

```powershell
docker compose down -v
```

`down -v` elimina los datos de MySQL del entorno Docker y no se puede deshacer.

Si algo falla, mirá los logs:

```powershell
docker compose logs -f
```

La explicación detallada de imágenes, containers, puertos, red y volumen está
en [docs/docker.md](docs/docker.md).

> **Sobre el idioma:** el código (clases, métodos, variables, carpetas) está en
> **inglés**, que es la convención en programación. Las explicaciones, los
> mensajes y las direcciones de la API quedan en **español**.

## Documentación general

Temas que no cambian entre niveles, compartidos en [docs/](docs):

| Doc | De qué habla |
|---|---|
| [docs/http-y-rest.md](docs/http-y-rest.md) | métodos HTTP, códigos de estado, qué es REST |
| [docs/variables-de-entorno.md](docs/variables-de-entorno.md) | qué es un `.env`, cómo se lee, por qué no se sube a git |
| [docs/git-y-gitignore.md](docs/git-y-gitignore.md) | qué archivos van a git y cuáles no, y por qué |
| [docs/seguridad-sqli-xss.md](docs/seguridad-sqli-xss.md) | inyección SQL y XSS: qué son, cómo se evitan |
| [docs/uso-de-ia.md](docs/uso-de-ia.md) | cómo usar la IA para aprender (y no para copiar sin entender) |
| [docs/docker.md](docs/docker.md) | imágenes, containers, Compose, red, volumen y comandos |
