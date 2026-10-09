# Dominio propio + HTTPS (Let's Encrypt) para el Moodle de Contabo

Dominio comprado el 2026-08-30: **`richiacademy.com`**, en **Porkbun**
(renovación .com ~11 US$/año constante, privacidad WHOIS incluida). El
certificado es **gratis** con Let's Encrypt (certbot) y se renueva solo.
Cierra el punto 3 de [security-checklist.md](security-checklist.md) y
desbloquea T-11 (pagos).

## 1. Dominio y DNS (Porkbun) — HECHO 2026-08-30

Comprado `richiacademy.com` con privacidad WHOIS. En *Domain Management →
DNS* se quitó el URL Forwarding y los ALIAS/CNAME del aparcado, y quedaron
7 registros: **2 A** (raíz y `www` → `169.58.171.171`), 2 MX
(`fwd1/fwd2.porkbun.com`, reenvío de correo de Porkbun) y 3 TXT (SPF de
Porkbun + 2 `_acme-challenge` de su certificado aparcado; inofensivos, el
certbot del servidor usa HTTP-01, no DNS). Propagación verificada con
`dig` local y `@8.8.8.8`; Apache ya contesta por el dominio (303 al wwwroot
viejo, esperado antes del paso 4).

## 2. Apache: reconocer el dominio (en Contabo, como root)

```bash
cp /var/www/html/config.php /root/config.php.bak-$(date +%F)   # respaldo antes de tocar nada

apache2ctl -S            # confirma qué vhost sirve el :80 (esperado: 000-default.conf)
nano /etc/apache2/sites-enabled/000-default.conf
#   Dentro de <VirtualHost *:80> añadir/ajustar:
#     ServerName richiacademy.com
#     ServerAlias www.richiacademy.com
apachectl configtest && systemctl reload apache2
curl -sI http://richiacademy.com/ | head -1     # sigue 303: normal, el wwwroot aún es la IP
```

`http://richiacademy.com` ya debe responder (Moodle redirigirá a la IP mientras
el wwwroot no cambie — normal).

## 3. Certificado Let's Encrypt

Puerto 443 abierto primero: `ufw status` (si está activo, `ufw allow 443/tcp`)
y el firewall del panel web de Contabo si lo hubiera. Luego:

```bash
apt install -y certbot python3-certbot-apache
certbot --apache -d richiacademy.com -d www.richiacademy.com
# correo: el de la academia · aceptar términos · elegir REDIRECT http→https
```

Certbot crea el vhost :443, instala el certificado (90 días) y deja un timer
de renovación automática. Comprobar:

```bash
ss -tlnp | grep ':443'                     # apache escuchando en 443
curl -sI https://richiacademy.com/ | head -1   # responde (302/303 hacia el wwwroot viejo, aún normal)
systemctl list-timers | grep certbot       # timer de renovación
certbot renew --dry-run                    # ensayo de renovación sin tocar nada
```

## 4. Moodle: cambiar el wwwroot (config.php, NO cfg.php)

`wwwroot` vive en `/var/www/html/config.php` (no viaja por git):

```bash
nano /var/www/html/config.php
#   $CFG->wwwroot = 'https://richiacademy.com';
sudo -u www-data php /var/www/html/admin/cli/purge_caches.php
```

Verificar: `curl -sIL https://richiacademy.com/ | head -3` → 200; login en
ventana privada; candado en el navegador. Con wwwroot https, Moodle marca
solo las cookies como seguras — nada más que tocar.

## 5. Efectos colaterales esperados

- **Todos deben volver a iniciar sesión** (la cookie era del host viejo). Una vez.
- **Enlaces de invitación ya compartidos** con `http://169.58.171.171/...`:
  volver a copiarlos desde la lista (se regeneran con el dominio). Los tokens
  siguen siendo válidos.
- El botón **Copiar enlace** ya usa la Clipboard API nativa (contexto seguro).
- Gmail SMTP no cambia. Con dominio propio, más adelante se puede pasar el
  correo a `@richiacademy.com` con SPF/DKIM (menos spam) — proyecto aparte.
- `admin/cli/checks.php` deja de avisar de HTTPS.
- T-11 (pasarela de pago) queda técnicamente desbloqueado.

## 6. Remate del mismo día

- `sudo -u www-data php /var/www/html/admin/cli/checks.php` → el aviso de
  HTTPS desaparece.
- Probar por el dominio: «Olvidé mi contraseña», una invitación completa
  (crear → copiar enlace → ventana privada) y la página de prueba de correo.
- **Volver a copiar** los enlaces de invitación pendientes: los ya
  compartidos con `http://169.58.171.171/...` llevan el host viejo (los
  tokens siguen siendo válidos; el enlace regenerado ya sale con el dominio).
- Avisar a Richi y alumnos: la dirección ahora es
  `https://richiacademy.com` (la IP redirige sola tras el cambio de wwwroot).
- Actualizar MEMORY.md del repo (wwwroot nuevo) al cerrar.

## Marcha atrás

`certbot` no rompe el :80. Si algo falla: restaurar
`$CFG->wwwroot = 'http://169.58.171.171'` en config.php + purge, y el sitio
queda como antes; el certificado puede quedarse instalado sin usarse.
