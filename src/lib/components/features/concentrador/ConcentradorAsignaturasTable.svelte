<script lang="ts">
  import type {
    EstudianteRow,
    ConcentradorParsed,
    AsignaturaOrdenItem,
  } from "../../../types";
  import { theme } from "../../../themeStore";
  import {
    parsed,
    loading,
    payload,
    showPeriodos,
    selectedPeriodos,
    currentOrden,
    concentradorType,
    selectedAsignatura,
  } from "../../../storeConcentrador";
  import { fade } from "svelte/transition";

  export let handleValoracionClick: (
    est: EstudianteRow,
    asignaturaAbrev: string,
    periodo: string,
    valoracion: string,
  ) => Promise<void>;
  export let onHeaderClick: (docenteId: string) => void;

  let search = "";

  $: currentTheme = $theme;
  $: dark = currentTheme === "dark";

  // Modern Color Palette & Styles
  $: bgSurface = dark ? "bg-gray-800" : "bg-white";
  $: bgHeader = dark
    ? "bg-gray-900/95 backdrop-blur-sm"
    : "bg-gray-50/95 backdrop-blur-sm";
  $: textPrimary = dark ? "text-white" : "text-gray-900";
  $: textSecondary = dark ? "text-gray-400" : "text-gray-500";
  $: borderDivide = dark ? "divide-gray-700" : "divide-gray-100";
  $: borderTable = dark ? "border-gray-700" : "border-gray-200";
  $: hoverRow = dark ? "hover:bg-gray-700/50" : "hover:bg-gray-50";
  $: stickyColBg = dark ? "bg-gray-800" : "bg-white";

  $: filteredEstudiantes = (() => {
    if (!$parsed || $concentradorType !== "asignaturas")
      return [] as EstudianteRow[];
    const p = $parsed as ConcentradorParsed;
    return !search.trim()
      ? p.estudiantes
      : p.estudiantes.filter((est) =>
          est.nombres.toLowerCase().includes(search.toLowerCase()),
        );
  })();

  function valorPeriodo(
    est: EstudianteRow,
    asignatura: string,
    periodo: string,
  ): string {
    const a = est.asignaturas?.find((a) => a.asignatura === asignatura);
    if (!a) return "";
    const p = a.periodos?.find((p) => p.periodo === periodo);
    const val = p?.valoracion;
    return val != null ? val.toFixed(1) : "";
  }

  function getPeriodColorClass(periodo: string): string {
    switch (periodo) {
      case "UNO":
        return "text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800";
      case "DOS":
        return "text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800";
      case "TRES":
        return "text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800";
      case "CUATRO":
        return "text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800";
      case "DEF":
        return "text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800 font-bold";
      default:
        return "text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700";
    }
  }

  function getShortPeriodName(periodo: string): string {
    switch (periodo) {
      case "UNO":
        return "1";
      case "DOS":
        return "2";
      case "TRES":
        return "3";
      case "CUATRO":
        return "4";
      case "DEF":
        return "D";
      default:
        return periodo;
    }
  }

  function getItemName(item: string | AsignaturaOrdenItem): string {
    const abreviatura = typeof item === "string" ? item : item.abreviatura;
    if (!$parsed || $concentradorType !== "asignaturas") return abreviatura;
    const p = $parsed as ConcentradorParsed;
    if (!p.asignaturas) return abreviatura;
    return (
      p.asignaturas.find((a) => a.abreviatura === abreviatura)?.nombre ??
      abreviatura
    );
  }

  function colorNota(valor: string): string {
    if (!valor)
      return "bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600";
    const v = parseFloat(valor);
    if (isNaN(v))
      return "bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600";

    // Modern gradient-like solid colors
    if (v >= 4)
      return "bg-emerald-500 text-white shadow-sm shadow-emerald-200 dark:shadow-none";
    if (v >= 3)
      return "bg-amber-400 text-white shadow-sm shadow-amber-200 dark:shadow-none";
    return "bg-rose-500 text-white shadow-sm shadow-rose-200 dark:shadow-none";
  }
</script>

