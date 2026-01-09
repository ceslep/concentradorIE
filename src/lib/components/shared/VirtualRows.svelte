<!-- 
VIRTUALROWS.SVELTE

DESCRIPCIÓN:
Componente de virtualización de listas para optimizar el renderizado de grandes tablas. Solo renderiza las filas visibles en el viewport más un margen de seguridad (overscan).

USO:
<VirtualRows items={items} let:item> <MiFilaComponente {item} /> </VirtualRows>

DEPENDENCIAS:
- Ninguna externa.

PROPS/EMIT:
- Prop: `items` → any[] → Lista total de elementos a virtualizar.
- Prop: `rowHeight` → number (def: 48) → Altura fija en píxeles de cada fila.
- Prop: `overscan` → number (def: 5) → Filas extra a renderizar arriba/abajo.
- Prop: `containerHeight` → number (def: 600) → Altura del contenedor visible.

RELACIONES:
- Usado en: ConcentradorAsignaturasTable.svelte y ConcentradorAreasTable.svelte.

NOTAS DE DESARROLLO:
- Requiere altura de fila fija para cálculos precisos de scroll.
- No está optimizado para filas de altura dinámica.

ESTILOS:
- Usa 'transform: translateY' para el posicionamiento eficiente de filas.
- Clase 'vr-phantom' para mantener el scrollbar del tamaño real del contenido.
-->

<script lang="ts">
  import { onMount } from "svelte";
  export let items: any[] = [];
  export let rowHeight = 48;
  export let overscan = 5;
  export let containerHeight = 600;

  let scrollTop = 0;
  let start = 0;
  let end = 0;
  let viewport: HTMLDivElement | null = null;

  function update() {
    const s = scrollTop;
    const visibleCount = Math.ceil(containerHeight / rowHeight);
    start = Math.max(0, Math.floor(s / rowHeight) - overscan);
    end = Math.min(items.length, start + visibleCount + overscan * 2);
  }

  function onScroll(e: Event) {
    scrollTop = (e.target as HTMLDivElement).scrollTop;
    update();
  }

  onMount(() => {
    update();
    if (viewport) {
      // Try to adapt containerHeight to actual element if not explicitly set
      containerHeight = viewport.clientHeight || containerHeight;
      update();
    }
  });
</script>

<div
  bind:this={viewport}
  class="vr-container"
  on:scroll={onScroll}
  style="height:{containerHeight}px;"
>
  <div class="vr-phantom" style="height:{items.length * rowHeight}px"></div>
  {#each items.slice(start, end) as item, i}
    <div
      class="vr-row"
      style="transform: translateY({(start + i) *
        rowHeight}px); height:{rowHeight}px;"
    >
      <slot {item} {i}></slot>
    </div>
  {/each}
</div>

<style>
  .vr-container {
    overflow: auto;
    position: relative;
  }
  .vr-phantom {
    width: 1px;
    opacity: 0;
  }
  .vr-row {
    position: absolute;
    left: 0;
    right: 0;
    display: flex;
  }
</style>
