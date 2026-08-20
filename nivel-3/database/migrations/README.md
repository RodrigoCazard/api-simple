# Migraciones

Las migraciones describen los cambios de estructura de la base de datos. Laravel
las ejecuta con `php artisan migrate` y registra cuáles ya aplicó en la tabla
`migrations`.

El nombre sigue esta convención:

```text
AAAA_MM_DD_HHMMSS_descripcion_de_la_migracion.php
```

El prefijo funciona como marca de tiempo y permite ordenar los archivos antes
de ejecutarlos. Por eso no conviene quitarlo ni cambiar el nombre después de
haber aplicado una migración.

Los archivos que comienzan con `0001_01_01` son migraciones base. No indican la
fecha real de creación: Laravel utiliza una fecha deliberadamente antigua para
asegurar que se ejecuten primero. Las migraciones creadas con Artisan suelen
usar la fecha y hora reales, como `2026_08_20_181518`.
