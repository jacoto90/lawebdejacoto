# Entrada editorial del portfolio

La portada combina tres movimientos coordinados: revelado por líneas del
titular, apertura vertical de la fotografía y aparición de una figura vectorial
de 18 elipses. La secuencia dura unos 2,6 segundos y se puede repetir desde
«Repetir entrada».

Al hacer scroll, las elipses se alinean hasta formar un círculo, la fotografía
y su lámina giran en sentidos opuestos y el titular reduce su escala. Los tres
capítulos —Imaginar, Construir, Hacerlo real— siguen el progreso de la escena.
Se usa scroll nativo y solo se fija la composición cuando cabe en la pantalla.

- `components/hero.php`: composición, figura SVG y controles ES/EN.
- `assets/css/hero-cinema.css`: dirección de arte, máscaras y coreografía.
- `assets/js/hero-cinema.js`: entrada, repetición y revelado de foto al aparecer.
- `assets/js/portfolio.js`: progreso del scroll y respuesta magnética.
- `data/editorial.php`: textos bilingües.

La animación es una mejora progresiva: sin JavaScript todo el contenido queda
visible. Movimiento reducido presenta una portada estática y oculta la
repetición. Las rutas internas abren directamente su sección; no ejecutan la
entrada. Cambiar de pestaña o activar movimiento reducido termina la secuencia.
La animación utiliza los assets locales, sin vídeos ni librerías adicionales.
