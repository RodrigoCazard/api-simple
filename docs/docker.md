# Docker: levantar la API sin instalar PHP ni MySQL

Docker permite ejecutar la aplicación dentro de entornos aislados llamados
**containers**. La computadora del estudiante solamente necesita Docker
Desktop; PHP, Apache, Composer y MySQL viven dentro de esos containers.

## Las palabras principales

| Palabra | Qué significa en este proyecto |
|---|---|
| Imagen | Plantilla que contiene PHP, Apache, extensiones y código |
| Container | Ejecución concreta de una imagen |
| Dockerfile | Receta utilizada para construir las imágenes de las APIs |
| Compose | Archivo que levanta y conecta varios servicios juntos |
| Volumen | Lugar persistente donde MySQL conserva los datos |
| Red | Comunicación privada entre los containers |
| Puerto | Entrada que permite acceder desde la computadora |

Una imagen se parece a la plantilla de una máquina. Un container es una
instancia encendida de esa plantilla. Si se crean dos containers a partir de la
misma imagen, los dos comienzan con las mismas herramientas, pero se ejecutan de
manera independiente.

## Arquitectura del entorno

```text
Navegador / REST Client
       │
       ├── localhost:8001 ──→ container nivel-1 ──┐
       │                                           │
       ├── localhost:8002 ──→ container nivel-2 ──┤
       │                                           │
       └── localhost:8003 ──→ container nivel-3 ──┤
                                                   │
                                      red privada de Docker
                                                   │
                                                   ▼
                                         container database
                                                   │
                                                   ▼
                                            volumen MySQL
```

Los containers PHP no usan `localhost` para conectarse a MySQL. Dentro de la
red de Docker, `localhost` significaría "este mismo container". Compose le da a
la base el nombre `database`, por eso las APIs reciben `DB_HOST=database`.

## Qué hace cada archivo

### `compose.yaml`

Describe los cuatro servicios:

- `database`: ejecuta MySQL 8.4.
- `nivel-1`: ejecuta la primera API con Apache.
- `nivel-2`: ejecuta la segunda API con Apache.
- `nivel-3`: ejecuta Laravel con Apache y sirve únicamente su carpeta `public/`.

También declara los puertos, variables, volumen y el orden de inicio. Las APIs
esperan a que el control de salud de MySQL indique que la base está lista.

### `docker/php-apache/Dockerfile`

Es la receta compartida por las dos APIs. Parte de una imagen oficial de PHP con
Apache y después:

1. Instala `unzip`, que Composer necesita para extraer paquetes.
2. Instala la extensión `pdo_mysql`.
3. Activa `mod_rewrite` para que funcionen las rutas.
4. Copia Composer desde su imagen oficial.
5. Copia el nivel correspondiente.
6. Ejecuta `composer install` usando `composer.lock`.

El argumento `APP_DIR` permite reutilizar la receta. Para una imagen vale
`nivel-1` y para la otra vale `nivel-2`.

### `docker/laravel/Dockerfile`

Es la receta específica de Nivel 3. Laravel debe exponerse desde `public/`, no
desde la raíz del proyecto, porque allí viven `.env`, configuración y código
que nunca deben quedar disponibles por HTTP. Al iniciar aplica migraciones y,
fuera de producción, carga los seeders educativos.

### `.dockerignore`

Evita copiar al interior de las imágenes archivos innecesarios o sensibles,
como `.git`, `.env`, `vendor`, documentos y el SQL de instalación.

La carpeta `vendor` se reconstruye dentro de cada imagen. Esto garantiza que
las dependencias sean compatibles con Linux aunque el proyecto se haya clonado
en Windows.

### `.env.docker.example`

Es una plantilla sin secretos reales. Se copia como `.env`, se genera una
`SECRET_KEY`, una `LARAVEL_APP_KEY` independiente, y Docker Compose usa sus
valores. El `.env` está ignorado por Git.

También contiene `APP_ENV=development`. Compose se la entrega a las tres APIs:
en desarrollo la raíz muestra la ayuda educativa; con `APP_ENV=production`
devuelve únicamente un mensaje de estado. Si la variable falta, se usa
`production` como opción segura.

`FRONTEND_ORIGIN` indica qué frontend puede enviar cookies a los niveles 2 y
3. Nivel 3 usa la sesión de Sanctum y protección CSRF. En localhost,
`LARAVEL_SESSION_SECURE=false` permite probar mediante HTTP; en producción debe
ser `true` y el sitio debe usar HTTPS.

## Primera ejecución

Desde la raíz del repositorio:

