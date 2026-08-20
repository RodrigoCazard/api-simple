# Cómo usar la IA para aprender a programar (y cómo no)

Este proyecto se armó ayudado por IA, así que conviene ser honestos sobre
esto: es una herramienta muy buena, y también muy fácil de usar mal. La
diferencia entre las dos cosas no es "usarla o no usarla" — es **cómo**.

## La prueba que importa: ¿podés explicarlo sin la IA?

Si la IA te arregló un error o te escribió una función y no podés explicar,
cerrando la ventana del chat, **qué hace cada línea y por qué**, no aprendiste
nada: solo copiaste. La próxima vez que aparezca un error parecido vas a
estar en el mismo lugar que hoy.

Antes de dar por terminado un ejercicio con ayuda de IA, preguntate:

- ¿Puedo explicar esto en el pizarrón sin mirar la pantalla?
- ¿Sé decir **por qué** se hizo así y no de otra forma?
- Si mañana aparece un error parecido en otro archivo, ¿lo reconozco solo?

Si la respuesta a alguna es "no", el paso siguiente no es pedirle otra cosa a
la IA: es volver atrás y entender lo que ya te dio.

## Cómo preguntar bien

Una mala pregunta da una mala respuesta, aunque la IA sea buena. Comparación:

| Mal | Bien |
|---|---|
| "no me anda, arreglalo" | "esperaba que `/productos/3` devuelva el producto 3, pero me da 404. Mandé un GET con Postman. Acá está el `Router.php` y el `routes.php`" |
| "hacé un CRUD de categorías" | "quiero agregar `Category` copiando la misma estructura que `Product` (model, repository, service, controller). ¿Qué archivos tengo que tocar y en qué orden?" |
| "por qué esto está mal" (pegando 200 líneas) | "en este método `sell()`, ¿por qué se valida la cantidad DESPUÉS de buscar el producto y no antes?" |

La regla general: **cuanto más específico el contexto y más concreta la
pregunta, más útil (y más corta) la respuesta.** Si tenés que pegar el
proyecto entero para que se entienda tu pregunta, probablemente la pregunta
todavía es demasiado grande — conviene partirla.

## No aceptar todo como verdad

La IA se equivoca, y se equivoca con la misma seguridad con la que acierta —
no hay ninguna señal en el tono de la respuesta que te avise "che, esto no
estoy seguro". Por eso:

- **Corré el código antes de asumir que funciona.** Una explicación que
  suena razonable no reemplaza probarlo.
- **Si algo te suena raro, decilo.** "¿Estás seguro de que `==` compara igual
  que `===` acá?" es una pregunta mejor que asumir que la IA ya lo pensó.
- **Pedí la fuente cuando importa.** Sobre todo en seguridad (¿por qué esta
  librería y no escribirlo a mano? ver [seguridad-sqli-xss.md](seguridad-sqli-xss.md)
  y el comentario de [Token.php](../nivel-2/core/Token.php)) conviene poder
  contrastar con la documentación oficial, no solo con lo que dijo el chat.
- **Desconfiá más cuando la respuesta te conviene.** Si le preguntás "¿está
  bien esto que hice?" es fácil que la respuesta suene a que sí. Preguntar
  "¿qué le encontrás mal a esto?" suele sacar más jugo.

## Si algo no se sabe: volver a preguntar, no inventar

Ni la IA ni ustedes tienen que saber todo de una. Si una respuesta usa una
palabra que no conocen (¿qué es "idempotente"? ¿qué es un "wrapper"?), la
salida correcta es **volver a preguntar ahí mismo** ("explicámelo con un
ejemplo", "no entendí esa palabra") antes de seguir. Seguir de largo sin
entender un paso arma una torre sobre una base que no existe: el problema
aparece después, más difícil de encontrar.

Y cuando la duda es sobre algo importante o que da vueltas (contraseñas,
seguridad, "¿esto es una buena práctica de verdad?"), vale la pena
**contrastar con otra fuente**: la documentación oficial de PHP
(<https://www.php.net/manual/es/>), la de la librería que estén usando (por
ejemplo, [firebase/php-jwt en GitHub](https://github.com/firebase/php-jwt)),
o preguntarle al profesor. Ninguna fuente sola —tampoco la IA— es infalible;
cruzar dos es lo que da confianza real.

## Lo que la IA no puede hacer por ustedes

- **No puede rendir el examen por ustedes.** Puede ayudar a preparar, pero el
  entendimiento tiene que quedar en la cabeza de cada uno.
- **No reemplaza correr y probar el código.** "Le pregunté a la IA y me dijo
  que andaba" no es lo mismo que "lo corrí y anduvo".
- **No sabe el contexto de la clase.** No sabe qué explicó el profesor la
  semana pasada, ni qué forma de resolverlo se espera en este curso puntual.
  Ante la duda, la palabra del profesor pesa más que la de la IA.

## Resumen para no errarle

- Si no podés explicarlo sin la IA delante, todavía no lo sabés.
- Preguntas específicas con contexto real dan mejores respuestas que
  preguntas grandes y vagas.
- Corré el código; no confíes en que "suena bien".
- Si algo no se entiende, volvé a preguntar ahí mismo — no sigan de largo
  con una base que no entendieron.
- Para temas importantes (seguridad, buenas prácticas), contrasten con la
  documentación oficial o con el profesor, no se queden con una sola fuente.
