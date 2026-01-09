<script lang="ts">
  import { onMount } from "svelte";
  import { fetchInasistenciasDetallado } from "../../../../api";
  import type {
    Inasistencia,
    InasistenciasDetalladoPayload,
  } from "../../../../types";
  import { theme } from "../../../../themeStore";
  import { fade, scale } from "svelte/transition";

  export let showDialog: boolean;
  export let estudianteId: string;
  export let nombres: string;
  export let asignatura: string;
  export let periodo: string;
  export let year: string;

  let inasistencias: Inasistencia[] = [];
  let loading = false;
  let error: string | null = null;

  function closeDialog() {
    showDialog = false;
    inasistencias = [];
    error = null;
  }

  async function loadInasistencias() {
    loading = true;
    error = null;
    try {
      const payload: InasistenciasDetalladoPayload = {
        estudiante: estudianteId,
        nombres: nombres,
        asignatura: asignatura,
        periodo: periodo,
        year: year,
      };
      inasistencias = await fetchInasistenciasDetallado(payload);
    } catch (e: any) {
      error = e.message;
    } finally {
      loading = false;
    }
  }

  // Reactive statement to re-fetch data when props change and dialog is open
  let lastFetchedKey: string | null = null;
  $: {
    if (showDialog && estudianteId && nombres && asignatura && periodo) {
      const key = `${estudianteId}-${nombres}-${asignatura}-${periodo}`;
      if (key !== lastFetchedKey) {
        lastFetchedKey = key;
        loadInasistencias();
      }
    } else if (!showDialog) {
      lastFetchedKey = null;
      inasistencias = []; // Clear data when dialog is closed
    }
  }
</script>

