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

### Sitio en vivo (dominio `serviciodejardineria.com.ar`) — CORREGIDO 2026-08-13

**Este repo (`v2`) ya está en producción real.** El corte (Fase 8) ya se hizo
alrededor del 5-6/8/2026 — lo de abajo estaba desactualizado (decía "Fase 8
pendiente, sigue el sitio viejo") porque nadie volvió a chequear el estado real del
servidor después del corte.

- Carpeta viva: `~/domains/serviciodejardineria.com.ar/new_release_20260805f/`
  (es la app de este repo — Laravel con `vite.config.js`/`tailwind.config.js`,
  no el Laravel 8 viejo que sigue suelto en la raíz del dominio sin usarse).
- `public_html` es un symlink a `new_release_20260805f/public`.
- **No hay `.git` en esa carpeta ni en la raíz del dominio.** El release se armó
  como una copia de archivos (probablemente clonado con la deploy key
  `~/.ssh/github_serviciodejardineria` y despachado sin dejar `.git`, a juzgar por
  los backups de DB `jardineria_db_pre_deploy_20260805b/e/f.sql.gz` — varios
  releases fechados el mismo día, patrón "release folder" tipo Capistrano, no un
  pipeline automático).
- No hay webhook ni GitHub Action disparando deploys, ni `crontab` (no existe el
  comando en este hosting compartido).
- PHP del hosting: `vendor/` requiere PHP >= 8.4.1. El binario correcto es
  `/opt/alt/php84/usr/bin/php` (¡ojo! `/opt/alt/php83/usr/bin/php` y el `php` del
  `$PATH` son 8.3 y fallan con "Composer detected issues in your platform").

### Cómo desplegar un cambio chico hoy (verificado 2026-08-13)

Sin `.git` en el server, el mecanismo real para un fix puntual (mismo patrón usado
el 6/8 para el footer de WhatsApp) es:

1. Backup de los archivos que se van a tocar, a mano, en una carpeta nueva en
   `~`, ej. `~/backup-<descripcion>-<fecha>/` (`cp` de los originales antes de
   pisarlos).
2. `scp` de la versión nueva desde el repo local a la ruta correspondiente dentro
   de `~/domains/serviciodejardineria.com.ar/new_release_20260805f/...`.
3. Limpiar cache de Laravel con el PHP correcto:
   ```
   cd ~/domains/serviciodejardineria.com.ar/new_release_20260805f
   /opt/alt/php84/usr/bin/php artisan view:clear
   /opt/alt/php84/usr/bin/php artisan optimize:clear
   ```
4. Verificar en vivo con `curl` (o el navegador) que el cambio se ve.

No versiona el server — hay que acordarse de que el repo local y el server pueden
divergir si se edita algo directo ahí sin después portarlo al commit.

### Deploys recientes (2026-08-31)

Se aplicó el mecanismo de arriba para 3 cambios chicos en la home y el layout,
todos commiteados en este repo y confirmados en vivo con `curl` después de cada uno:

- Home: nueva sección "Corte de Cercos y Enredaderas" en el bloque de publicaciones
  por servicio (`app/Http/Controllers/HomeController.php`, array
  `$homeServiceBlocks`). Commit `14ccb0e`.
- Home: nueva sección de fumigación después del formulario de contacto
  (`resources/views/home.blade.php`) + imagen `public/images/serviciodefumigacion.webp`
  (no existía en el server, se subió por primera vez con este deploy). Commit `57d441c`.
- Menú y footer: enlace externo "Fumigación" → `https://serviciodefumigacion.com.ar/`,
  ubicado después de "Servicios" en el menú desktop, el menú móvil y el footer
  (`resources/views/layouts/app.blade.php`). Commits `afe41e8` (alta), `a39bd14`
  (reorden en el menú), `b61b6f9` (alta en el footer).

Backup a mano en el server, uno por archivo la primera vez que se tocó en esta
tanda (los deploys siguientes sobre el mismo archivo se verificaron con `diff`
contra el commit previo antes de pisar, sin backup adicional):

- `~/backup-home-cercos-enredaderas-20260831/HomeController.php`
- `~/backup-home-fumigacion-20260831/home.blade.php`
- `~/backup-menu-fumigacion-20260831/app.blade.php`

### Clon de staging — borrado (2026-08-13)

Había un clon separado en `~/serviciodejardineria-v2-staging` (HTTPS, sin deploy
key), pensado para revisar código "antes del corte" — pero el corte ya había
pasado y no se estaba usando para nada (sin `.env`, sin contenedores levantados,
parado en el commit `0f46f64`). Se confirmó `git status` limpio y se borró
(`rm -rf`) para no dejar una copia zombie dando vueltas.

### Pendiente / mejora sugerida

- Adoptar `git` en `new_release_20260805f` (o en una carpeta nueva y repuntar el
  symlink), igual que ya se hizo hoy para `limpieza-terrenos` (backup previo +
  `git init` + remote HTTPS al repo público + dejarlo parado en el commit
  correspondiente) y como ya funciona `poda-de-altura-v2`. Así los próximos
  deploys pueden ser `git pull` en vez de `scp` archivo por archivo.
- Mientras tanto, cualquier fix se despliega a mano como se describe arriba —
  y hay que acordarse de commitear el mismo cambio en este repo para que no
  diverja del server.
