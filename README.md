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
computadora. Desde la raíz del proyecto:

### 1. Crear la configuración local

```powershell
Copy-Item .env.docker.example .env
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

El segundo comando genera una clave. Copiala después de `SECRET_KEY=` dentro
del nuevo archivo `.env`. Este archivo es local y Git lo ignora.

### 2. Construir y levantar los containers

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

Para probar nivel 2 con [peticiones.http](nivel-2/peticiones.http), cambiá su
variable inicial por:

```http
@url = http://localhost:8002
```

### 3. Detenerlos

```powershell
docker compose down
```

Los datos quedan guardados en un volumen. Para borrar también la base y hacer
que `database.sql` vuelva a ejecutarse desde cero:

```powershell
docker compose down -v
```

`down -v` elimina los datos de MySQL del entorno Docker y no se puede deshacer.

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