{#if showDialog}
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-sans"
    transition:fade={{ duration: 250 }}
    style="font-family: 'Inter', sans-serif;"
  >
    <!-- Backdrop -->
    <div
      class="absolute inset-0 bg-gradient-to-br from-gray-900/65 via-gray-900/60 to-gray-900/65 backdrop-blur-md transition-opacity"
      role="button"
      tabindex="0"
      on:click={closeDialog}
      on:keydown={(e) => e.key === "Escape" && closeDialog()}
      aria-label="Cerrar diálogo"
      title="Cerrar diálogo"
    ></div>

    <!-- Modal Container -->
    <div
      class="relative w-full max-w-5xl max-h-[92vh] flex flex-col bg-white dark:bg-[#1a1b1e] rounded-3xl shadow-2xl overflow-hidden border border-gray-200/80 dark:border-gray-700/60 transform transition-all"
      transition:scale={{ start: 0.94, duration: 250 }}
    >
      <!-- Header -->
      <div
        class="flex-none flex items-center justify-between px-7 py-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-br from-white/95 via-white/90 to-white/95 dark:from-gray-900/95 dark:via-gray-900/90 dark:to-gray-900/95 backdrop-blur-xl z-10"
      >
        <div class="flex-1 min-w-0">
          <h2
            class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent tracking-tight leading-tight mb-3"
          >
            Inasistencias Detalladas
          </h2>
          <div class="flex flex-wrap items-center gap-2 text-sm">
            {#if nombres}
              <div
                class="flex items-center gap-2 bg-gray-100/80 dark:bg-gray-800/80 px-3 py-1.5 rounded-lg group hover:bg-gray-200/80 dark:hover:bg-gray-700/80 transition-colors"
              >
                <div
                  class="p-0.5 rounded bg-gray-200 dark:bg-gray-700 group-hover:bg-gray-300 dark:group-hover:bg-gray-600 transition-colors"
                >
                  <span
                    class="material-symbols-rounded text-base text-gray-600 dark:text-gray-400"
                    >person</span
                  >
                </div>
                <span class="font-semibold text-gray-800 dark:text-gray-200"
                  >{nombres}</span
                >
              </div>
            {/if}
            {#if asignatura}
              <div
                class="flex items-center gap-2 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/40 dark:to-blue-900/40 px-3 py-1.5 rounded-lg text-indigo-700 dark:text-indigo-300 border border-indigo-100/50 dark:border-indigo-800/30 shadow-sm"
              >
                <span class="material-symbols-rounded text-base">book</span>
                <span class="font-bold">{asignatura}</span>
              </div>
            {/if}
            {#if periodo}
              <div
                class="flex items-center gap-2 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/40 dark:to-green-900/40 px-3 py-1.5 rounded-lg text-emerald-700 dark:text-emerald-300 border border-emerald-100/50 dark:border-emerald-800/30 shadow-sm"
              >
                <span class="material-symbols-rounded text-base"
                  >calendar_today</span
                >
                <span class="font-bold">Periodo {periodo}</span>
              </div>
            {/if}
          </div>
        </div>
        <button
          on:click={closeDialog}
          class="group p-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/30 flex-shrink-0"
        >
          <span
            class="material-symbols-rounded text-gray-500 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors text-[24px] font-medium"
            >close</span
          >
        </button>
      </div>

      <!-- Content -->
      <div
        class="flex-1 overflow-y-auto p-0 custom-scrollbar bg-gradient-to-br from-gray-50/80 via-gray-50/60 to-gray-50/80 dark:from-black/30 dark:via-black/20 dark:to-black/30"
      >
        {#if loading}
          <div class="flex flex-col items-center justify-center h-80 space-y-5">
            <div class="relative">
              <div
                class="absolute inset-0 bg-indigo-500/20 rounded-full blur-2xl animate-pulse"
              ></div>
              <span
                class="material-symbols-rounded text-7xl text-indigo-600 dark:text-indigo-400 animate-spin relative"
                >progress_activity</span
              >
            </div>
            <div class="text-center space-y-2">
              <p
                class="text-base font-semibold text-gray-700 dark:text-gray-300"
              >
                Cargando inasistencias...
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-500">
                Por favor espera un momento
              </p>
            </div>
          </div>
        {:else if error}
          <div
            class="flex flex-col items-center justify-center h-80 text-center p-8"
          >
            <div
              class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 p-5 rounded-2xl mb-5 shadow-sm"
            >
              <span
                class="material-symbols-rounded text-5xl text-red-600 dark:text-red-400"
                >error</span
              >
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
              No se pudo cargar la información
            </h3>
            <p
              class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed"
            >
              {error}
            </p>
          </div>
        {:else if inasistencias.length > 0}
          <div class="p-6 md:p-8">
            <div
              class="overflow-hidden rounded-2xl border border-gray-200/80 dark:border-gray-700/50 shadow-lg bg-white dark:bg-gray-800/90 backdrop-blur-sm"
            >
              <div class="overflow-x-auto custom-table-scrollbar">
                <table
                  class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                >
                  <thead
                    class="bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-900/80 dark:to-gray-900/60 border-b-2 border-gray-200 dark:border-gray-700"
                  >
                    <tr>
                      <th
                        scope="col"
                        class="px-6 md:px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                        >Fecha</th
                      >
                      <th
                        scope="col"
                        class="px-6 md:px-8 py-4 text-center text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                        >Horas</th
                      >
                      <th
                        scope="col"
                        class="px-6 md:px-8 py-4 text-center text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                        >Hora Clase</th
                      >
                      <th
                        scope="col"
                        class="px-6 md:px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                        >Detalle</th
                      >
                      <th
                        scope="col"
                        class="px-6 md:px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider"
                        >Excusa</th
                      >
                    </tr>
                  </thead>
                  <tbody
                    class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800/90"
                  >
                    {#each inasistencias as inasistencia, index (inasistencia.ind)}
                      <tr
                        class="hover:bg-gradient-to-r hover:from-gray-50/80 hover:to-transparent dark:hover:from-gray-700/40 dark:hover:to-transparent transition-all duration-200 {index %
                          2 ===
                        0
                          ? ''
                          : 'bg-gray-50/30 dark:bg-gray-800/30'}"
                      >
                        <td class="px-6 md:px-8 py-4 whitespace-nowrap">
                          <div class="flex items-center gap-2">
                            <span
                              class="material-symbols-rounded text-base text-gray-400"
                              >calendar_month</span
                            >
                            <span
                              class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                              >{inasistencia.fecha}</span
                            >
                          </div>
                        </td>
                        <td
                          class="px-6 md:px-8 py-4 whitespace-nowrap text-center"
                        >
                          <span
                            class="inline-flex items-center justify-center min-w-[3rem] px-3 py-1 rounded-lg text-sm font-bold bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600 shadow-sm"
                          >
                            {inasistencia.horas}
                          </span>
                        </td>
                        <td
                          class="px-6 md:px-8 py-4 whitespace-nowrap text-center"
                        >
                          <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-300 font-mono"
                          >
                            {inasistencia.hora_clase}
                          </span>
                        </td>
                        <td class="px-6 md:px-8 py-4">
                          {#if inasistencia.detalle}
                            <div class="flex items-start gap-2 max-w-md">
                              <div
                                class="p-1 rounded-md bg-blue-50 dark:bg-blue-900/30 mt-0.5 flex-shrink-0"
                              >
                                <span
                                  class="material-symbols-rounded text-base text-blue-600 dark:text-blue-400"
                                  >info</span
                                >
                              </div>
                              <span
                                class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed"
                                >{inasistencia.detalle}</span
                              >
                            </div>
                          {:else}
                            <span
                              class="text-sm text-gray-400 dark:text-gray-500 italic"
                              >Sin detalle</span
                            >
                          {/if}
                        </td>
                        <td class="px-6 md:px-8 py-4">
                          {#if inasistencia.excusa_motivo}
                            <div class="flex flex-col gap-2">
                              <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/40 dark:to-green-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/30 w-fit shadow-sm"
                              >
                                <span class="material-symbols-rounded text-sm"
                                  >check_circle</span
                                >
                                Excusada
                              </span>
                              <div class="pl-1 space-y-1">
                                <p
                                  class="text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                  {inasistencia.excusa_motivo}
                                </p>
                                {#if inasistencia.excusa_causa}
                                  <p
                                    class="text-xs text-gray-500 dark:text-gray-500"
                                  >
                                    <span class="font-semibold">Causa:</span>
                                    {inasistencia.excusa_causa}
                                  </p>
                                {/if}
                              </div>
                            </div>
                          {:else}
                            <span
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/40 dark:to-rose-900/40 text-red-800 dark:text-red-300 border border-red-200/50 dark:border-red-800/30 shadow-sm"
                            >
                              <span class="material-symbols-rounded text-sm"
                                >cancel</span
                              >
                              No Excusada
                            </span>
                          {/if}
                        </td>
                      </tr>
                    {/each}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        {:else}
          <div
            class="flex flex-col items-center justify-center h-80 text-center p-8"
          >
            <div
              class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900/30 dark:to-green-900/30 rounded-full flex items-center justify-center mb-6 shadow-inner"
            >
              <span
                class="material-symbols-rounded text-6xl text-emerald-600 dark:text-emerald-400"
                >event_available</span
              >
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
              ¡Excelente asistencia!
            </h3>
            <p
              class="text-gray-600 dark:text-gray-400 leading-relaxed max-w-sm"
            >
              No se encontraron registros de inasistencias para este periodo.
              Sigue así.
            </p>
          </div>
        {/if}
      </div>

      <!-- Footer -->
      <div
        class="flex-none px-6 md:px-8 py-5 border-t border-gray-200 dark:border-gray-800 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-900/80 dark:to-gray-900/60 flex justify-end backdrop-blur-sm"
      >
        <button
          on:click={closeDialog}
          class="px-8 py-3 rounded-xl bg-gradient-to-r from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 font-bold hover:from-gray-200 hover:to-gray-100 dark:hover:from-gray-600 dark:hover:to-gray-700 hover:text-gray-900 dark:hover:text-white transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:focus:ring-gray-600 dark:focus:ring-offset-gray-900"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.8);
  }
</style>