{#if $loading}
  <div
    class="rounded-2xl shadow-xl overflow-hidden flex-grow flex flex-col {bgSurface} border {borderTable} font-sans min-h-[400px]"
    style="font-family: 'Inter', sans-serif;"
    in:fade={{ duration: 200 }}
  >
    <div class="flex flex-col items-center justify-center h-full py-20 gap-6">
      <div class="relative">
        <div
          class="w-16 h-16 border-4 border-indigo-200 dark:border-indigo-900 border-t-indigo-600 dark:border-t-indigo-400 rounded-full animate-spin"
        ></div>
        <div
          class="absolute inset-0 w-16 h-16 border-4 border-transparent border-t-indigo-400 dark:border-t-indigo-300 rounded-full animate-spin"
          style="animation-duration: 1.5s; animation-direction: reverse;"
        ></div>
      </div>
      <div class="text-center">
        <p class="text-lg font-semibold {textPrimary} mb-1">
          Cargando datos...
        </p>
        <p class="text-sm {textSecondary}">Por favor espera un momento</p>
      </div>
    </div>
  </div>
{:else if $parsed && $concentradorType === "asignaturas"}
  <div
    class="rounded-2xl shadow-xl overflow-hidden flex-grow flex flex-col {bgSurface} border {borderTable} font-sans"
    style="font-family: 'Inter', sans-serif;"
    in:fade={{ duration: 300 }}
  >
    <div class="overflow-x-auto overflow-y-auto max-h-[70vh] custom-scrollbar">
      <table class="min-w-full text-sm text-left border-collapse">
        <thead
          class="text-xs uppercase tracking-wider sticky top-0 z-20 {bgHeader} shadow-sm"
        >
          <tr>
            <th
              scope="col"
              class="p-2 text-left sticky left-0 z-30 w-max {bgHeader} border-b {borderTable} min-w-[220px]"
              rowspan={$showPeriodos ? 2 : 1}
            >
              <div class="flex flex-col gap-1">
                <span
                  class="flex items-center gap-1 text-[10px] font-bold text-indigo-500 dark:text-indigo-400"
                >
                  <span class="material-symbols-rounded text-base">school</span>
                  ESTUDIANTE
                </span>
                <div class="relative group">
                  <span
                    class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"
                  >
                    <span class="material-symbols-rounded text-base"
                      >search</span
                    >
                  </span>
                  <input
                    type="text"
                    aria-label="Buscar estudiante"
                    placeholder="Buscar..."
                    bind:value={search}
                    class="w-full pl-7 pr-2 py-1 rounded-lg text-xs border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                  />
                </div>
              </div>
            </th>
            {#each $currentOrden as asignaturaItem (asignaturaItem.abreviatura)}
              {#if asignaturaItem}
                <th
                  scope="col"
                  class="p-0.5 whitespace-nowrap text-center border-b {borderTable} min-w-[80px]"
                >
                  <button
                    class="w-full h-full px-2 py-1 rounded-md text-[10px] font-bold transition-all duration-200 flex flex-col items-center justify-center
                           {dark
                      ? 'text-gray-300 hover:bg-gray-800 hover:text-white'
                      : 'text-gray-600 hover:bg-gray-100 hover:text-indigo-600'}"
                    on:click={() => {
                      selectedAsignatura.set(asignaturaItem.abreviatura);
                      onHeaderClick(asignaturaItem.docenteId);
                    }}
                    title={getItemName(asignaturaItem.abreviatura)}
                  >
                    <span class="line-clamp-1"
                      >{getItemName(asignaturaItem.abreviatura)}</span
                    >
                  </button>
                </th>
              {/if}
            {/each}
          </tr>

          {#if $showPeriodos}
            <tr class={bgHeader}>
              {#each $currentOrden as asignaturaItem (asignaturaItem.abreviatura)}
                {#if asignaturaItem}
                  <th class="p-1 border-b {borderTable}">
                    <div class="flex justify-center gap-0.5">
                      {#each $selectedPeriodos.filter((p: string) => p !== "DEF") as per}
                        <span
                          class="w-5 h-5 flex items-center justify-center rounded-full text-[9px] font-bold border {getPeriodColorClass(
                            per,
                          )}"
                          title="Período {getShortPeriodName(per)}"
                        >
                          {getShortPeriodName(per)}
                        </span>
                      {/each}
                      <span
                        class="w-5 h-5 flex items-center justify-center rounded-full text-[9px] font-bold border {getPeriodColorClass(
                          'DEF',
                        )}"
                        title="Definitiva"
                      >
                        D
                      </span>
                    </div>
                  </th>
                {/if}
              {/each}
            </tr>
          {/if}
        </thead>

        <tbody class="divide-y {borderDivide}">
          {#each filteredEstudiantes as est (est.nombres)}
            <tr class="group transition-colors duration-150 {hoverRow}">
              <td
                class="px-4 py-3 font-medium sticky left-0 z-10 whitespace-nowrap {textPrimary} {stickyColBg} group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50 border-r {borderTable} shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold shadow-sm"
                  >
                    {est.nombres.charAt(0)}
                  </div>
                  <span class="text-sm">{est.nombres}</span>
                </div>
              </td>
              {#each $currentOrden as asignaturaItem (asignaturaItem.abreviatura)}
                {#if asignaturaItem}
                  <td
                    class="p-2 text-center align-middle border-r border-dashed border-gray-100 dark:border-gray-800 last:border-r-0"
                  >
                    {#if !$showPeriodos}
                      {#if $payload && $payload.periodo}
                        <button
                          type="button"
                          class="w-12 h-9 rounded-lg font-bold text-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 {colorNota(
                            valorPeriodo(
                              est,
                              asignaturaItem.abreviatura,
                              $payload.periodo,
                            ),
                          )}"
                          on:click={() =>
                            handleValoracionClick(
                              est,
                              asignaturaItem.abreviatura,
                              $payload.periodo,
                              valorPeriodo(
                                est,
                                asignaturaItem.abreviatura,
                                $payload.periodo,
                              ),
                            )}
                        >
                          {valorPeriodo(
                            est,
                            asignaturaItem.abreviatura,
                            $payload.periodo,
                          ) || "-"}
                        </button>
                      {/if}
                    {:else}
                      <div class="flex justify-center gap-1">
                        {#each $selectedPeriodos.filter((p: string) => p !== "DEF") as per}
                          <button
                            type="button"
                            class="w-6 h-7 rounded-md text-[10px] font-bold flex items-center justify-center transition-transform hover:scale-110 focus:outline-none {colorNota(
                              valorPeriodo(
                                est,
                                asignaturaItem.abreviatura,
                                per,
                              ),
                            )}"
                            on:click={() =>
                              handleValoracionClick(
                                est,
                                asignaturaItem.abreviatura,
                                per,
                                valorPeriodo(
                                  est,
                                  asignaturaItem.abreviatura,
                                  per,
                                ),
                              )}
                          >
                            {valorPeriodo(
                              est,
                              asignaturaItem.abreviatura,
                              per,
                            ) || "-"}
                          </button>
                        {/each}
                        <button
                          type="button"
                          class="w-6 h-7 rounded-md text-[10px] font-bold flex items-center justify-center transition-transform hover:scale-110 focus:outline-none ring-1 ring-inset ring-black/5 dark:ring-white/10 {colorNota(
                            valorPeriodo(
                              est,
                              asignaturaItem.abreviatura,
                              'DEF',
                            ),
                          )}"
                          on:click={() =>
                            handleValoracionClick(
                              est,
                              asignaturaItem.abreviatura,
                              "DEF",
                              valorPeriodo(
                                est,
                                asignaturaItem.abreviatura,
                                "DEF",
                              ),
                            )}
                        >
                          {valorPeriodo(
                            est,
                            asignaturaItem.abreviatura,
                            "DEF",
                          ) || "-"}
                        </button>
                      </div>
                    {/if}
                  </td>
                {/if}
              {/each}
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
{/if}

<style>
  .custom-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.3);
    border-radius: 4px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.5);
  }
</style>
