# Variables de entorno (`.env`)

## El problema que resuelve

Un programa necesita datos que **no son parte del código**: una clave secreta,
la contraseña de una base de datos, la dirección de un servidor. Esos datos
tienen dos problemas si los escribís directo en el código:

1. **Cambian según dónde corra el programa.** En tu computadora la base se
   llama de una forma, en la del profesor de otra, y en un servidor real de
   otra distinta. Si el dato está *adentro* del código, hay que editar el
   código cada vez que cambia de máquina.
2. **Algunos son secretos.** Si subís el código a GitHub (o a cualquier lado)
   y la clave está escrita ahí adentro, ya no es secreta: la puede leer
   cualquiera que vea el repositorio.

La solución: esos datos no van en el código, van en un archivo aparte que
**cada máquina tiene el suyo** y que **nunca se sube** al control de
versiones. Ese archivo es el `.env`.

## Cómo se usa en este proyecto

Hay dos archivos parecidos, y la diferencia entre ellos es la que importa:

| Archivo | ¿Qué tiene? | ¿Se sube a git? |
|---|---|---|
| `.env.example` | los NOMBRES de las variables, con valores de ejemplo | **sí** |
| `.env` | los valores REALES de esta máquina | **no** (está en `.gitignore`) |

Cuando alguien baja el proyecto por primera vez, hace:

```bash
cp .env.example .env        # Linux/Mac
copy .env.example .env      # Windows
```

Y después edita su `.env` con sus propios valores (por ejemplo, genera su
propia `SECRET_KEY`). El `.env.example` le sirvió de "receta": le dijo
exactamente qué variables tenía que definir, sin revelarle ningún secreto de
otra persona.

## El formato

Un `.env` es texto plano, una variable por línea:

```
CLAVE=valor
OTRA_CLAVE=otro valor
# esto es un comentario, se ignora
```

Sin comillas, sin espacios alrededor del `=` (aunque si los hay, este
proyecto los saca solo). Nada más.

## Cómo llegan esos valores al código

PHP no lee `.env` solo — hay que decirle cómo. En
[config.php](../nivel-2/config.php)
hay una función `loadEnv()` que lo hace:

```php
loadEnv(__DIR__ . '/.env');
```

Ese archivo lee cada línea del `.env`, la separa en clave y valor, y los dos
los guarda con `putenv()`. A partir de ahí, **cualquier parte del proyecto**
puede leer esos valores con `getenv('SECRET_KEY')`, o con el ayudante que
también está en `config.php`:

```php
env('SECRET_KEY')                 // el valor, o null si no está
env('TOKEN_LIFETIME', 3600)       // el valor, o 3600 si no está
env('FRONTEND_ORIGIN', 'http://localhost:5173')
env('APP_ENV', 'production')      // entorno; si falta, usa el más seguro
```

Y `config.php` los convierte en las constantes que usa el resto del proyecto:

```php
define('SECRET_KEY', env('SECRET_KEY'));
```

## Desarrollo y producción con `APP_ENV`

La misma aplicación puede necesitar un comportamiento distinto según dónde
esté ejecutándose. Para eso se usa:

```env
APP_ENV=development
```

- `development` habilita información útil para aprender: `GET /` enumera los
  endpoints y las cuentas locales de prueba.
- `production` entrega una respuesta mínima en `GET /` y no publica esos
  datos.

`config.php` acepta únicamente esos dos valores. Si `APP_ENV` no está definida,
elige `production`: ante una configuración incompleta es preferible revelar
menos información.

Esta separación no vuelve segura una ruta por ocultarla. Todas las rutas deben
seguir validando autenticación, roles y datos de entrada. Además, las cuentas
de demostración deben eliminarse o reemplazarse antes de publicar la API.

`loadEnv()` no tiene nada específico de esta API — es una función genérica de
20 líneas. **La pueden copiar tal cual al principio de cualquier otro
proyecto PHP** para tener soporte de `.env` sin instalar ninguna librería.
(Proyectos grandes en general usan una ya hecha, `vlucas/phpdotenv`, que hace
lo mismo pero con más casos cubiertos.)

## "Fallar rápido"

Fijate que `config.php` no se queda callado si falta la clave:

```php
if (!SECRET_KEY || SECRET_KEY === 'cambiame-por-una-clave-generada-al-azar') {
    die('Falta configurar SECRET_KEY en el archivo .env (mirá .env.example).');
}
```

Es mejor que la API **no arranque** a que arranque funcionando pero insegura
sin que nadie se entere. A esta idea se le llama *fail fast* (fallar rápido):
si algo importante falta, avisar de inmediato y fuerte, no dejar que el
problema aparezca después, escondido, en producción.

## Resumen para no errarle

- `.env` → secretos y config de esta máquina → **nunca se sube a git**.
- `.env.example` → la plantilla, sin secretos → **sí se sube**.
- En esta API tradicional, `index.php` se ejecuta y lee `.env` en cada
  petición. Normalmente no hace falta reiniciar `php -S` después de cambiarlo.
- Si borrás el `.env` por accidente, no perdiste nada importante del código:
  lo volvés a crear copiando `.env.example`.
