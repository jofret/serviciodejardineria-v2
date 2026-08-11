# Infraestructura

## Acceso al repo (GitHub)

El remoto usa SSH con una deploy key propia de esta máquina (no una cuenta de usuario):

```
origin  git@github.com-serviciodejardineria:jofret/serviciodejardineria-v2.git
```

- Clave privada: `~/.ssh/id_ed25519_serviciodejardineria`
- Alias en `~/.ssh/config`:

  ```
  Host github.com-serviciodejardineria
      HostName github.com
      User git
      IdentityFile ~/.ssh/id_ed25519_serviciodejardineria
      IdentitiesOnly yes
  ```

- La clave pública está cargada como **Deploy Key** (con "Allow write access") en:
  `github.com/jofret/serviciodejardineria-v2/settings/keys`

Para replicar el acceso en otra máquina: generar un par de claves nuevo (no reutilizar
el de esta máquina), agregar la pública como deploy key del repo, y crear el alias de
host correspondiente en `~/.ssh/config` de esa máquina. Las claves privadas nunca se
copian entre máquinas.

## Entorno de desarrollo

Todo corre con Docker Compose (`docker-compose.yml` en la raíz). PHP y Composer corren
dentro de los contenedores; Node hace falta en el host solo para reconstruir el CSS de
Tailwind (ver README.md).

Puertos de este stack en el host:

| Servicio   | Puerto host |
|------------|-------------|
| nginx      | 8094        |
| mysql      | 3311        |
| phpmyadmin | 8095        |

Elegidos para no chocar con otros stacks que corren en la misma máquina
(`limpieza_*` en 8080/8081/3307, `jardineria_*` del sitio actual en 8082/8083/3308,
`isolu_*` en 8085/8086/3310).

## Producción

**Todavía no hay servidor de producción configurado para este proyecto.** El dominio
final es `serviciodejardineria.com.ar` (ver `src/.env.production.example`), pero el
corte de DNS es la Fase 8 del plan de migración, pendiente (ver README.md, sección
"Estado de la migración"). Cuando se defina el hosting, documentar acá:

- Host/puerto/usuario SSH y alias en `~/.ssh/config`.
- Ruta de la app en el servidor.
- Comandos de deploy (`git pull`, `artisan migrate --force`, `artisan optimize:clear`, etc.).
- Variables de entorno reales completadas a partir de `src/.env.production.example`
  (nunca commitear el `.env` real).
