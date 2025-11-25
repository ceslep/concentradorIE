<script lang="ts">
  import { theme } from "./themeStore";
  import {
    loadConcentradorData,
    loading,
    lastDuration,
    exportCSV,
    exportExcel,
    concentradorType,
  } from "./storeConcentrador";

  export let showPayloadForm: boolean;
  export let showInfoCantDialog: boolean;

  function handleConcentradorTypeChange(event: Event) {
    const target = event.target as HTMLInputElement;
    concentradorType.set(target.checked ? "areas" : "asignaturas");
    loadConcentradorData(); // Reload data when concentrador type changes
  }
</script>

<header class="flex justify-between items-center flex-wrap gap-3">
  <div class="flex gap-2">
    <!-- Botón de tema -->
    <button
      on:click={theme.toggle}
      class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 {$theme ===
      'dark'
        ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}"
      aria-label="Toggle theme"
    >
      {#if $theme === "dark"}
        <span class="material-symbols-rounded text-2xl">light_mode</span>
      {:else}
        <span class="material-symbols-rounded text-2xl">dark_mode</span>
      {/if}
    </button>

    <!-- Botón para ocultar/mostrar formulario -->
    <button
      on:click={() => (showPayloadForm = !showPayloadForm)}
      class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 {$theme ===
      'dark'
        ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}"
      title={showPayloadForm ? "Ocultar Controles" : "Mostrar Controles"}
    >
      {#if showPayloadForm}
        <span class="material-symbols-rounded text-2xl">visibility_off</span>
      {:else}
        <span class="material-symbols-rounded text-2xl">visibility</span>
      {/if}
    </button>

    <!-- Switch para tipo de concentrador -->
    <div class="flex items-center space-x-2">
      <span
        class="text-sm {$theme === 'dark' ? 'text-gray-300' : 'text-gray-700'}"
        >Asignaturas</span
      >
      <label class="relative inline-flex items-center cursor-pointer">
        <input
          type="checkbox"
          value=""
          class="sr-only peer"
          on:change={handleConcentradorTypeChange}
          checked={$concentradorType === "areas"}
        />
        <div
          class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
        ></div>
        <span
          class="ml-3 text-sm font-medium {$theme === 'dark'
            ? 'text-gray-300'
            : 'text-gray-700'}">Áreas</span
        >
      </label>
    </div>

    <!-- Botón para exportar CSV -->
    <button
      on:click={exportCSV}
      disabled={$loading}
      class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 {$theme ===
      'dark'
        ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}"
      aria-label="Exportar CSV"
      title="Exportar a CSV"
    >
      <span class="material-symbols-rounded text-2xl">file_download</span>
    </button>

    <!-- Botón para exportar Excel -->
    <button
      on:click={exportExcel}
      disabled={$loading}
      class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 {$theme ===
      'dark'
        ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}"
      aria-label="Exportar Excel"
      title="Exportar a Excel"
    >
      <span class="material-symbols-rounded text-2xl">table_view</span>
    </button>

    <!-- Botón para recargar datos -->
    <button
      on:click={() => loadConcentradorData()}
      disabled={$loading}
      class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 {$theme ===
      'dark'
        ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}"
      aria-label="Recargar Datos"
      title="Recargar Datos"
    >
      <span class="material-symbols-rounded text-2xl">refresh</span>
    </button>

    <!-- Botón para mostrar InfoCantDialog -->
    <button
      on:click={() => (showInfoCantDialog = true)}
      class="p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 {$theme ===
      'dark'
        ? 'bg-gray-700 text-gray-300 hover:bg-gray-600'
        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}"
      aria-label="Mostrar Información de Cantidades"
      title="Información de Cantidades"
    >
      <span class="material-symbols-rounded text-2xl">info</span>
    </button>
  </div>

  <!-- Indicador de tiempo de carga -->
  {#if $lastDuration}
    <span
      class="text-xs {$theme === 'dark' ? 'text-gray-500' : 'text-gray-600'}"
      >Carga en: {$lastDuration}ms</span
    >
  {/if}
</header>
