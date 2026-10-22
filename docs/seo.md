# SEO técnico · Auditoría del 22 de octubre de 2026

## Objetivo y alcance

Orientar la presentación en buscadores a desarrollo web en Girona, páginas
web a medida, tiendas online y software de negocio. La versión inglesa cubre
«web developer in Girona» y «custom websites». Los títulos, textos, enlaces
visibles, fotografías y diseño de la web conservan su contenido.

La auditoría y las comparaciones se realizaron en local antes de la publicación
autorizada. No se han enviado URLs a Google. Unos metadatos correctos facilitan
comprensión e indexación; no garantizan una primera posición.

## Auditoría de la versión pública

- El título del portfolio era el mismo en todas sus rutas: nombre + Portfolio.
- La descripción era genérica y no mencionaba Girona.
- No había JSON-LD ni imagen social en el portfolio.
- Los casos de estudio no tenían canonical ni enlaces de idiomas en el head.
- `/sitemap.xml` respondía 200 con el HTML de inicio, no con un sitemap.
- `robots.txt` no declaraba sitemap.
- Las miniaturas tenían textos alternativos genéricos y varias imágenes
  tenían nombres como `imagen1.webp` o `portrait.webp`.

Las consultas públicas de marca, dominio, `site:lawebdejacoto.com` y términos
locales no permitieron verificar una posición de Google. La petición directa
al buscador no proporcionó resultados utilizables. No se dispone de informes
de Search Console, por lo que no se afirma que el sitio esté desindexado ni
se atribuyen posiciones, volúmenes, tráfico o mejoras medidas.

## Implementación

- `data/seo.php`: veinte títulos y descripciones ES/EN, además de alt de retrato
  y miniaturas. Separados de los textos editoriales y de los campos visibles
  de Studio. Sin `meta keywords`: Google no utiliza esa etiqueta.
- `app/seo.php` + `components/seo-head.php`: canonical, hreflang ES/EN/x-default,
  robots, Open Graph, Twitter Cards e imágenes sociales con dimensiones y alt.
- JSON-LD: WebSite, WebPage/ProfilePage, Person, Service, CreativeWork,
  BreadcrumbList e ImageObject. Perfil y servicios salen de los mismos datos que ve el usuario;
  no se inventan direcciones comerciales, valoraciones, premios ni precios.
- `sitemap.xml`: ocho documentos canónicos (portfolio, dos casos de estudio y
  CV, cada uno en ES/EN), versiones de idioma e imágenes. Sin `lastmod` ficticio
  ni prioridades artificiales. `robots.txt` declara su URL pública.
- `data/image-assets.php` + `app/images.php`: 27 nombres descriptivos para los
  mismos bytes WebP. `.htaccess` mantiene las URLs antiguas mediante 301.
  Se conservan tamaños responsive, lazy loading y prioridades de descarga.
- Las secuencias decorativas mantienen alt vacío; las imágenes de golf y las
  capturas técnicas conservan sus descripciones pertinentes. No se añade
  «Girona» a imágenes que no representan la ubicación o el perfil profesional.
- Dos imágenes sociales de 1200 × 630 en ES/EN, exclusivas de los metadatos.

### Canonical de las secciones

`/sobre-mi`, `/servicios`, `/proyectos`, `/experiencia`, `/hobbies` y `/hablemos`
sirven el mismo documento completo; JavaScript solo cambia el scroll. Sus
canonical apuntan a `/` (y las equivalentes inglesas a `/en`). Siguen siendo
navegables y tienen títulos contextualizados, pero no se presentan como seis
landing pages independientes. El sitemap recoge solo documentos distintos.
Las rutas de proyecto con `index.php`, `?lang=es` y parámetros adicionales se
consolidan en la versión canónica del idioma. La vista de impresión del CV
también apunta al CV del mismo idioma sin el parámetro de impresión.

### Mantenimiento

Actualizar los metadatos si cambian los servicios o los proyectos, y el sitemap
si se crean páginas independientes o se retiran imágenes/proyectos. Regenerar
las imágenes sociales cuando cambien nombre, fotografía o identidad visual.
El generador usado para la propuesta local está en el directorio temporal de
OpenCode (`jacoto-social-images.mjs`).

Las redirecciones de imágenes se despliegan junto a los nuevos archivos y
plantillas. El paquete de publicación admite altas, modificaciones y bajas,
verifica hashes y guarda una copia privada de los archivos anteriores. Las
imágenes antiguas se retiran después de instalar sus sustitutas y las reglas 301.

## Verificación

- `php -d xdebug.mode=off tests/seo.php`: metadatos, canonical, hreflang,
  JSON-LD, escapado, contenido dinámico, sitemap, tamaños y reglas 301.
- Comprobación HTTP local: documentos ES/EN, imágenes, srcset y destinos de
  redirecciones; las reglas Apache se verifican también contra el mapa completo.
- Comparación de texto en diez documentos antes/después y capturas idénticas
  de la portada móvil/escritorio; se preserva la propuesta móvil ya aprobada.

## Medición después de publicar

En la propiedad verificada de Google Search Console, enviar
`https://lawebdejacoto.com/sitemap.xml` e inspeccionar los ocho documentos.
Revisar canonical elegida, indexación y rastreo. Comparar consultas, clics,
impresiones, CTR y posición media para «desarrollador web Girona», «páginas web
Girona», «web developer Girona», «tiendas online Girona» y búsquedas de marca,
filtrando por país, dispositivo y periodos equivalentes. Los cambios requieren
un nuevo rastreo y tiempo de observación. No se ha enviado ninguna solicitud.

## Fuentes consultadas: Google Search Central

- [Guía SEO](https://developers.google.com/search/docs/fundamentals/seo-starter-guide?hl=es)
- [Títulos](https://developers.google.com/search/docs/appearance/title-link)
- [SEO de imágenes y alt](https://developers.google.com/search/docs/appearance/google-images)
- [Canonicalización](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls)
- [Políticas de datos estructurados](https://developers.google.com/search/docs/appearance/structured-data/sd-policies)
