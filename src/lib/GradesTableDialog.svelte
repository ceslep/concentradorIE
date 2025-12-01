<script lang="ts">
  import GradesTable2 from "./GradesTable2.svelte";
  import GradesStatistics from "./GradesStatistics.svelte";
  import { theme } from "./themeStore";
  import { selectedAsignatura } from "./storeConcentrador";

  export let showDialog: boolean;
  export let docenteId: string; // New prop
  export let periodo: string;

  function closeDialog() {
    showDialog = false;
  }

  let tableData: any[] = [];
  let showStats = false;
  let isLoading = false;
</script>

{#if showDialog}
  <div
    class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4 z-50 backdrop-blur-sm transition-opacity duration-300"
    class:opacity-100={showDialog}
    class:opacity-0={!showDialog}
  >
    <div
      class="rounded-2xl shadow-2xl w-auto max-w-[98vw] max-h-[95vh] overflow-hidden flex flex-col border transform transition-all duration-300 ease-out
                   {$theme === 'dark'
        ? 'bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700'
        : 'bg-white border-gray-300'}"
      class:scale-100={showDialog}
      class:scale-95={!showDialog}
      class:opacity-100={showDialog}
      class:opacity-0={!showDialog}
    >
      <div
        class="flex justify-between items-center p-4 border-b
                        {$theme === 'dark'
          ? 'border-gray-700 bg-gray-800'
          : 'border-gray-200 bg-gray-50'}"
      >
        <h3
          class="text-2xl font-extrabold tracking-wide {$theme === 'dark'
            ? 'text-white'
            : 'text-gray-800'}"
        >
          Detalle de Calificaciones para {$selectedAsignatura ||
            "la Asignatura Seleccionada"}
        </h3>
        <button
          on:click={closeDialog}
          class="text-gray-400 hover:text-red-500 transition duration-300 transform hover:scale-110"
          title="Cerrar"
        >
          <span class="material-symbols-rounded text-2xl">close</span>
        </button>
      </div>

      <!-- Toolbar for View Toggle -->
      <div
        class="px-4 py-2 border-b flex justify-end gap-2
                    {$theme === 'dark'
          ? 'border-gray-700 bg-gray-800/50'
          : 'border-gray-200 bg-gray-50/50'}"
      >
        <button
          class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                        {!showStats
            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'}"
          on:click={() => (showStats = false)}
        >
          <span class="material-symbols-rounded text-lg">table_chart</span>
          Tabla
        </button>
        <button
          class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                        {showStats
            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'}"
          on:click={() => (showStats = true)}
        >
          <span class="material-symbols-rounded text-lg">bar_chart</span>
          Estadísticas
        </button>
      </div>

      <div class="p-4 overflow-y-auto flex-grow">
        <!-- GradesTable.svelte will automatically read from Svelte stores -->
        <div style="display: {showStats ? 'none' : 'block'}; height: 100%;">
          <GradesTable2
            tableNotasId="gradesTableInDialog"
            {docenteId}
            {periodo}
            bind:tableData
            bind:isLoading
          />
        </div>
        {#if showStats}
          <GradesStatistics data={tableData} loading={isLoading} />
        {/if}
      </div>

      <div
        class="p-4 border-t flex justify-end
                        {$theme === 'dark'
          ? 'border-gray-700 bg-gray-800'
          : 'border-gray-200 bg-gray-50'}"
      >
        <button
          on:click={closeDialog}
          class="px-6 py-2 rounded-lg text-white font-semibold transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-opacity-50
                            {$theme === 'dark'
            ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
            : 'bg-blue-500 hover:bg-blue-600 focus:ring-blue-400'}"
          >Cerrar</button
        >
      </div>
    </div>
  </div>
{/if}
