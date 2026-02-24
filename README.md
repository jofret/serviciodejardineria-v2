# 🌿 Limpieza y Desmalezado de Terrenos

Web corporativa para empresa de limpieza de terrenos, desarrollada con Laravel 12.

## 🚀 Características

- ✅ Posts con categorías y tags
- ✅ Panel de administración con Filament
- ✅ Subida de imágenes con Spatie Media Library
- ✅ Formulario de contacto con base de datos
- ✅ URLs semánticas para SEO (/categoria/titulo)
- ✅ Docker para desarrollo

## 📋 Requisitos

- Docker Desktop
- PHP 8.3+
- Composer

## 🛠️ Instalación

```bash
# Clonar repositorio
git clone https://github.com/TU-USUARIO/limpieza-terrenos.git
cd limpieza-terrenos

# Levantar contenedores
docker-compose up -d

# Instalar dependencias
docker-compose exec php composer install

# Configurar entorno
cp .env.example .env
docker-compose exec php php artisan key:generate

# Migrar base de datos
docker-compose exec php php artisan migrate --seed