# Galería de proyectos

La sección Proyectos presenta los registros activos de Studio en dos filas
continuas, con direcciones opuestas. Se conserva una sola copia accesible de
cada proyecto; las copias visuales del bucle se excluyen del lector de pantalla
y de la navegación por Tab. Todos los enlaces visibles siguen siendo navegables.

## Interacción

- Desplazamiento automático y flotación suave mientras la galería está visible.
- Respuesta a la velocidad del scroll vertical, sin capturar la rueda.
- Arrastre horizontal con ratón o táctil e inercia. En táctil, el movimiento se
  amplifica 1,55× y un lanzamiento puede alcanzar 2600 px/s. La velocidad cae
  gradualmente hasta enlazar con el avance automático en la misma dirección;
  la otra fila recibe un impulso menor en sentido opuesto. Agarrar de nuevo,
  pausar o enfocar con teclado permite detener el impulso. El gesto vertical
  se reserva para el scroll de página. Arrastrar un enlace no lo abre.
- Ligera inclinación y atracción hacia el cursor en dispositivos con ratón.
- Pausa al pasar sobre una tarjeta o enfocar la galería, y botón de pausa manual.
- Flechas para desplazar las filas y «Ver todos» para volver a una cuadrícula fija.
- Movimiento reducido y ausencia de JavaScript: cuadrícula completa sin clones.
- Animaciones detenidas fuera de pantalla o con la pestaña oculta.
- Seis familias pastel con más profundidad, vinculadas a cada proyecto:
  terracota para fotografía, verde para SticNow, azul/agua para Logística,
  lavanda para Content Generator y rojo coral para Paso Seguro. La invitación
  alterna los verdes de la portada: lima con texto oscuro y verde oscuro con
  texto lima. Cada bucle alterna las dos variantes sin saltos.
- En móvil, las tarjetas del carrusel ocupan el 72 % de la pantalla (máximo
  300 px), dejando ver más de la siguiente tarjeta.

El contenido sigue procediendo de `profile['projects']`. La tarjeta de invitación
final enlaza a Contacto y no se cuenta como proyecto. Los enlaces internos se
abren en la misma pestaña; los externos conservan su pestaña nueva.
Las miniaturas y sus variantes responsive llevan versión por fecha de archivo
para mostrar las capturas actualizadas aunque el navegador conserve las anteriores.

## Archivos

- `components/projects.php`: contenido y estructura progresiva.
- `assets/css/projects.css`: estilos limitados a esta sección.
- `assets/js/projects.js`: bucle, gestos, foco, controles y preferencias.
- `data/editorial.php`: textos ES/EN, compatibles con los overrides de Studio.
- `index.php`: carga de los nuevos assets con versión por fecha de modificación.

La galería se inicializa antes del script general del portfolio para resolver
correctamente las rutas `/proyectos` y `/en/projects` después de medir el layout.
