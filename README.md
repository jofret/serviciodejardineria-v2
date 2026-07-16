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

Ver el documento de auditoría y plan de migración para el detalle completo de fases. Este repo corresponde a la **Fase 1** (base del código nuevo). Pendiente: adaptar `routes/web.php` a los paths actuales de jardinería (`/publicaciones/{slug}`, `/categoria/{slug}`), migrar datos preservando slugs, y portar SEO/branding.
