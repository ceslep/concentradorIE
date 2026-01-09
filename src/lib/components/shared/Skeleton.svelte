<!-- 
SKELETON.SVELTE

DESCRIPCIÓN:
Componente base para crear animaciones de carga (shimmer effect). Permite configurar filas, columnas y anchos específicos para simular cualquier estructura de tabla o lista.

USO:
<Skeleton rows={8} columns={5} theme={$theme} /> usado internamente por LoadingSkeleton.svelte.

DEPENDENCIAS:
- Ninguna externa relevante más allá de props.

PROPS/EMIT:
- Prop: `rows` → number (def: 6) → Cantidad de filas de carga.
- Prop: `columns` → number (def: 4) → Cantidad de columnas decorativas.
- Prop: `headerWidth` → string (def: '33%') → Ancho de la cabecera simulada.
- Prop: `theme` → 'dark' | 'light' (def: 'dark') → Paleta de colores.
- Prop: `columnWidths` → string[] | null → Array opcional de anchos (ej: ['20%', '80%']).

RELACIONES:
- Llamado por: LoadingSkeleton.svelte.

NOTAS DE DESARROLLO:
- Utiliza una animación CSS `@keyframes shimmer` con gradientes lineales.
- Incluye un spinner circular en la esquina superior derecha como indicador adicional.

ESTILOS:
- Variables CSS locales (--bg, --block, --s1, --s2) para gestión de temas.
-->

<script lang="ts">
  // Cantidad de filas a renderizar en el esqueleto
  export let rows = 5;
  // Cantidad de columnas por fila
  export let columns = 3;
  // Ancho base del encabezado (opcional)
  export let headerWidth = "30%";
  // Tema visual (dark o light)
  export let theme: "dark" | "light" = "light";
  // Anchos personalizados para las columnas
  export let columnWidths: string[] = [];

  // Lógica reactiva para generar los arrays de iteración
  $: rowArray = Array(rows).fill(0);
  $: colArray = Array(columns).fill(0);

  /**
   * Obtiene el ancho de una columna específica.
   * @param index - Índice de la columna.
   * @returns El ancho en porcentaje o píxeles.
   */
  function getColWidth(index: number) {
    if (columnWidths && columnWidths[index]) return columnWidths[index];
    return `${100 / columns}%`;
  }
</script>

<div
  class="skeleton-container"
  style="--bg: {theme === 'dark' ? '#0f172a' : '#f8fafc'}; --block: {theme ===
  'dark'
    ? '#374151'
    : '#e5e7eb'}; --s1: {theme === 'dark'
    ? '#2d3748'
    : '#e2e8f0'}; --s2: {theme === 'dark'
    ? '#4a5568'
    : '#cbd5e1'}; position: relative;"
>
  <div class="spinner"></div>
  <div
    class="skeleton skeleton-block skeleton-header"
    style="width:{headerWidth};"
  ></div>
  <div class="skeleton-rows">
    {#each Array(rows) as _, r}
      <div class="skeleton-row">
        {#if columnWidths}
          {#each columnWidths as w}
            <div
              class="skeleton skeleton-block skeleton-cell"
              style="width:{w};"
            ></div>
          {/each}
        {:else}
          {#each Array(columns) as _, c}
            <div
              class="skeleton skeleton-block skeleton-cell"
              style="flex: 1;"
            ></div>
          {/each}
        {/if}
      </div>
    {/each}
  </div>
</div>

<style>
  .skeleton {
    background: linear-gradient(
      90deg,
      var(--s1) 25%,
      var(--s2) 37%,
      var(--s1) 63%
    );
    background-size: 600% 100%;
    animation: shimmer 1.2s ease infinite;
  }
  @keyframes shimmer {
    0% {
      background-position: 100% 0;
    }
    100% {
      background-position: -100% 0;
    }
  }
  .skeleton-block {
    background: var(--block);
    border-radius: 6px;
  }
  .skeleton-container {
    background: var(--bg);
    padding: 16px;
    border-radius: 8px;
  }
  .skeleton-rows {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .skeleton-row {
    display: flex;
    gap: 12px;
    align-items: center;
  }
  .skeleton-header {
    height: 20px;
    margin-bottom: 12px;
  }
  .skeleton-cell {
    height: 14px;
    border-radius: 4px;
  }

  .spinner {
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 3px solid #3498db;
    width: 20px;
    height: 20px;
    -webkit-animation: spin 1s linear infinite;
    animation: spin 1s linear infinite;
    position: absolute;
    top: 10px;
    right: 10px;
  }

  @-webkit-keyframes spin {
    0% {
      -webkit-transform: rotate(0deg);
    }
    100% {
      -webkit-transform: rotate(360deg);
    }
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }
</style>
