<script lang="ts">
  import type { ConcentradorAreasParsed, EstudianteRowArea } from "./types";
  import { theme } from "./themeStore";
  import {
    parsed,
    loading,
    concentradorType,
    showPeriodos,
    selectedPeriodos,
    currentOrden,
  } from "./storeConcentrador";
  import { fade } from "svelte/transition";

  export let handleValoracionClick: (
    est: EstudianteRowArea,
    itemAbrev: string,
    periodo: string,
    valoracion: string,
  ) => Promise<void>;
  let handleShowInasistencias: (
    estudianteId: string,
    nombres: string,
    asignatura: string,
    periodo: string,
  ) => void;

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
    if (!$parsed || $concentradorType !== "areas")
      return [] as EstudianteRowArea[];
    const p = $parsed as ConcentradorAreasParsed;
    const list = p.estudiantes;

    if (!search.trim()) return list;
    const q = search.toLowerCase();
    return list.filter((est) => est.nombres.toLowerCase().includes(q));
  })();

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

  function getItemName(abreviatura: string): string {
    if (!$parsed || $concentradorType !== "areas") return abreviatura;
    const p = $parsed as ConcentradorAreasParsed;
    if (!p.areas) return abreviatura;
    const item = p.areas.find((a) => a.abreviatura === abreviatura);
    return item?.nombre ?? abreviatura;
  }

  function valorPeriodo(
    est: EstudianteRowArea,
    areaAbrev: string,
    periodo: string,
  ): string {
    const a = est.areas?.find((a) => a.area === areaAbrev);
    if (!a) return "";
    const p = a.periodos?.find((p) => p.periodo === periodo);
    const val = p?.valoracion;
    return val != null ? val.toFixed(1) : "";
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

{#if $parsed && !$loading && $concentradorType === "areas"}
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
              class="p-4 text-left sticky left-0 z-30 w-max {bgHeader} border-b {borderTable} min-w-[250px]"
              rowspan={$showPeriodos ? 2 : 1}
            >
              <div class="flex flex-col gap-2">
                <span
                  class="flex items-center gap-1.5 text-xs font-bold text-indigo-500 dark:text-indigo-400"
                >
                  <span class="material-symbols-rounded text-lg">school</span>
                  ESTUDIANTE
                </span>
                <div class="relative group">
                  <span
                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-indigo-500 transition-colors"
                  >
                    <span class="material-symbols-rounded text-lg">search</span>
                  </span>
                  <input
                    type="text"
                    aria-label="Buscar estudiante"
                    placeholder="Buscar..."
                    bind:value={search}
                    class="w-full pl-9 pr-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                  />
                </div>
              </div>
            </th>
            {#each $currentOrden as itemAbrev}
              {#if itemAbrev}
                <th
                  scope="col"
                  class="p-1 whitespace-nowrap text-center border-b {borderTable} min-w-[100px]"
                >
                  <div
                    class="w-full h-full px-3 py-2 rounded-lg text-xs font-bold flex flex-col items-center justify-center gap-1 {textSecondary}"
                  >
                    <span
                      class="line-clamp-1"
                      title={getItemName(itemAbrev.abreviatura)}
                      >{getItemName(itemAbrev.abreviatura)}</span
                    >
                    <span
                      class="text-[10px] font-normal opacity-60 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full"
                      >{itemAbrev.abreviatura}</span
                    >
                  </div>
                </th>
              {/if}
            {/each}
          </tr>

          {#if $showPeriodos}
            <tr class={bgHeader}>
              {#each $currentOrden as itemAbrev}
                {#if itemAbrev}
                  <th class="p-2 border-b {borderTable}">
                    <div class="flex justify-center gap-1">
                      {#each $selectedPeriodos.filter((p: string) => p !== "DEF") as per}
                        <span
                          class="w-6 h-6 flex items-center justify-center rounded-full text-[10px] font-bold border {getPeriodColorClass(
                            per,
                          )}"
                          title="Período {getShortPeriodName(per)}"
                        >
                          {getShortPeriodName(per)}
                        </span>
                      {/each}
                      <span
                        class="w-6 h-6 flex items-center justify-center rounded-full text-[10px] font-bold border {getPeriodColorClass(
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
              {#each $currentOrden as itemAbrev}
                {#if itemAbrev}
                  <td
                    class="p-2 text-center align-middle border-r border-dashed border-gray-100 dark:border-gray-800 last:border-r-0"
                  >
                    {#if !$showPeriodos}
                      <button
                        type="button"
                        class="w-12 h-9 rounded-lg font-bold text-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 {colorNota(
                          valorPeriodo(est, itemAbrev.abreviatura, 'DEF'),
                        )}"
                        on:click={() =>
                          handleValoracionClick(
                            est,
                            itemAbrev.abreviatura,
                            "DEF",
                            valorPeriodo(est, itemAbrev.abreviatura, "DEF"),
                          )}
                      >
                        {valorPeriodo(est, itemAbrev.abreviatura, "DEF") || "-"}
                      </button>
                    {:else}
                      <div class="flex justify-center gap-1">
                        {#each $selectedPeriodos.filter((p: string) => p !== "DEF") as per}
                          <button
                            type="button"
                            class="w-6 h-7 rounded-md text-[10px] font-bold flex items-center justify-center transition-transform hover:scale-110 focus:outline-none {colorNota(
                              valorPeriodo(est, itemAbrev.abreviatura, per),
                            )}"
                            on:click={() =>
                              handleValoracionClick(
                                est,
                                itemAbrev.abreviatura,
                                per,
                                valorPeriodo(est, itemAbrev.abreviatura, per),
                              )}
                          >
                            {valorPeriodo(est, itemAbrev.abreviatura, per) ||
                              "-"}
                          </button>
                        {/each}
                        <button
                          type="button"
                          class="w-6 h-7 rounded-md text-[10px] font-bold flex items-center justify-center transition-transform hover:scale-110 focus:outline-none ring-1 ring-inset ring-black/5 dark:ring-white/10 {colorNota(
                            valorPeriodo(est, itemAbrev.abreviatura, 'DEF'),
                          )}"
                          on:click={() =>
                            handleValoracionClick(
                              est,
                              itemAbrev.abreviatura,
                              "DEF",
                              valorPeriodo(est, itemAbrev.abreviatura, "DEF"),
                            )}
                        >
                          {valorPeriodo(est, itemAbrev.abreviatura, "DEF") ||
                            "-"}
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