```powershell
Copy-Item .env.docker.example .env
$jwtBytes = [byte[]]::new(32)
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($jwtBytes)
[BitConverter]::ToString($jwtBytes).Replace('-', '').ToLower()
$laravelBytes = [byte[]]::new(32)
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($laravelBytes)
'base64:' + [Convert]::ToBase64String($laravelBytes)
```

Copiá la primera clave después de `SECRET_KEY=` y el resultado `base64:...` de
Artisan después de `LARAVEL_APP_KEY=`. Luego ejecutá:

```powershell
docker compose up --build
```

`--build` pide reconstruir las imágenes cuando cambió el código o el
Dockerfile. Si las imágenes ya están actualizadas, Docker reutiliza las capas
anteriores y termina más rápido.

## Qué ocurre al iniciar

1. Docker descarga las imágenes base que todavía no existen localmente.
2. Construye las imágenes de nivel 1, nivel 2 y nivel 3.
3. Composer instala las dependencias de JWT y Laravel dentro de sus imágenes.
4. Crea una red privada para los cuatro servicios.
5. Crea el volumen `mysql_data`.
6. MySQL ejecuta `nivel-2/database.sql` y carga los datos iniciales.
7. Cuando MySQL está saludable, arrancan las tres APIs.
8. Nivel 3 aplica sus migraciones y carga seeders solo fuera de producción.

El SQL de inicialización se ejecuta solamente cuando el volumen está vacío. Al
detener y volver a levantar los containers, los usuarios y productos siguen
guardados.

## Comandos útiles

Levantar y ver los logs en la misma terminal:

```powershell
docker compose up --build
```

Levantar en segundo plano:

```powershell
docker compose up --build -d
```

Levantar solamente nivel 2 y su base de datos:

```powershell
docker compose up --build nivel-2
```

Ver el estado:

```powershell
docker compose ps
```

Ver logs de todos los servicios:

```powershell
docker compose logs -f
```

Ver solamente los logs de nivel 2:

```powershell
docker compose logs -f nivel-2
```

Detener y eliminar los containers y la red:

```powershell
docker compose down
```

Los datos de MySQL permanecen porque están en un volumen.

## Reiniciar completamente la base

```powershell
docker compose down -v
docker compose up --build
```

La opción `-v` también elimina el volumen. Eso borra definitivamente los datos
guardados en el MySQL de Docker y hace que `database.sql` vuelva a ejecutarse.
No afecta un MySQL instalado directamente en la computadora.

## Puertos

Los valores predeterminados son:

| Servicio | Puerto de la computadora | Puerto interno |
|---|---:|---:|
| Nivel 1 | 8001 | 80 |
| Nivel 2 | 8002 | 80 |
| Nivel 3 | 8003 | 80 |
| MySQL | 3307 | 3306 |

La parte izquierda es el puerto visible en la computadora. La derecha es el
puerto donde escucha el programa dentro del container.

Si un puerto está ocupado, se puede cambiar en el `.env` raíz:

```env
NIVEL_1_PORT=8011
NIVEL_2_PORT=8012
NIVEL_3_PORT=8013
MYSQL_PORT=3317
```

## Problemas frecuentes

### Docker no puede conectarse al daemon

Docker Desktop está cerrado o todavía está iniciando. Abrilo, esperá a que
indique que el motor está listo y repetí el comando.

### Falta `SECRET_KEY`

No se creó el `.env` raíz o la variable quedó vacía. Copiá
`.env.docker.example`, generá una clave y completala.

### Falta `LARAVEL_APP_KEY`

Nivel 3 necesita su propia clave de cifrado. Generá 32 bytes aleatorios con el
comando PowerShell explicado en la primera ejecución y copiá el resultado
`base64:...` en `LARAVEL_APP_KEY` dentro del `.env` raíz.

### Cambié `database.sql`, pero la base sigue igual

El volumen ya estaba inicializado. Si no necesitás conservar sus datos,
recrealo con `docker compose down -v`.

### Cambié PHP, pero el container conserva el código anterior

Las APIs están copiadas dentro de las imágenes. Reconstruí con:

```powershell
docker compose up --build
```

### Un puerto ya está ocupado

Cambiá `NIVEL_1_PORT`, `NIVEL_2_PORT`, `NIVEL_3_PORT` o `MYSQL_PORT` en el
`.env` raíz.

## Resumen para no errarle

- `Dockerfile` construye una imagen.
- Compose conecta y levanta varios containers.
- `database` es el hostname interno de MySQL.
- Los puertos 8001, 8002 y 8003 permiten entrar desde la computadora.
- El volumen conserva los datos aunque los containers se eliminen.
- `.env` contiene la configuración local y no se sube al repositorio.
- `docker compose down -v` borra los datos del MySQL de Docker.
