# Paso Seguro — landing de portfolio

Ruta: `/proyecto-paso-seguro/?lang=es` (también `en`).

## Origen

Adaptación del frontal de `jacoto90/avisosalidavehiculo`, commit
`a697c2f214a17c034aae0cdb54a2d736a5bb8d54`:

- `resources/js/pages/Welcome.vue`: orden de las secciones.
- `resources/js/components/landing/Landing*.vue`: menú, composición, tarjetas,
  paleta roja/zinc y comportamiento de la landing.
- `resources/js/i18n/{es,en}.json`: textos de producto.
- `resources/images/simulacion/*.webp`: los seis fotogramas originales,
  sin modificar; intervalo de 2400 ms y transición de 2000 ms.
- `resources/images/stop*.png`: imágenes de producto convertidas a WebP.

El marcado se ha portado a PHP y los estilos/interacciones a CSS y JavaScript
nativos, siguiendo la arquitectura del portfolio. No requiere Node, Laravel,
Inertia ni conexión con el antiguo servidor para servir esta página.

## Adaptación

El menú mantiene las seis secciones. Los accesos de cuenta se sustituyen por
el regreso al portfolio. Contacto lleva al contacto del desarrollador, sin
simular envíos ni disponibilidad del antiguo SaaS. Los detalles de instalación
se despliegan en la propia sección. Los textos comerciales se contextualizan
como proyecto; no se reproducen estadísticas sin fuente ni garantías de servicio.

Incluye ES/EN, modo claro/oscuro, pausa y selección de fotogramas, navegación
móvil y soporte de movimiento reducido. Sin JavaScript, el contenido y las
anclas siguen disponibles, con el primer fotograma como imagen fija.

## Contenido administrado

`data/profile.php` incorpora el proyecto por defecto. Para una base ya sembrada,
el script CLI restaura el registro existente, conserva su ID y orden, guarda
una copia privada y usa las revisiones de Studio:

```text
php tools/restore-paso-seguro.php /ruta/al/portfolio /ruta/privada/paso-seguro-antes.json
```

Es idempotente y no restaura las antiguas tarjetas del hero. Comprobar primero
en una copia local de la base. Publicar los archivos y ejecutar la actualización
de contenido solo al aprobar el despliegue.

Archivos de la landing: `proyecto-paso-seguro/index.php`, `data/paso-seguro.php`,
`assets/css/paso-seguro.css`, `assets/js/paso-seguro.js`,
`assets/images/paso-seguro/`. La tarjeta usa `assets/images/paso-seguro.webp`
y su variante `paso-seguro-640.webp`.
