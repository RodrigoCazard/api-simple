# Mejoras opcionales para acercar el nivel 2 a una API profesional

El nivel 2 ya tiene una base correcta para aprender a construir una API:
separa controllers, validators, DTOs, services, repositories y models; utiliza
consultas preparadas; guarda las contraseñas con un hash; maneja la
autenticación mediante JWT y centraliza los errores inesperados.

Este documento no describe tareas obligatorias. Es una hoja de ruta para el
estudiante que quiera seguir practicando y acercar el proyecto a una API de
producción sin convertirlo de golpe en un sistema demasiado complejo.

## Prioridad 1: interpretar correctamente la entrada HTTP

Actualmente, si el cuerpo está vacío o contiene un JSON mal escrito,
`Controller::requestData()` devuelve `[]` en los dos casos. Sería mejor poder
distinguirlos.

Una versión más estricta debería:

- Aceptar `application/json` en los endpoints que esperan JSON.
- Responder `415 Unsupported Media Type` cuando el tipo de contenido no sea el
  esperado.
- Detectar un JSON mal formado y responder `400 Bad Request`.
- Limitar el tamaño máximo del cuerpo y responder `413 Payload Too Large`.
- Rechazar campos desconocidos o avisar claramente que no se utilizan.
- Validar longitudes máximas además de longitudes mínimas.

Los máximos deberían coincidir con la base. Por ejemplo, si `nombre` es
`VARCHAR(150)`, el validator no debería permitir más de 150 caracteres. Un
dato demasiado largo es un error del cliente, no un error interno `500`.

## Prioridad 2: proteger las cookies contra CSRF

La cookie del JWT tiene `HttpOnly` y `SameSite=Lax`, lo cual es una buena base.
Sin embargo, una cookie se envía automáticamente en los pedidos y por eso los
endpoints que modifican datos también necesitan protección contra CSRF.

Una mejora educativa posible es crear un `CsrfMiddleware` para `POST`, `PUT`,
`PATCH` y `DELETE`. Este middleware podría:

1. Verificar que la cabecera `Origin` coincida con `FRONTEND_ORIGIN`.
2. Exigir un token CSRF en una cabecera como `X-CSRF-Token`.
3. Rechazar el pedido con `403 Forbidden` cuando la comprobación falle.

Es importante comprender que CORS controla si un navegador puede leer una
respuesta. No debería utilizarse como la única protección para impedir que un
pedido malicioso se ejecute.

Referencia: [OWASP CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html).

## Prioridad 3: vender stock de manera atómica

El proceso actual de venta hace tres pasos:

```text
leer el stock → comprobar la cantidad → guardar el nuevo stock
```

Si llegan dos ventas al mismo tiempo, ambas podrían leer el mismo stock antes
de que la otra lo cambie. A este problema se lo llama **condición de carrera**.

Para un descuento sencillo se puede hacer una actualización condicional:

```sql
UPDATE productos
SET stock = stock - :cantidad
WHERE id = :id
  AND stock >= :cantidad;
```

La base realiza la comprobación y el descuento como una sola operación. Luego
el repository puede mirar `rowCount()` para saber si realmente se vendió.

Cuando una operación necesita modificar varias tablas, por ejemplo stock,
venta y detalle de venta, conviene utilizar una transacción y, cuando sea
necesario, un bloqueo como `SELECT ... FOR UPDATE`.

