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

Todo lo demás (PHP, Composer, Node) corre dentro de los contenedores — no hace falta instalarlo en el host.

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
- [ ] **Fase 4** — admin Filament (ya viene de la base, falta revisar que los Resources cubran el caso de jardinería).
- [ ] **Fase 5** — formulario de contacto y activación del flujo de encuestas por WhatsApp.
- [ ] **Fase 6** — SEO/branding (textos, marca, JSON-LD, robots.txt) todavía dicen "Limpieza de Terrenos" en varios lugares (contenido, no rutas).
- [ ] **Fase 7** — validación pre-corte contra el listado real de URLs indexadas.
- [ ] **Fase 8** — corte de DNS/dominio.

### Nota sobre los testimonios migrados

El formulario de testimonios viejo no pedía teléfono, y `Customer.phone` es único y es la clave del flujo de WhatsApp nuevo. Los 31 testimonios migrados generaron un `Customer` con `phone` placeholder (`legacy-cliente-{id}`) y `fuente=migracion_legacy` — **no son teléfonos reales**, no van a poder recibir el flujo de "Encuesta WhatsApp" hasta que se les cargue un teléfono real a mano si hace falta contactarlos de nuevo.
