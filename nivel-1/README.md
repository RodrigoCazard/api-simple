# Nivel 1 — El recorrido explícito

Esta carpeta contiene una API independiente para aprender el recorrido de un
pedido antes de incorporar el Router, los middlewares, los validators y los
DTOs de [nivel-2](../nivel-2).

Las dos versiones trabajan con los mismos usuarios, productos y reglas de
negocio. No son idénticas en la forma de manejar la sesión:

- Nivel 1 devuelve el JWT en el JSON y el cliente lo manda en
  `Authorization: Bearer <token>`.
- Nivel 2 guarda el JWT en una cookie HttpOnly y por eso tiene un endpoint
  `/logout` para borrar esa cookie.

Cada carpeta tiene su propio `config.php`, `.env`, `vendor/`, `database.sql` y
código. Ningún nivel necesita archivos PHP del otro para funcionar. Por defecto
ambas apuntan a la base `utu_demo`; se puede cambiar `DB_NAME` si se quieren
datos separados.

## Qué se aprende en este nivel

| Responsabilidad | Cómo se resuelve en nivel 1 |
|---|---|
| Enrutar | Un `switch (true)` visible en `index.php` |
| Controllers | Clases independientes, sin una clase `Controller` padre |
| Leer JSON | La función `requestData()` de `core/helpers.php` |
| Validar entrada | Condiciones escritas directamente en cada controller |
| Exigir login o rol | Cada método llama manualmente a `requireLogin()` o `requireAdmin()` |
| Transportar datos | Parámetros y arreglos; todavía no hay DTOs |
| Reglas del negocio | Services |
| Consultar la base | Repositories con consultas preparadas PDO |

Las funciones sueltas de `core/helpers.php` son ayudas compartidas. Los
controllers no son funciones sueltas: `AuthController`, `ProductController` y
`HomeController` ya son clases.

En [nivel-2](../nivel-2/README.md), el mismo tipo de trabajo se organiza con
una clase Router, middleware, una clase Controller padre, validators y DTOs.
La comparación permite ver qué repetición resuelve cada herramienta.

## Recorrido de un pedido

```text
Pedido HTTP
    ↓
index.php lee el método y la dirección
    ↓
el switch elige un controller y un método
    ↓
el controller exige login cuando corresponde y valida la entrada
    ↓
el service aplica las reglas del negocio
    ↓
el repository consulta o modifica MySQL
    ↓
Response devuelve JSON
```

No hay Router, middleware, Validator ni DTO como clases separadas. Eso es
intencional: primero se ve el trabajo escrito directamente y después se aprende
a extraerlo.

## Endpoints

| Método | Dirección | Acción | Acceso |
|---|---|---|---|
| `GET` | `/` | Ver la ayuda de la API | Público |
| `POST` | `/registro` | Crear una cuenta | Público |
| `POST` | `/login` | Iniciar sesión y obtener un JWT | Público |
| `GET` | `/perfil` | Ver el usuario autenticado | Bearer token |
| `GET` | `/productos` | Listar productos | Público |
| `GET` | `/productos/{id}` | Ver un producto | Público |
| `POST` | `/productos` | Crear un producto | Bearer token |
| `PUT` | `/productos/{id}` | Modificar campos de un producto | Bearer token |
| `DELETE` | `/productos/{id}` | Eliminar un producto | Solo admin |
| `POST` | `/productos/{id}/vender` | Vender y descontar stock | Bearer token |

El listado acepta el filtro opcional `GET /productos?categoria=audio`.

## Cómo se usa el token en nivel 1

Cuando el login es correcto, la respuesta contiene el token. En las peticiones
protegidas se manda mediante esta cabecera:

```http
Authorization: Bearer eyJhbGciOiJIUzI1NiJ9...
```

Nivel 1 no necesita un endpoint `/logout`: como el servidor no creó una cookie,
cerrar sesión significa que el cliente elimina el token que había guardado. Un
token copiado seguiría siendo válido hasta su vencimiento; esa limitación se
estudia con más profundidad al mejorar la seguridad.

## Cómo levantar la API

Necesitás PHP 8 con la extensión `pdo_mysql` y un servidor MySQL. XAMPP y WAMP
ya incluyen ambos.

### 1. Crear la base

Importá [database.sql](database.sql):

```bash
mysql -u root -p < database.sql
```

También podés importarlo desde phpMyAdmin.

### 2. Crear el archivo `.env`

```bash
cp .env.example .env
```

En Windows CMD:

```bat
copy .env.example .env
```

Revisá `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `SECRET_KEY` y `APP_ENV`.
Usá `APP_ENV=development` para ver la ayuda educativa en `/`; en un servidor
público usá `APP_ENV=production`. El `.env` contiene configuración local y no
se sube a Git.

### 3. Instalar las dependencias

```bash
composer install
```

Composer instala `firebase/php-jwt`, la librería utilizada para crear y
verificar los tokens.

### 4. Iniciar el servidor

```bash
cd nivel-1
php -S localhost:8000 index.php
```

Después entrá a <http://localhost:8000>.

También podés levantar los tres niveles y MySQL juntos mediante Docker siguiendo
la guía del [README principal](../README.md#levantar-todo-con-docker).

## Validaciones y errores

La validación básica de entrada está escrita dentro de los controllers para que
sea visible. Incluye tipos, campos obligatorios, IDs, números no negativos y
los campos opcionales de `update()`.

Las reglas que necesitan conocer el estado del sistema permanecen en los
services. Algunos ejemplos son impedir nombres repetidos, no vender más stock
del disponible y no borrar productos con mercadería.

Todo el switch está rodeado por un `try/catch`: los errores inesperados se
registran en el servidor y la API devuelve un mensaje genérico, sin exponer SQL
ni información interna.

## Usuarios de prueba

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin@utu.edu.uy` | `admin123` | `admin` |
| `alumno@utu.edu.uy` | `alumno123` | `usuario` |

Las contraseñas se guardan como hashes generados con `password_hash()`, nunca en
texto plano.

## Para continuar

La documentación general está en [docs](../docs):

- [HTTP y REST](../docs/http-y-rest.md)
- [Variables de entorno](../docs/variables-de-entorno.md)
- [Git y `.gitignore`](../docs/git-y-gitignore.md)
- [Seguridad contra SQL injection y XSS](../docs/seguridad-sqli-xss.md)

Cuando este recorrido esté claro, [nivel-2](../nivel-2/README.md) muestra cómo
extraer el switch, la autenticación, la validación y el transporte de datos a
componentes específicos.
