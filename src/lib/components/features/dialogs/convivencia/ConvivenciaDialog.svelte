<script lang="ts">
  import { createEventDispatcher, onMount } from "svelte";
  import { fade, scale } from "svelte/transition";
  import {
    fetchConsolidadoConvivencia,
    fetchConvivenciaDetallado,
  } from "../../../../api"; // Import fetchConvivenciaDetallado
  import type {
    ConvivenciaRecord,
    ConsolidadoConvivenciaPayload,
    ConvivenciaDetallado,
  } from "../../../../types"; // Import ConvivenciaDetallado

  export let showDialog: boolean;
  export let estudianteId: string;
  export let year: string;
  export let hasConvivenciaRecords: boolean = false; // New export prop

  let convivenciaRecords: ConvivenciaRecord[] = [];
  let loading = false;
  let error: string | null = null;
  let selectedRecord: ConvivenciaRecord | null = null;
  let convivenciaDetallado: ConvivenciaDetallado | null = null; // New state variable
  let loadingDetalle = false; // New loading state
  let errorDetalle: string | null = null; // New error state

  const dispatch = createEventDispatcher();

  function closeDialog() {
    showDialog = false;
    convivenciaRecords = [];
    error = null;
    selectedRecord = null;
    convivenciaDetallado = null; // Reset detailed data
    errorDetalle = null; // Reset detailed error
    hasConvivenciaRecords = false; // Reset on close
    dispatch("close");
  }

  async function loadConvivenciaData() {
    loading = true;
    error = null;
    try {
      const payload: ConsolidadoConvivenciaPayload = {
        estudiante: estudianteId,
        year: year,
      };
      // Agregar un delay mínimo para que el loading sea visible
      const [data] = await Promise.all([
        fetchConsolidadoConvivencia(payload),
        new Promise((resolve) => setTimeout(resolve, 800)), // 800ms delay mínimo
      ]);
      convivenciaRecords = data;
      hasConvivenciaRecords = convivenciaRecords.length > 0; // Update prop
    } catch (e: any) {
      error = e.message;
      hasConvivenciaRecords = false; // No records on error
    } finally {
      loading = false;
    }
  }

  async function loadConvivenciaDetalladoData(recordInd: string) {
    loadingDetalle = true;
    errorDetalle = null;
    convivenciaDetallado = null; // Clear previous detailed data
    try {
      convivenciaDetallado = await fetchConvivenciaDetallado({
        ind: recordInd,
        year: year,
      });
    } catch (e: any) {
      errorDetalle = e.message;
    } finally {
      loadingDetalle = false;
    }
  }

  $: if (showDialog && estudianteId && year) {
    loadConvivenciaData();
  }

  // Watch for selectedRecord change to load detailed data
  $: if (selectedRecord) {
    loadConvivenciaDetalladoData(selectedRecord.ind);
  }
</script>