Referencia: [MySQL: InnoDB Locking Reads](https://dev.mysql.com/doc/refman/8.4/en/innodb-locking-reads.html).

## Prioridad 4: repetir las reglas críticas en la base

El validator ayuda al usuario y el service protege las reglas del negocio,
pero la base debe ser la última barrera contra datos imposibles.

Se podrían agregar restricciones como estas:

```sql
UNIQUE (nombre)
CHECK (precio >= 0)
CHECK (stock >= 0)
CHECK (rol IN ('usuario', 'admin'))
```

Si el nombre del producto debe ser único, esa regla debe comprobarse al crear
y también al cambiar el nombre durante un update. La restricción `UNIQUE` es
necesaria aunque el service consulte antes, porque dos pedidos simultáneos
podrían superar esa consulta previa.

Una duplicación esperada debería responder `409 Conflict`. Los errores de base
inesperados sí deben seguir llegando al manejador general y convertirse en una
respuesta `500` sin mostrar información interna.

También conviene decidir qué significa el campo `activo`:

- Si se utiliza borrado lógico, `DELETE` cambia `activo` a `0` y los listados
  normales excluyen esos registros.
- Si se utiliza borrado físico, se puede eliminar el campo si no cumple otra
  función.

## Prioridad 5: reforzar la autenticación

Las siguientes prácticas permitirían continuar la parte de seguridad:

- Limitar los intentos de `/login` y `/registro` y responder `429 Too Many
  Requests` cuando se supera el límite.
- Aumentar la longitud mínima de la contraseña y agregar una longitud máxima
  razonable, por ejemplo 128 caracteres.
- Exigir que `SECRET_KEY` sea una clave larga generada al azar.
- Agregar y verificar datos del JWT como emisor (`iss`) y audiencia (`aud`).
- Comprobar que el usuario continúe activo y que conserve su rol en la base.
- Hacer que HTTPS sea obligatorio en producción.
- Asegurar que la cookie siempre tenga `Secure` en producción.

Cerrar sesión elimina la cookie del navegador, pero una copia robada del JWT
seguiría funcionando hasta su vencimiento. En un nivel posterior se puede
estudiar alguna de estas alternativas:

- Tokens de acceso con una vida más corta.
- Una versión de sesión almacenada en el usuario.
- Una lista de tokens revocados identificados mediante `jti`.
- Un sistema de access token y refresh token.

No es necesario implementar todas juntas para comprender la idea.

Referencias:

- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [OWASP Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [OWASP REST Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html)

## Prioridad 6: agregar pruebas automatizadas

Probar manualmente con `peticiones.http` es útil para aprender, pero una API
cercana a producción debería poder comprobarse automáticamente.

Un conjunto inicial de pruebas podría cubrir:

- Validaciones de registro, login y productos.
- Normalización y tipos de cada DTO.
- Registro y login correctos.
- Email y nombre de producto repetidos.
- Rutas que exigen autenticación.
- Rutas exclusivas del administrador.
- Producto inexistente.
- Venta sin stock suficiente.
- Dos ventas simultáneas sobre el mismo stock.
- JSON mal formado y tipo de contenido incorrecto.
- Creación y eliminación de la cookie al iniciar y cerrar sesión.
- Respuestas `500` que no exponen consultas, contraseñas ni trazas internas.

Se pueden comenzar con pruebas unitarias de validators y DTOs, porque no
necesitan una base. Después se agregan pruebas de integración que recorran el
endpoint completo y utilicen una base exclusiva para testing.

## Mejoras para una segunda etapa

Estas tareas también son valiosas, pero pueden realizarse después de las seis
prioridades anteriores.

### No usar `float` para dinero

Los números `float` pueden tener pequeñas diferencias de precisión. Para
precios se pueden guardar centésimos como enteros o trabajar con strings
decimales. La base ya utiliza `DECIMAL(10, 2)`, pero el DTO actualmente lo
convierte a `float`.

### Diferenciar `PUT` y `PATCH`

`PUT` normalmente representa el reemplazo completo de un recurso. Si el
cliente manda solamente los campos que desea cambiar, la operación se parece
más a `PATCH`.

Se puede elegir una de estas opciones:

- Mantener `PUT` y exigir todos los campos.
- Cambiar el update parcial a `PATCH`.

Referencias: [RFC 9110: PUT](https://www.rfc-editor.org/rfc/rfc9110.html#name-put)
y [RFC 5789: PATCH](https://www.rfc-editor.org/rfc/rfc5789.html).

### Usar códigos HTTP más precisos

Se pueden incorporar gradualmente:

| Código | Uso |
|---|---|
| `405` | La ruta existe, pero no admite ese método HTTP |
| `409` | Hay un conflicto, por ejemplo un email repetido |
| `413` | El cuerpo del pedido es demasiado grande |
| `415` | El tipo de contenido no es aceptado |
| `422` | El JSON es válido, pero sus datos no pasan la validación |
| `429` | Se realizaron demasiados intentos |

Cuando se responde `405`, también se debería enviar la cabecera `Allow` con
los métodos aceptados.

### Paginar los listados

`GET /productos` devuelve actualmente todos los registros. Con miles de
productos sería demasiado costoso. Se podrían aceptar parámetros como:

```text
GET /productos?pagina=2&limite=20
```

El límite debe tener un máximo definido por el servidor para evitar que el
cliente solicite toda la tabla de una vez.

### Separar las reglas de negocio de las respuestas HTTP

La idea de las capas dice que el service no debería conocer HTTP. Actualmente
los services llaman a `Response::error()`, por lo que todavía existe ese
acoplamiento.

En una evolución del proyecto, el service podría lanzar excepciones propias:

```text
ProductNotFoundException
InsufficientStockException
DuplicateProductException
```

Un manejador global convertiría después cada excepción en `404`, `409` u otro
código HTTP. Así, el mismo service podría utilizarse desde una API, una tarea
automática o un programa de consola.

### Agregar cabeceras, logs y un identificador de pedido

En producción conviene estudiar:

- `Cache-Control: no-store` para respuestas sensibles.
- `X-Content-Type-Options: nosniff`.
- HSTS cuando toda la aplicación funciona mediante HTTPS.
- Un identificador distinto para cada pedido.
- Logs estructurados con fecha, ruta, método, usuario y ese identificador.
- Registro de eventos importantes como intentos de login, ventas y operaciones
  administrativas, sin guardar contraseñas ni tokens.

### Automatizar la calidad del código

Un nivel posterior podría incorporar:

- Namespaces y autoload PSR-4 mediante Composer.
- `declare(strict_types=1);`.
- Un formateador compatible con PSR-12.
- PHPStan o Psalm para análisis estático.
- OpenAPI para documentar formalmente los endpoints.
- Migraciones de base en lugar de un único archivo SQL.
- Integración continua para ejecutar lint, pruebas y `composer audit`.

También es importante mantener sincronizados `composer.json` y
`composer.lock`, para que todos instalen exactamente las mismas versiones con
`composer install`.

## Orden recomendado para practicar

Para no hacer todos los cambios al mismo tiempo, se recomienda este orden:

1. Mejorar la lectura y validación del JSON.
2. Crear la protección CSRF.
3. Hacer atómica la venta de stock.
4. Agregar restricciones a la base y manejar los conflictos.
5. Incorporar rate limiting y reforzar JWT, cookies y HTTPS.
6. Escribir las pruebas automatizadas.
7. Recién después agregar paginación, excepciones de negocio, OpenAPI y las
   herramientas automáticas de calidad.

Cada mejora debería hacerse en un cambio pequeño, probarse y documentarse
antes de comenzar la siguiente.

## Resumen para no errarle

- El nivel 2 ya es una buena base educativa; estas mejoras son opcionales.
- Las primeras mejoras deberían ser JSON estricto, CSRF, stock atómico,
  restricciones de base, autenticación más resistente y pruebas.
- La seguridad se construye con varias barreras; no depende de una sola clase
  o validación.
