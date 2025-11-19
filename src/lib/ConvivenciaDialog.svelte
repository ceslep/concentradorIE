<script lang="ts">
  import { createEventDispatcher, onMount } from 'svelte';
  import { fetchConsolidadoConvivencia, fetchConvivenciaDetallado } from './api'; // Import fetchConvivenciaDetallado
  import type { ConvivenciaRecord, ConsolidadoConvivenciaPayload, ConvivenciaDetallado } from './types'; // Import ConvivenciaDetallado
  import Skeleton from './Skeleton.svelte';
  import { theme } from './themeStore';

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
    dispatch('close');
  }

  async function loadConvivenciaData() {
    loading = true;
    error = null;
    try {
      const payload: ConsolidadoConvivenciaPayload = {
        estudiante: estudianteId,
        year: year
      };
      convivenciaRecords = await fetchConsolidadoConvivencia(payload);
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
      convivenciaDetallado = await fetchConvivenciaDetallado({ ind: recordInd, year: year });
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
  <div class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4 z-50 backdrop-blur-sm transition-opacity duration-300"
       class:opacity-100={showDialog}
       class:opacity-0={!showDialog}>
    <div class="rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col border transform transition-all duration-300 ease-out
                {$theme === 'dark' ? 'bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700' : 'bg-white border-gray-300'}"
         class:scale-100={showDialog}
         class:scale-95={!showDialog}
         class:opacity-100={showDialog}
         class:opacity-0={!showDialog}>
      <div class="flex justify-between items-center p-4 border-b
                  {$theme === 'dark' ? 'border-gray-700 bg-gray-800' : 'border-gray-200 bg-gray-50'}">
        <h3 class="text-2xl font-extrabold tracking-wide {$theme === 'dark' ? 'text-white' : 'text-gray-800'}">
          Consolidado de Convivencia
          {#if estudianteId}
            <span class="text-sm {$theme === 'dark' ? 'text-yellow-400' : 'text-yellow-600'}">Estudiante ID: {estudianteId}</span><br>
          {/if}
          {#if year}
            <span class="text-sm {$theme === 'dark' ? 'text-green-400' : 'text-green-600'}">Año: {year}</span>
          {/if}
        </h3>
        <button on:click={closeDialog} class="text-gray-400 hover:text-red-500 transition duration-300 transform hover:scale-110" title="Cerrar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="p-4 flex-grow grid grid-cols-1 md:grid-cols-3 gap-4 overflow-y-auto">
        <!-- Columna de tarjetas (izquierda) -->
        <div class="md:col-span-1 overflow-y-auto pr-4 border-r {$theme === 'dark' ? 'border-gray-700' : 'border-gray-300'}">
          {#if loading}
            <Skeleton rows={5} columns={1} theme={$theme} />
          {:else if error}
            <p class="error text-red-500 font-medium text-center py-4">Error al cargar el consolidado de convivencia: {error}</p>
          {:else if convivenciaRecords.length > 0}
            <ul class="space-y-4">
              {#each convivenciaRecords as record (record.ind)}
                <li>
                  <div
                    class="p-5 rounded-xl shadow-lg transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-xl
                                  {$theme === 'dark' ? 'bg-gray-700 hover:bg-gray-600 border border-gray-600' : 'bg-white hover:bg-gray-50 border border-gray-200'}
                                  {selectedRecord === record ? ($theme === 'dark' ? 'ring-2 ring-blue-500 border-blue-500' : 'ring-2 ring-blue-400 border-blue-400') : ''}"
                  >
                    <p class="text-lg font-semibold {$theme === 'dark' ? 'text-white' : 'text-gray-800'} mb-1">{record.fecha} - {record.hora}</p>
                    <p class="text-sm {$theme === 'dark' ? 'text-gray-300' : 'text-gray-600'}">{record.tipoFalta}</p>
                    <p class="text-sm {$theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}">Asignatura: {record.asignatura}</p>
                    <div class="text-center">
                      <button
                        on:click={() => (selectedRecord = record)}
                        class="mt-4 px-5 py-2.5 rounded-full text-sm font-bold tracking-wide transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-3 focus:ring-opacity-60
                                    {$theme === 'dark' ? 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500' : 'bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400'}">
                        Ver Detalles
                      </button>
                    </div>
                  </div>
                </li>
              {/each}
            </ul>
          {:else}
            <p class="text-center py-8 text-lg {$theme === 'dark' ? 'text-gray-400' : 'text-gray-600'}">No se encontraron registros de convivencia.</p>
          {/if}
        </div>

        <!-- Columna de contenido (derecha) -->
        <div class="md:col-span-2 p-6 {$theme === 'dark' ? 'bg-gray-800 rounded-xl shadow-inner' : 'bg-gray-50 rounded-xl shadow-inner'}">
          {#if loadingDetalle}
            <Skeleton rows={10} columns={1} theme={$theme} />
          {:else if errorDetalle}
            <p class="error text-red-500 font-medium text-center py-4">Error al cargar el detalle de convivencia: {errorDetalle}</p>
          {:else if convivenciaDetallado}
            <div class="space-y-5 {$theme === 'dark' ? 'text-gray-200' : 'text-gray-800'}">
              <h4 class="text-2xl font-extrabold mb-5 pb-2 border-b {$theme === 'dark' ? 'border-gray-700 text-white' : 'border-gray-200 text-gray-900'}">Detalle del Registro</h4>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Nombres:</strong> {convivenciaDetallado.nombres}</p>
                <p><strong>Docente:</strong> {convivenciaDetallado.cv_docente}</p>
                <p><strong>Asignatura:</strong> {convivenciaDetallado.cv_asignatura}</p>
                <p><strong>Tipo de Situación:</strong> {convivenciaDetallado.cv_tipoFalta}</p>
                <p><strong>Fecha:</strong> {convivenciaDetallado.cv_fecha}</p>
                <p><strong>Hora:</strong> {convivenciaDetallado.cv_hora}</p>
              </div>

              {#if convivenciaDetallado.cv_faltas}
                <div class="p-4 rounded-lg {$theme === 'dark' ? 'bg-gray-700' : 'bg-gray-100'} shadow-sm">
                  <p class="font-semibold mb-2">Faltas:</p>
                  <p class="text-sm"> {convivenciaDetallado.cv_faltas}</p>
                </div>
              {/if}

              {#if convivenciaDetallado.cv_descripcionSituacion}
                <div class="p-4 rounded-lg {$theme === 'dark' ? 'bg-gray-700' : 'bg-gray-100'} shadow-sm">
                  <p class="font-semibold mb-2">Descripción de la Situación:</p>
                  <p class="text-sm"> {convivenciaDetallado.cv_descripcionSituacion}</p>
                </div>
              {/if}

              {#if convivenciaDetallado.cv_descargosEstudiante}
                <div class="p-4 rounded-lg {$theme === 'dark' ? 'bg-gray-700' : 'bg-gray-100'} shadow-sm">
                  <p class="font-semibold mb-2">Descargos del Estudiante:</p>
                  <p class="text-sm"> {convivenciaDetallado.cv_descargosEstudiante}</p>
                </div>
              {/if}

              {#if convivenciaDetallado.cv_positivos}
                <div class="p-4 rounded-lg {$theme === 'dark' ? 'bg-gray-700' : 'bg-gray-100'} shadow-sm">
                  <p class="font-semibold mb-2">Aspectos Positivos:</p>
                  <p class="text-sm"> {convivenciaDetallado.cv_positivos}</p>
                </div>
              {/if}
              
              <p class="text-sm text-right {$theme === 'dark' ? 'text-gray-400' : 'text-gray-500'}">Registrado: {convivenciaDetallado.cv_fechahora}</p>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {#if convivenciaDetallado.cv_firma}
                  <div class="p-4 rounded-lg {$theme === 'dark' ? 'bg-gray-700' : 'bg-gray-100'} shadow-sm text-center">
                    <p class="font-semibold mb-2">Firma del Estudiante:</p>
                    <img src="{convivenciaDetallado.cv_firma}" alt="Firma del Estudiante" class="max-w-full h-auto border {$theme === 'dark' ? 'border-gray-600' : 'border-gray-300'} rounded-md mx-auto"/>
                  </div>
                {/if}
                {#if convivenciaDetallado.firmaAcudiente}
                  <div class="p-4 rounded-lg {$theme === 'dark' ? 'bg-gray-700' : 'bg-gray-100'} shadow-sm text-center">
                    <p class="font-semibold mb-2">Firma del Acudiente:</p>
                    <img src="{convivenciaDetallado.firmaAcudiente}" alt="Firma del Acudiente" class="max-w-full h-auto border {$theme === 'dark' ? 'border-gray-600' : 'border-gray-300'} rounded-md mx-auto"/>
                  </div>
                {/if}
              </div>
            </div>
          {:else if selectedRecord}
            <div class="flex items-center justify-center h-full">
              <p class="text-lg {$theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}">Cargando detalle...</p>
            </div>
          {:else}
            <div class="flex items-center justify-center h-full">
              <p class="text-lg {$theme === 'dark' ? 'text-gray-500' : 'text-gray-400'}">Seleccione un registro para ver el detalle.</p>
            </div>
          {/if}
        </div>
      </div>

      <div class="p-4 border-t flex justify-end
                  {$theme === 'dark' ? 'border-gray-700 bg-gray-800' : 'border-gray-200 bg-gray-50'}">
        <button on:click={closeDialog} class="px-6 py-2 rounded-lg text-white font-semibold transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-opacity-50
                    {$theme === 'dark' ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : 'bg-blue-500 hover:bg-blue-600 focus:ring-blue-400'}">Cerrar</button>
      </div>
    </div>
  </div>
{/if}