{#if showDialog}
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-sans"
    transition:fade={{ duration: 200 }}
    style="font-family: 'Inter', sans-serif;"
  >
    <!-- Backdrop -->
    <div
      class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
      role="button"
      tabindex="0"
      on:click={closeDialog}
      on:keydown={(e) => e.key === "Escape" && closeDialog()}
    ></div>

    <!-- Modal Container -->
    <div
      class="relative w-full max-w-6xl max-h-[90vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700 transform transition-all"
      transition:scale={{ start: 0.96, duration: 200 }}
    >
      <!-- Header -->
      <div
        class="flex-none flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md z-10"
      >
        <div>
          <h2
            class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight leading-none"
          >
            Consolidado de Convivencia
          </h2>
          <div
            class="flex items-center gap-3 mt-2 text-sm text-gray-500 dark:text-gray-400"
          >
            {#if estudianteId}
              <div
                class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-md"
              >
                <span class="material-symbols-rounded text-sm text-gray-400"
                  >person</span
                >
                <span class="font-medium text-gray-700 dark:text-gray-300"
                  >{estudianteId}</span
                >
              </div>
            {/if}
            {#if year}
              <div
                class="flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-md text-indigo-600 dark:text-indigo-400"
              >
                <span class="material-symbols-rounded text-sm"
                  >calendar_month</span
                >
                <span class="font-semibold">{year}</span>
              </div>
            {/if}
          </div>
        </div>
        <button
          on:click={closeDialog}
          class="group p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/40"
        >
          <span
            class="material-symbols-rounded text-gray-400 group-hover:text-red-500 transition-colors text-2xl"
            >close</span
          >
        </button>
      </div>

      <!-- Professional Loading Overlay -->
      {#if loading}
        <div
          class="absolute inset-0 z-20 flex items-center justify-center bg-white/80 dark:bg-gray-900/80 backdrop-blur-md"
          transition:fade={{ duration: 300 }}
        >
          <div class="flex flex-col items-center space-y-6">
            <!-- Animated Spinner Circle -->
            <div class="relative">
              <!-- Outer Ring -->
              <div
                class="w-24 h-24 rounded-full border-4 border-gray-200 dark:border-gray-700"
              ></div>
              <!-- Animated Ring -->
              <div
                class="absolute inset-0 w-24 h-24 rounded-full border-4 border-transparent border-t-indigo-600 dark:border-t-indigo-400 animate-spin"
              ></div>
              <!-- Inner Icon -->
              <div class="absolute inset-0 flex items-center justify-center">
                <span
                  class="material-symbols-rounded text-4xl text-indigo-600 dark:text-indigo-400 animate-pulse"
                  style="font-variation-settings: 'FILL' 1, 'wght' 300, 'GRAD' 0, 'opsz' 48;"
                >
                  school
                </span>
              </div>
            </div>

            <!-- Loading Text -->
            <div class="text-center space-y-2">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Cargando registros
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Obteniendo datos de convivencia...
              </p>
            </div>

            <!-- Animated Dots -->
            <div class="flex space-x-2">
              <div
                class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full animate-bounce"
                style="animation-delay: 0ms;"
              ></div>
              <div
                class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full animate-bounce"
                style="animation-delay: 150ms;"
              ></div>
              <div
                class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full animate-bounce"
                style="animation-delay: 300ms;"
              ></div>
            </div>
          </div>
        </div>
      {/if}

      <!-- Body Content -->
      <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
        <!-- Left Sidebar: List of Records -->
        <div
          class="w-full md:w-80 lg:w-96 flex-none border-r border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-black/20 overflow-y-auto p-4 space-y-3 custom-scrollbar"
        >
          {#if error}
            <div
              class="p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 text-center"
            >
              <p class="text-sm text-red-600 dark:text-red-400 font-medium">
                Error al cargar
              </p>
              <p class="text-xs text-red-500 dark:text-red-500 mt-1">{error}</p>
            </div>
          {:else if convivenciaRecords.length > 0}
            {#each convivenciaRecords as record (record.ind)}
              <button
                on:click={() => (selectedRecord = record)}
                class="w-full text-left group relative p-4 rounded-xl transition-all duration-200 border
                       {selectedRecord === record
                  ? 'bg-white dark:bg-gray-800 border-indigo-500 dark:border-indigo-500 shadow-md ring-1 ring-indigo-500 z-10'
                  : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-sm'}"
              >
                <div class="flex justify-between items-start mb-2">
                  <span
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500"
                  >
                    {record.fecha}
                  </span>
                  <span
                    class="text-xs font-mono text-gray-400 dark:text-gray-500"
                  >
                    {record.hora}
                  </span>
                </div>
                <h4
                  class="text-sm font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                >
                  {record.tipoFalta}
                </h4>
                <p
                  class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1"
                >
                  {record.asignatura}
                </p>

                {#if selectedRecord === record}
                  <div
                    class="absolute left-0 top-4 bottom-4 w-1 bg-indigo-500 rounded-r-full"
                    transition:scale={{ start: 0, duration: 200 }}
                  ></div>
                {/if}
              </button>
            {/each}
          {:else}
            <div
              class="flex flex-col items-center justify-center h-64 text-center p-6"
            >
              <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-full mb-3">
                <span class="material-symbols-rounded text-4xl text-gray-400"
                  >inbox</span
                >
              </div>
              <p class="text-gray-500 dark:text-gray-400 font-medium">
                No hay registros
              </p>
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                Este estudiante no tiene registros de convivencia.
              </p>
            </div>
          {/if}
        </div>

        <!-- Right Main: Details -->
        <div
          class="flex-1 bg-white dark:bg-gray-900 overflow-y-auto relative custom-scrollbar"
        >
          {#if loadingDetalle}
            <div class="p-8 space-y-6">
              <div
                class="w-1/3 h-8 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"
              ></div>
              <div class="grid grid-cols-2 gap-6">
                <div
                  class="h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"
                ></div>
                <div
                  class="h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"
                ></div>
                <div
                  class="h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"
                ></div>
                <div
                  class="h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"
                ></div>
              </div>
              <div
                class="h-32 bg-gray-100 dark:bg-gray-800 rounded animate-pulse"
              ></div>
            </div>
          {:else if errorDetalle}
            <div
              class="flex flex-col items-center justify-center h-full p-8 text-center"
            >
              <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-full mb-4">
                <span class="material-symbols-rounded text-4xl text-red-500"
                  >error</span
                >
              </div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Error al cargar detalles
              </h3>
              <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-xs">
                {errorDetalle}
              </p>
            </div>
          {:else if convivenciaDetallado}
            <div class="p-6 md:p-8 space-y-8" in:fade={{ duration: 200 }}>
              <!-- Header Section of Details -->
              <div>
                <div class="flex items-center gap-2 mb-4">
                  <span
                    class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400"
                  >
                    Detalle del Registro
                  </span>
                  <span class="text-xs text-gray-400 dark:text-gray-500">
                    Registrado: {convivenciaDetallado.cv_fechahora}
                  </span>
                </div>
                <h3
                  class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white leading-tight"
                >
                  {convivenciaDetallado.cv_tipoFalta}
                </h3>
              </div>

              <!-- Info Grid -->
              <div
                class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800"
              >
                <div>
                  <p
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                  >
                    Estudiante
                  </p>
                  <p
                    class="text-base font-medium text-gray-900 dark:text-white"
                  >
                    {convivenciaDetallado.nombres}
                  </p>
                </div>
                <div>
                  <p
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                  >
                    Docente
                  </p>
                  <p
                    class="text-base font-medium text-gray-900 dark:text-white"
                  >
                    {convivenciaDetallado.cv_docente}
                  </p>
                </div>
                <div>
                  <p
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                  >
                    Asignatura
                  </p>
                  <p
                    class="text-base font-medium text-gray-900 dark:text-white"
                  >
                    {convivenciaDetallado.cv_asignatura}
                  </p>
                </div>
                <div>
                  <p
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                  >
                    Fecha y Hora
                  </p>
                  <p
                    class="text-base font-medium text-gray-900 dark:text-white"
                  >
                    {convivenciaDetallado.cv_fecha} - {convivenciaDetallado.cv_hora}
                  </p>
                </div>
              </div>

              <!-- Content Sections -->
              <div class="space-y-6">
                {#if convivenciaDetallado.cv_faltas}
                  <div class="prose dark:prose-invert max-w-none">
                    <h4
                      class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"
                    >
                      <span class="w-1 h-6 bg-orange-500 rounded-full"></span> Faltas
                    </h4>
                    <div
                      class="p-4 bg-orange-50 dark:bg-orange-900/10 rounded-xl text-gray-700 dark:text-gray-300 border border-orange-100 dark:border-orange-900/20"
                    >
                      {convivenciaDetallado.cv_faltas}
                    </div>
                  </div>
                {/if}

                {#if convivenciaDetallado.cv_descripcionSituacion}
                  <div class="prose dark:prose-invert max-w-none">
                    <h4
                      class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"
                    >
                      <span class="w-1 h-6 bg-blue-500 rounded-full"></span> Descripción
                      de la Situación
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                      {convivenciaDetallado.cv_descripcionSituacion}
                    </p>
                  </div>
                {/if}

                {#if convivenciaDetallado.cv_descargosEstudiante}
                  <div class="prose dark:prose-invert max-w-none">
                    <h4
                      class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"
                    >
                      <span class="w-1 h-6 bg-purple-500 rounded-full"></span> Descargos
                      del Estudiante
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                      {convivenciaDetallado.cv_descargosEstudiante}
                    </p>
                  </div>
                {/if}

                {#if convivenciaDetallado.cv_positivos}
                  <div class="prose dark:prose-invert max-w-none">
                    <h4
                      class="text-lg font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2"
                    >
                      <span class="w-1 h-6 bg-green-500 rounded-full"></span> Aspectos
                      Positivos
                    </h4>
                    <div
                      class="p-4 bg-green-50 dark:bg-green-900/10 rounded-xl text-gray-700 dark:text-gray-300 border border-green-100 dark:border-green-900/20"
                    >
                      {convivenciaDetallado.cv_positivos}
                    </div>
                  </div>
                {/if}
              </div>

              <!-- Signatures -->
              <div
                class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-gray-100 dark:border-gray-800 mt-8"
              >
                {#if convivenciaDetallado.cv_firma}
                  <div class="text-center">
                    <div
                      class="bg-white dark:bg-white rounded-lg border border-gray-200 p-2 inline-block shadow-sm mb-3"
                    >
                      <img
                        src={convivenciaDetallado.cv_firma}
                        alt="Firma del Estudiante"
                        class="h-24 object-contain"
                      />
                    </div>
                    <p
                      class="text-sm font-medium text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-2 inline-block px-8"
                    >
                      Firma del Estudiante
                    </p>
                  </div>
                {/if}
                {#if convivenciaDetallado.firmaAcudiente}
                  <div class="text-center">
                    <div
                      class="bg-white dark:bg-white rounded-lg border border-gray-200 p-2 inline-block shadow-sm mb-3"
                    >
                      <img
                        src={convivenciaDetallado.firmaAcudiente}
                        alt="Firma del Acudiente"
                        class="h-24 object-contain"
                      />
                    </div>
                    <p
                      class="text-sm font-medium text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-2 inline-block px-8"
                    >
                      Firma del Acudiente
                    </p>
                  </div>
                {/if}
              </div>
            </div>
          {:else}
            <!-- Empty State for Details -->
            <div
              class="flex flex-col items-center justify-center h-full text-center p-8 opacity-50"
            >
              <div
                class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6"
              >
                <span
                  class="material-symbols-rounded text-5xl text-gray-300 dark:text-gray-600"
                  >touch_app</span
                >
              </div>
              <h3
                class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
              >
                Seleccione un registro
              </h3>
              <p class="text-gray-500 dark:text-gray-400 max-w-sm">
                Haga clic en uno de los registros de la lista izquierda para ver
                los detalles completos de la convivencia.
              </p>
            </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
{/if}
