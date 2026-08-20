# Git y el `.gitignore`

## Qué es Git, en una frase

Git guarda el historial de cambios de un proyecto: quién cambió qué línea,
cuándo, y por qué. Es lo que te permite volver atrás si algo se rompe, y lo
que te permite compartir el proyecto con otros sin mandar la carpeta entera
por WhatsApp cada vez.

Este apunte no enseña a usar Git (eso se ve aparte); explica una sola cosa
puntual: **por qué no todo lo que hay en la carpeta del proyecto se sube al
repositorio.**

## No todo archivo merece estar en git

Git está pensado para guardar **código fuente**: lo que escribieron ustedes.
Hay dos tipos de archivos que NO deberían subirse nunca:

1. **Secretos** — el `.env`, con la clave del JWT y los datos de conexión a
   MySQL (usuario, contraseña). Si se sube, quedan visibles en el historial
   para siempre, incluso si después los borran (`git log` los sigue
   mostrando).
2. **Archivos regenerables** — la carpeta `vendor/`, que Composer reconstruye
   solo con `composer install`. Subirla infla el repositorio con código de
   otra gente que ya está descrito en `composer.json`.

Los datos en sí (los productos, los usuarios) ya no viven en una carpeta
del proyecto: están en la base MySQL, que tampoco es cosa de Git — se crea
una sola vez importando [database.sql](../nivel-2/database.sql), y de ahí
en más cada instalación tiene la suya.

## El `.gitignore`

Es un archivo de texto en la raíz del proyecto que le dice a Git "estas
rutas, ni las mires". El de este proyecto:

```
.env
vendor/
```

Cada línea es un patrón. `.env` ignora ese archivo (en cualquier carpeta,
por eso no lleva `/` adelante: así ignora tanto `nivel-1/.env` como
`nivel-2/.env`); `vendor/` ignora esa carpeta entera, dondequiera que
aparezca.

**Importante:** el `.gitignore` solo funciona para archivos que Git
**todavía no conoce**. Si un archivo ya fue subido alguna vez, agregarlo acá
no lo borra del historial — hay que sacarlo aparte (`git rm --cached`). Por
eso conviene tener el `.gitignore` listo **antes** del primer `git add`.

## Qué SÍ se sube

La contracara de lo de arriba: si `vendor/` no se sube, ¿cómo sabe otra
persona qué librerías necesita el proyecto? Por eso **sí** se suben:

- `composer.json` y `composer.lock` — la lista de librerías y sus versiones
  exactas. Con eso, cualquiera reconstruye `vendor/` corriendo
  `composer install`.
- `.env.example` — la plantilla de variables de entorno, sin los valores
  reales (ver [variables-de-entorno.md](variables-de-entorno.md)).

## Cómo arrancar el repositorio de este proyecto

Si todavía no es un repositorio git (`git status` da error), se inicializa
una sola vez:

```bash
git init
git add .
git commit -m "Primer commit"
```

Con el `.gitignore` ya puesto ANTES de ese `git add .`, `.env` y `vendor/`
quedan afuera automáticamente — no hace falta acordarse de excluirlos a
mano cada vez.

## Resumen para no errarle

- `.gitignore` no borra archivos del disco: solo le dice a Git que los
  ignore al hacer `add`/`commit`.
- Si algo es secreto (contraseñas, claves) o se regenera solo (`vendor/`),
  no va a git.
- Si algo describe **cómo reconstruir** lo que no se sube (`composer.json`,
  `.env.example`, `database.sql`), sí va a git.
