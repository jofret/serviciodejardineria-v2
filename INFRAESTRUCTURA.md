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

El hosting es el mismo servidor compartido (Hostinger) que ya se usa para
`poda-de-altura-v2` y otros dominios — no es un servidor nuevo:

- Host: `185.245.180.173`, puerto `65002`, usuario `u761161547`.
- Alias en `~/.ssh/config`: `produccion-poda` (clave `~/.ssh/id_ed25519_prod`),
  reutilizado tal cual — no hace falta una clave nueva porque es la misma cuenta.
- Conectar: `ssh produccion-poda`.

### Sitio en vivo (dominio `serviciodejardineria.com.ar`)

**Ojo:** en `~/domains/serviciodejardineria.com.ar/` en el servidor corre hoy el sitio
**viejo** (Laravel 8, estructura con la app en la raíz, no en `src/`), sirviendo
tráfico real. `public_html` es un symlink a `new_release_20260805f/public`. **No es
este repo (`v2`)** — el corte a este repo es la Fase 8 del plan de migración
(README.md, "Estado de la migración"), todavía pendiente. No tocar esa carpeta ni el
symlink hasta que se decida hacer el corte.

### Clon de staging de este repo (`v2`)

Para poder revisar el código de este repo en el servidor sin afectar el sitio en vivo,
se clonó (por HTTPS, el repo es público, no hace falta deploy key en el servidor) en
una ruta separada, fuera de `domains/` — no expuesta públicamente:

```
~/serviciodejardineria-v2-staging
```

Estado actual: solo el código clonado (`git clone`, commit `0f46f64` al crearlo).
Todavía falta, cuando se quiera avanzar ahí: `.env` (a partir de
`src/.env.production.example`, completando los TODO — nunca commitear el real),
levantar los contenedores o instalar dependencias, y decidir cómo se sirve
(¿Docker en el servidor, o Apache/PHP-FPM del hosting apuntando a `src/public`?
falta definir — el hosting de Hostinger normalmente no corre Docker).

### Pendiente para el corte real (Fase 8)

- Definir cómo se sirve `v2` en este hosting compartido (probablemente sin Docker,
  directo con el PHP del hosting — ver cómo está armado `poda-de-altura-v2` como
  referencia).
- Completar `.env` de producción real.
- Mover/renombrar carpetas y repuntar `public_html` (o el symlink que corresponda)
  de la app vieja a la nueva.
- Comandos de deploy esperables una vez armado: `git pull`, `artisan migrate --force`,
  `artisan optimize:clear` (mismo patrón que `poda-de-altura-v2`).
