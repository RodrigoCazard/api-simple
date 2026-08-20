# HTTP y REST

## Qué es HTTP

Es el protocolo (el idioma acordado) con el que un cliente (un navegador, una
app, Postman) le habla a un servidor por internet. Cada pedido tiene siempre
la misma forma: un **método**, una **dirección**, y opcionalmente un
**cuerpo** con datos. Cada respuesta tiene un **código de estado** y
opcionalmente un cuerpo.

HTTP **no tiene memoria**: cada pedido es independiente del anterior. El
servidor no sabe por sí solo que dos pedidos vienen de la misma persona. Por
eso se usa un JWT: en `nivel-1` viaja en `Authorization` y en `nivel-2`
viaja en una cookie HttpOnly (ver [Token.php](../nivel-2/core/Token.php)).

## Los métodos (verbos)

Cada método comunica una *intención* distinta sobre el mismo recurso:

| Método | Intención | Ejemplo en esta API |
|---|---|---|
| `GET` | leer, sin cambiar nada | `GET /productos` |
| `POST` | crear algo nuevo | `POST /productos` |
| `PUT` | reemplazar/modificar algo que ya existe | `PUT /productos/3` |
| `DELETE` | borrar | `DELETE /productos/3` |

Un detalle que suele confundir al principio: **la dirección puede ser
idéntica** y lo que cambia es el método.

```
GET    /productos      -> listar
POST   /productos      -> crear
```

No existe `/crearProducto`. Eso es justamente la idea de REST (ver más
abajo): las direcciones nombran **recursos** (sustantivos: "productos"), y
los métodos dicen **qué hacer** con ellos (verbos).

### Idempotencia (una palabra que vale la pena conocer)

Un método es *idempotente* si pedirlo una vez o pedirlo diez veces seguidas
da el mismo resultado final. `GET`, `PUT` y `DELETE` son idempotentes: pedir
`DELETE /productos/3` diez veces termina igual que pedirlo una sola vez (la
primera lo borra, las siguientes ya no tienen nada para borrar). `POST` **no**
es idempotente: mandarlo diez veces crea diez productos.

## Los códigos de estado

El primer dígito ya dice la categoría:

| Rango | Significa |
|---|---|
| `2xx` | salió bien |
| `4xx` | error del que pidió (mandó algo mal) |
| `5xx` | error del servidor (se rompió algo del lado de acá) |

Los que usa esta API:

| Código | Nombre | Cuándo |
|---|---|---|
| `200` | OK | salió todo bien |
| `201` | Created | se creó algo (`POST` que tuvo éxito) |
| `204` | No Content | salió bien, no hay nada que devolver (lo usa el `OPTIONS` de CORS) |
| `400` | Bad Request | los datos vinieron mal, o no se cumple una regla |
| `401` | Unauthorized | no sabemos quién sos: falta la sesión o venció el JWT |
| `403` | Forbidden | sabemos quién sos, pero no tenés permiso |
| `404` | Not Found | esa dirección, o ese registro, no existe |

**401 vs 403 es la confusión más común:** 401 es una pregunta de
*autenticación* (¿quién sos?); 403 es una pregunta de *autorización* (ya sé
quién sos, ¿te dejo?). Ejemplo real del proyecto: pedir `DELETE /productos/3`
sin iniciar sesión da 401; pedirlo con la sesión de un usuario normal (no
admin) da 403.

## Qué es REST

REST es un **estilo** para diseñar APIs, no una tecnología ni una librería.
La idea central: cada dirección representa un **recurso** (una cosa: un
producto, un usuario), y se opera sobre ese recurso usando los métodos HTTP
que ya existen, en vez de inventar una dirección distinta por cada acción.

```
GET    /productos       listar
GET    /productos/3     ver uno
POST   /productos       crear
PUT    /productos/3     modificar
DELETE /productos/3     borrar
```

Esas cinco operaciones sobre un mismo recurso se llaman **CRUD**
(Create, Read, Update, Delete), y son tan comunes que Laravel les da nombres
estándar a los métodos del controlador — los mismos que usa este proyecto a
propósito:

| Método del controller | Corresponde a |
|---|---|
| `index()` | listar (`GET /productos`) |
| `show()` | ver uno (`GET /productos/3`) |
| `store()` | crear (`POST /productos`) |
| `update()` | modificar (`PUT /productos/3`) |
| `destroy()` | borrar (`DELETE /productos/3`) |

### ¿Y las acciones que no son CRUD?

No todo en un sistema es "guardar un dato". `POST /productos/3/vender` no
encaja en el CRUD (vender no es "crear", es una operación con reglas propias:
descuenta stock, calcula un total). La convención en esos casos es agregar un
verbo a la dirección, siempre después del recurso al que pertenece:
`/productos/{id}/vender`, `/pedidos/{id}/cancelar`, etc.

## Cómo entra un pedido a esta API

```
1. El cliente manda:  GET /productos/3
                            │
2. index.php lee $_SERVER['REQUEST_METHOD'] (GET) y la URL (/productos/3)
                            │
3. routes.php ya armó la tabla de direcciones conocidas
                            │
4. Router busca cuál coincide y ejecuta el middleware de la ruta
                            │
5. El controller llama al validator, al DTO y al service que correspondan
                            │
6. El controller responde con Response::success(...) → código 200 + JSON
```

Ver [index.php](../nivel-2/index.php) y
[core/Router.php](../nivel-2/core/Router.php) para el código real de cada paso.

## Resumen para no errarle

- La dirección nombra **qué** cosa; el método dice **qué hacer** con ella.
- `GET` nunca debería cambiar datos (ni crear, ni borrar, ni modificar).
- 401 = no sé quién sos. 403 = sé quién sos, pero no podés.
- Si una acción no es un CRUD típico, se agrega como verbo después del
  recurso: `/recurso/{id}/accion`.
