<script lang="ts">
  import { onMount } from "svelte";
  import { fetchInasistenciasDetallado } from "../../../../api";
  import type { Inasistencia, InasistenciasDetalladoPayload } from "../../../../types";
  import { theme } from "../../../../themeStore";
  import { fade, scale } from "svelte/transition";

  export let showDialog: boolean;
  export let estudianteId: string;
  export let nombres: string;
  export let asignatura: string;
  export let periodo: string;

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
      aria-label="Cerrar diálogo"
      title="Cerrar diálogo"
    ></div>

    <!-- Modal Container -->
    <div
      class="relative w-full max-w-4xl max-h-[90vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700 transform transition-all"
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
            Inasistencias Detalladas
          </h2>
          <div
            class="flex flex-wrap items-center gap-3 mt-2 text-sm text-gray-500 dark:text-gray-400"
          >
            {#if nombres}
              <div
                class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-md"
              >
                <span class="material-symbols-rounded text-sm text-gray-400"
                  >person</span
                >
                <span class="font-medium text-gray-700 dark:text-gray-300"
                  >{nombres}</span
                >
              </div>
            {/if}
            {#if asignatura}
              <div
                class="flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-md text-indigo-600 dark:text-indigo-400"
              >
                <span class="material-symbols-rounded text-sm">book</span>
                <span class="font-semibold">{asignatura}</span>
              </div>
            {/if}
            {#if periodo}
              <div
                class="flex items-center gap-1.5 bg-green-50 dark:bg-green-900/30 px-2.5 py-1 rounded-md text-green-600 dark:text-green-400"
              >
                <span class="material-symbols-rounded text-sm"
                  >calendar_today</span
                >
                <span class="font-semibold">Periodo {periodo}</span>
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

      <!-- Content -->
      <div
        class="flex-1 overflow-y-auto p-0 custom-scrollbar bg-gray-50 dark:bg-black/20"
      >
        {#if loading}
          <div class="flex flex-col items-center justify-center h-64 space-y-4">
            <div class="relative">
              <span
                class="material-symbols-rounded text-6xl text-indigo-500 animate-spin"
                >progress_activity</span
              >
            </div>
            <p
              class="text-sm font-medium text-gray-500 dark:text-gray-400 animate-pulse"
            >
              Cargando inasistencias...
            </p>
          </div>
        {:else if error}
          <div
            class="flex flex-col items-center justify-center h-64 text-center p-8"
          >
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-full mb-4">
              <span class="material-symbols-rounded text-4xl text-red-500"
                >error</span
              >
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Error al cargar
            </h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
              {error}
            </p>
          </div>
        {:else if inasistencias.length > 0}
          <div class="p-6">
            <div
              class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-900"
            >
              <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
              >
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >Fecha</th
                    >
                    <th
                      scope="col"
                      class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >Horas</th
                    >
                    <th
                      scope="col"
                      class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >Hora Clase</th
                    >
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >Detalle</th
                    >
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                      >Excusa</th
                    >
                  </tr>
                </thead>
                <tbody
                  class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900"
                >
                  {#each inasistencias as inasistencia (inasistencia.ind)}
                    <tr
                      class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150"
                    >
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <span
                            class="text-sm font-medium text-gray-900 dark:text-white"
                            >{inasistencia.fecha}</span
                          >
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span
                          class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200"
                        >
                          {inasistencia.horas}
                        </span>
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400"
                      >
                        {inasistencia.hora_clase}
                      </td>
                      <td class="px-6 py-4">
                        {#if inasistencia.detalle}
                          <div class="flex items-start gap-2">
                            <span
                              class="material-symbols-rounded text-lg text-blue-500 mt-0.5"
                              >info</span
                            >
                            <span
                              class="text-sm text-gray-600 dark:text-gray-300"
                              >{inasistencia.detalle}</span
                            >
                          </div>
                        {:else}
                          <span class="text-sm text-gray-400 italic"
                            >Sin detalle</span
                          >
                        {/if}
                      </td>
                      <td class="px-6 py-4">
                        {#if inasistencia.excusa_motivo}
                          <div class="flex flex-col gap-1">
                            <span
                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 w-fit"
                            >
                              <span class="material-symbols-rounded text-sm"
                                >check_circle</span
                              >
                              Excusada
                            </span>
                            <span
                              class="text-xs text-gray-500 dark:text-gray-400 ml-1"
                              >{inasistencia.excusa_motivo}</span
                            >
                            {#if inasistencia.excusa_causa}
                              <span class="text-xs text-gray-400 ml-1"
                                >Causa: {inasistencia.excusa_causa}</span
                              >
                            {/if}
                          </div>
                        {:else}
                          <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400"
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
        {:else}
          <div
            class="flex flex-col items-center justify-center h-64 text-center p-8 opacity-60"
          >
            <div
              class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4"
            >
              <span class="material-symbols-rounded text-5xl text-gray-400"
                >event_available</span
              >
            </div>
            <p class="text-lg font-medium text-gray-900 dark:text-white">
              Sin inasistencias
            </p>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
              No se encontraron registros para este periodo.
            </p>
          </div>
        {/if}
      </div>

      <!-- Footer -->
      <div
        class="flex-none p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 flex justify-end"
      >
        <button
          on:click={closeDialog}
          class="px-6 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-all duration-200 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700"
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
