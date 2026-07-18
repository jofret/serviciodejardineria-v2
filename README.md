# 🌿 Servicio de Jardinería (v2)

Base de la migración de [serviciodejardineria.com.ar](https://serviciodejardineria.com.ar) al mismo stack usado por `limpieza-terrenos-fresh`: Laravel 12 + Filament + CRM (Customer/Property/ServiceOrder). Clonado desde `limpieza-terrenos-fresh` el 2026-07-16; todavía **no tiene** las rutas, modelos ni contenido propios de jardinería adaptados — eso corresponde a las fases siguientes del plan de migración.

## 🚀 Características (heredadas de la base)

- ✅ Posts con categorías y tags
- ✅ Panel de administración con Filament
- ✅ Subida de imágenes con Spatie Media Library
- ✅ Formulario de contacto con base de datos
- ✅ Encuestas/testimonios por WhatsApp
- ✅ Docker para desarrollo

## 📋 Requisitos

- Docker Desktop

PHP y Composer corren dentro de los contenedores — no hace falta instalarlos en el host. Node **sí** hace falta en el host (ver la sección "CSS de Tailwind" más abajo): el contenedor `php` no lo incluye.

## 🛠️ Instalación

```bash
# Levantar contenedores
docker-compose up -d

# Instalar dependencias
docker-compose exec php composer install

# Generar clave de la app (el .env ya está armado con los puertos de este stack)
docker-compose exec php php artisan key:generate

# Migrar base de datos
docker-compose exec php php artisan migrate
```

## 🎨 CSS de Tailwind: hay que reconstruirlo a mano

`public/css/tailwind-generated.css` es un archivo generado y **commiteado al repo** (a propósito, para que el sitio funcione out-of-the-box sin depender de Node en runtime) — pero eso significa que **no se regenera solo**. Si agregás o cambiás clases de Tailwind en cualquier `.blade.php` y no lo reconstruís, vas a ver el HTML correcto pero sin estilos nuevos (clases "fantasma": existen en el HTML pero no tienen ninguna regla CSS que las respalde). Así se manifestó un bug real: una miniatura de foto se veía gigante en vez de recortada en cuadradito, porque `aspect-square`/`grid-cols-*` no estaban compiladas — ni `view:clear` ni reiniciar OPcache lo arreglan, porque el problema nunca fue de caché de Laravel/PHP.

Node no está instalado en el contenedor `php` (su Dockerfile es PHP puro), así que este paso se corre **desde el host**, no con `docker compose exec`:

```bash
cd src
npm install    # una sola vez
npm run css    # reconstruye el CSS — correlo después de cualquier cambio de clases en un .blade.php
```

`npm run build` (el build "oficial" del proyecto, el que corre `composer run setup`) también reconstruye este CSS como parte del proceso normal — corre `vite build` y al final `npm run css`. Pero como el día a día en este proyecto es Docker + edición directa de Blade sin pasar por Vite, en la práctica **`npm run css` es el comando que hay que acordarse de correr** después de tocar clases de Tailwind.

Puertos de este stack (elegidos para no chocar con los stacks que ya corren en esta máquina: `limpieza_*` en 8080/8081/3307, `jardineria_*` del sitio actual en 8082/8083/3308, `isolu_*` en 8085/8086/3310):

| Servicio   | Puerto host |
|------------|-------------|
| nginx      | 8094        |
| mysql      | 3311        |
| phpmyadmin | 8095        |

## Estado de la migración

Ver el documento de auditoría y plan de migración para el detalle completo de fases.

- [x] **Fase 1** — base del código nuevo (clonado de `limpieza-terrenos-fresh`, Docker propio).
- [x] **Fase 2** — rutas públicas adaptadas a los paths actuales (`/publicaciones/{slug}`, `/categoria/{slug}`, `/tag/{slug}`), sin segmento de categoría en el post.
- [x] **Fase 3** — datos migrados desde la base Laravel 8 actual preservando slugs exactos: 6 categorías, 23 tags, 41 posts (234 imágenes vía Spatie Media Library) y 31 testimonios (→ `Customer`+`Survey`). Comando: `php artisan jardineria:import-legacy` (`--fresh` para reimportar desde cero en dev; ver opciones con `--help`).
- [x] **Fase 4** — admin Filament revisado para jardinería: mensajes de WhatsApp, color del panel, y dos bugs de slug corregidos (se regeneraba solo al editar un post/categoría/tag existente) más el form de imágenes de posts reconectado a Spatie Media Library.
- [x] **Fase 5** — formulario de contacto y flujo de encuestas por WhatsApp verificados de punta a punta (disparo desde el admin → respuesta pública → moderación → aparece en la home). Se corrigió un bug real: `ContactController` rompía con `TypeError` en el segundo contacto de un mismo cliente (doble manejo de JSON sobre un campo que el modelo ya castea como array). `/clientesformulario` (el form viejo de testimonios) queda sin ruta — cae en el 404 normal, consistente con sus 0 clics/impresiones reales en Search Console.
- [x] **Fase 6** — SEO/branding: marca "Servicio de Jardinería" en título/meta/JSON-LD/footer en todo el sitio (home, posts, categorías, tags, 404), datos de contacto reales (teléfono, email, Facebook — confirmados, no inventados), `robots.txt` apuntando al dominio correcto (`limpieza-y-desmalezado-de-terrenos.com.ar` → `serviciodejardineria.com.ar`) y bloqueando `/encuesta` además de `/admin`. Las estadísticas del hero ("+15 años", "500+ terrenos") se reemplazaron por números reales calculados de la propia base (trabajos, testimonios, servicios) en vez de inventar cifras. El banner del hero, `og-default.jpg` y los tres fallbacks de imagen que no existían como archivo (`default-og.jpg`, `default-post.jpg`, `default-thumb.jpg` — roto para cualquier post futuro sin imagen destacada) se reemplazaron por una foto real del sitio actual (`layout/imgs/corte_de_pasto.jpg`).
- [x] **Fase 7** — validación pre-corte: se tomó el export real de Search Console (últimos 6 meses) y se probó cada una de las 46 URLs únicas contra el sitio nuevo. **41/41 páginas de contenido responden 200 sin ningún redirect** (home, listado, paginación, los 36 posts con tráfico real). Los únicos 5 fallos son URLs de imágenes estáticas legacy (`/image/*.jpg`, `/layout/imgs/*.jpg`, 1-3 impresiones cada una) que no aplican al nuevo sistema de imágenes (Spatie Media Library) — esperable, no estaba en el alcance de "no romper" (eso era para páginas de contenido, no assets sueltos). Además se probaron las 6 categorías y los 23 tags reales (no cubiertos por Search Console): 29/29 responden 200.
- [ ] **Fase 8** — corte de DNS/dominio.

### Nota sobre los testimonios migrados

El formulario de testimonios viejo no pedía teléfono, y `Customer.phone` es único y es la clave del flujo de WhatsApp nuevo. Los 31 testimonios migrados generaron un `Customer` con `phone` placeholder (`legacy-cliente-{id}`) y `fuente=migracion_legacy` — **no son teléfonos reales**, no van a poder recibir el flujo de "Encuesta WhatsApp" hasta que se les cargue un teléfono real a mano si hace falta contactarlos de nuevo.

### Quirk de Docker Desktop (Windows) a tener en cuenta

Si corrés `docker compose exec php php artisan ...` (o `tinker`), el proceso corre como `root` dentro del contenedor. Si eso llega a crear/tocar `storage/logs/laravel.log` (por ejemplo, un comando que loguee algo), el archivo puede quedar con permisos que **php-fpm** (que sirve el tráfico HTTP real, como `www-data`) no puede escribir, y cualquier request que intente loguear algo revienta con `Permission denied`. Si aparece ese error: `docker compose exec php rm -f storage/logs/laravel.log && docker compose exec php chmod -R 777 storage bootstrap/cache`.
