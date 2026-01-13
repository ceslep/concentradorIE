<!-- 
CONCENTRADORASIGNATURASTABLE.SVELTE

DESCRIPCIÓN:
Versión premium de la tabla de calificaciones organizada por Asignaturas. Incluye efectos visuales avanzados (glow), animaciones de entrada y acceso a estadísticas por docente.

USO:
<ConcentradorAsignaturasTable {handleValoracionClick} onHeaderClick={handleHeaderClick} /> en App.svelte.

DEPENDENCIAS:
- Store: theme (themeStore.ts), storeConcentrador (parsed, loading, payload, showPeriodos, selectedPeriodos, currentOrden, selectedAsignatura).
- Tipos: EstudianteRow, ConcentradorParsed, AsignaturaOrdenItem (types.ts).

PROPS/EMIT:
- Prop: `handleValoracionClick` → function → Callback para abrir el detalle de notas.
- Prop: `onHeaderClick` → function → Callback disparado al hacer clic en el nombre de una asignatura para ver estadísticas.

RELACIONES:
- Llamado por: App.svelte.
- Llama a: GradesTableDialog (vía App.svelte).

NOTAS DE DESARROLLO:
- Implementa 'cyber-border' y efectos de brillo ('nota-glow').
- Animaciones de entrada escalonadas para las filas de estudiantes (`idx * 20ms`).

ESTILOS:
- 'futuristic-scrollbar' y 'avatar-glow' para una estética moderna de alto nivel.
- Altas densidades de información gestionadas con sticky headers/columns.
-->

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

  let { handleValoracionClick, onHeaderClick } = $props<{
    handleValoracionClick: (
      est: EstudianteRow,
      asignaturaAbrev: string,
      periodo: string,
      valoracion: string,
    ) => Promise<void>;
    onHeaderClick: (docenteId: string) => void;
  }>();

  let search = $state("");

  let dark = $derived($theme === "dark");

  // Modern Color Palette & Styles - Enhanced
  let bgSurface = $derived(dark ? "bg-gray-900" : "bg-white");
  let bgHeader = $derived(
    dark
      ? "bg-gradient-to-r from-gray-900/98 via-gray-800/98 to-gray-900/98 backdrop-blur-xl"
      : "bg-gradient-to-r from-gray-50/98 via-white/98 to-gray-50/98 backdrop-blur-xl",
  );
  let textPrimary = $derived(dark ? "text-white" : "text-gray-900");
  let textSecondary = $derived(dark ? "text-gray-400" : "text-gray-500");
  let borderDivide = $derived(dark ? "divide-gray-700/50" : "divide-gray-100");
  let borderTable = $derived(dark ? "border-gray-700/50" : "border-gray-200");
  let hoverRow = $derived(dark ? "hover:bg-gray-800/60" : "hover:bg-gray-50");
  let stickyColBg = $derived(dark ? "bg-gray-900" : "bg-white");

  /**
   * Filtra la lista de estudiantes basándose en el término de búsqueda interactivo.
   */
  let filteredEstudiantes = $derived(
    (() => {
      if (!$parsed || $concentradorType !== "asignaturas")
        return [] as EstudianteRow[];
      const p = $parsed as ConcentradorParsed;
      return !search.trim()
        ? p.estudiantes
        : p.estudiantes.filter((est) =>
            est.nombres.toLowerCase().includes(search.toLowerCase()),
          );
    })(),
  );

  /**
   * Busca el valor de la calificación para una asignatura y período específicos.
   * @returns La calificación formateada a un decimal o cadena vacía.
   */
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
        return "text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-700/50 shadow-sm dark:shadow-blue-500/10";
      case "DOS":
        return "text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/40 border-green-300 dark:border-green-700/50 shadow-sm dark:shadow-green-500/10";
      case "TRES":
        return "text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-950/40 border-yellow-300 dark:border-yellow-700/50 shadow-sm dark:shadow-yellow-500/10";
      case "CUATRO":
        return "text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-700/50 shadow-sm dark:shadow-purple-500/10";
      case "DEF":
        return "text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/50 border-indigo-300 dark:border-indigo-600/60 font-bold shadow-sm dark:shadow-indigo-500/20 period-def-glow";
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

  /**
   * Determina la clase de color y brillo (glow) según el valor de la nota.
   * @param valor - El valor numérico de la nota en formato string.
   */
  function colorNota(valor: string): string {
    if (!valor)
      return "bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 border border-gray-200 dark:border-gray-700";
    const v = parseFloat(valor);
    if (isNaN(v))
      return "bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 border border-gray-200 dark:border-gray-700";

    // Paleta premium: Verde (>=4), Ámbar (>=3), Rojo (<3)
    if (v >= 4)
      return "bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 dark:shadow-emerald-500/20 border border-emerald-400/50 nota-glow-green";
    if (v >= 3)
      return "bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-lg shadow-amber-500/30 dark:shadow-amber-500/20 border border-amber-300/50 nota-glow-yellow";
    return "bg-gradient-to-br from-rose-500 to-rose-600 text-white shadow-lg shadow-rose-500/30 dark:shadow-rose-500/20 border border-rose-400/50 nota-glow-red";
  }
</script>

{#if $loading}
  <div
    class="rounded-2xl shadow-2xl overflow-hidden flex-grow flex flex-col {bgSurface} border {borderTable} font-sans min-h-[400px] relative"
    style="font-family: 'Inter', sans-serif;"
    in:fade={{ duration: 200 }}
  >
    <!-- Futuristic background pattern -->
    <div class="absolute inset-0 opacity-5 dark:opacity-10 pointer-events-none">
      <div
        class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-pink-500/20 animate-pulse"
      ></div>
    </div>

    <div
      class="flex flex-col items-center justify-center h-full py-20 gap-8 relative z-10"
    >
      <!-- Enhanced multi-layer loading spinner -->
      <div class="relative">
        <div
          class="w-20 h-20 border-4 border-indigo-200 dark:border-indigo-900/50 border-t-indigo-600 dark:border-t-indigo-400 rounded-full animate-spin shadow-lg shadow-indigo-500/20"
        ></div>
        <div
          class="absolute inset-0 w-20 h-20 border-4 border-transparent border-t-purple-500 dark:border-t-purple-400 rounded-full animate-spin opacity-60"
          style="animation-duration: 1.5s; animation-direction: reverse;"
        ></div>
        <div
          class="absolute inset-2 w-16 h-16 border-3 border-transparent border-t-pink-500 dark:border-t-pink-400 rounded-full animate-spin opacity-40"
          style="animation-duration: 2s;"
        ></div>
        <!-- Center pulse -->
        <div class="absolute inset-0 flex items-center justify-center">
          <div
            class="w-3 h-3 bg-indigo-500 dark:bg-indigo-400 rounded-full animate-ping"
          ></div>
          <div
            class="absolute w-2 h-2 bg-indigo-600 dark:bg-indigo-300 rounded-full"
          ></div>
        </div>
      </div>

      <div class="text-center space-y-2">
        <p class="text-lg font-bold {textPrimary} mb-1 tracking-wide">
          Cargando datos...
        </p>
        <p class="text-sm {textSecondary}">Por favor espera un momento</p>
        <div class="flex gap-1 justify-center mt-3">
          <div
            class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"
            style="animation-delay: 0ms;"
          ></div>
          <div
            class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"
            style="animation-delay: 150ms;"
          ></div>
          <div
            class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"
            style="animation-delay: 300ms;"
          ></div>
        </div>
      </div>
    </div>
  </div>
{:else if $parsed && $concentradorType === "asignaturas"}
  <div
    class="rounded-2xl shadow-2xl overflow-hidden flex-grow flex flex-col {bgSurface} border-2 {borderTable} font-sans cyber-border relative"
    style="font-family: 'Inter', sans-serif;"
    in:fade={{ duration: 300 }}
  >
    <!-- Subtle gradient overlay at top -->
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"
    ></div>

    <div
      class="overflow-x-auto overflow-y-auto max-h-[70vh] futuristic-scrollbar"
    >
      <table class="min-w-full text-sm text-left border-collapse">
        <thead
          class="text-xs uppercase tracking-wider sticky top-0 z-20 {bgHeader} shadow-lg border-b-2 border-indigo-500/20 dark:border-indigo-500/30"
        >
          <tr>
            <th
              scope="col"
              class="p-3 text-left sticky left-0 z-30 w-max {bgHeader} border-b-2 border-r-2 border-indigo-500/20 dark:border-indigo-500/30 min-w-[220px] shadow-lg"
              rowspan={$showPeriodos ? 2 : 1}
            >
              <div class="flex flex-col gap-2">
                <span
                  class="flex items-center gap-2 text-[11px] font-extrabold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent"
                >
                  <span
                    class="material-symbols-rounded text-lg text-indigo-600 dark:text-indigo-400 drop-shadow-sm"
                    >school</span
                  >
                  ESTUDIANTE
                </span>
                <div class="relative group">
                  <span
                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-indigo-500 transition-all duration-300 z-10"
                  >
                    <span
                      class="material-symbols-rounded text-base drop-shadow-sm"
                      >search</span
                    >
                  </span>
                  <input
                    type="text"
                    aria-label="Buscar estudiante"
                    placeholder="Buscar estudiante..."
                    bind:value={search}
                    class="w-full pl-9 pr-3 py-2 rounded-xl text-xs border-2 border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/80 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 shadow-sm hover:shadow-md backdrop-blur-sm"
                  />
                </div>
              </div>
            </th>
            {#each $currentOrden as asignaturaItem (asignaturaItem.abreviatura)}
              {#if asignaturaItem}
                <th
                  scope="col"
                  class="p-1 whitespace-nowrap text-center border-b-2 border-indigo-500/20 dark:border-indigo-500/30 min-w-[80px]"
                >
                  <button
                    class="w-full h-full px-3 py-2 rounded-lg text-[10px] font-bold transition-all duration-300 flex flex-col items-center justify-center header-btn
                           {dark
                      ? 'text-gray-300 hover:bg-gradient-to-br hover:from-gray-800/80 hover:to-gray-700/80 hover:text-white hover:shadow-lg hover:shadow-indigo-500/20'
                      : 'text-gray-700 hover:bg-gradient-to-br hover:from-gray-100 hover:to-gray-50 hover:text-indigo-700 hover:shadow-lg hover:shadow-indigo-500/10'}
                           active:scale-95"
                    onclick={() => {
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
                  <th
                    class="p-1.5 border-b-2 border-indigo-500/20 dark:border-indigo-500/30"
                  >
                    <div class="flex justify-center gap-1">
                      {#each $selectedPeriodos.filter((p: string) => p !== "DEF") as per}
                        <span
                          class="w-6 h-6 flex items-center justify-center rounded-full text-[10px] font-bold border-2 transition-all duration-200 hover:scale-110 {getPeriodColorClass(
                            per,
                          )}"
                          title="Período {getShortPeriodName(per)}"
                        >
                          {getShortPeriodName(per)}
                        </span>
                      {/each}
                      <span
                        class="w-6 h-6 flex items-center justify-center rounded-full text-[10px] font-bold border-2 transition-all duration-200 hover:scale-110 animate-pulse {getPeriodColorClass(
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
          {#each filteredEstudiantes as est, idx (est.nombres)}
            <tr
              class="group transition-all duration-200 {hoverRow} hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-indigo-500/5 student-row"
              style="animation-delay: {idx * 20}ms;"
            >
              <td
                class="px-4 py-3 font-medium sticky left-0 z-10 whitespace-nowrap {textPrimary} {stickyColBg} group-hover:bg-gradient-to-r group-hover:from-gray-100 dark:group-hover:from-gray-800/80 dark:group-hover:to-gray-800/60 border-r-2 border-indigo-500/10 dark:border-indigo-500/20 shadow-[4px_0_8px_-2px_rgba(0,0,0,0.1)] dark:shadow-[4px_0_8px_-2px_rgba(0,0,0,0.3)] group-hover:shadow-[4px_0_12px_-2px_rgba(99,102,241,0.15)] transition-all duration-200"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-indigo-500/30 group-hover:shadow-xl group-hover:shadow-indigo-500/40 transition-all duration-300 group-hover:scale-110 avatar-glow"
                  >
                    {est.nombres.charAt(0)}
                  </div>
                  <span class="text-sm font-semibold">{est.nombres}</span>
                </div>
              </td>
              {#each $currentOrden as asignaturaItem (asignaturaItem.abreviatura)}
                {#if asignaturaItem}
                  <td
                    class="p-2 text-center align-middle border-r border-dashed border-gray-200/50 dark:border-gray-700/50 last:border-r-0"
                  >
                    {#if !$showPeriodos}
                      {#if $payload && $payload.periodo}
                        <button
                          type="button"
                          class="w-14 h-10 rounded-xl font-bold text-sm transition-all duration-300 hover:scale-115 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:scale-95 {colorNota(
                            valorPeriodo(
                              est,
                              asignaturaItem.abreviatura,
                              $payload.periodo,
                            ),
                          )}"
                          onclick={() =>
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
                      <div class="flex justify-center gap-1.5">
                        {#each $selectedPeriodos.filter((p: string) => p !== "DEF") as per}
                          <button
                            type="button"
                            class="w-7 h-8 rounded-lg text-[11px] font-bold flex items-center justify-center transition-all duration-300 hover:scale-115 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-indigo-500 active:scale-95 {colorNota(
                              valorPeriodo(
                                est,
                                asignaturaItem.abreviatura,
                                per,
                              ),
                            )}"
                            onclick={() =>
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
                          class="w-7 h-8 rounded-lg text-[11px] font-extrabold flex items-center justify-center transition-all duration-300 hover:scale-115 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-indigo-500 ring-2 ring-inset ring-black/10 dark:ring-white/20 active:scale-95 {colorNota(
                            valorPeriodo(
                              est,
                              asignaturaItem.abreviatura,
                              'DEF',
                            ),
                          )}"
                          onclick={() =>
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
  /* Enhanced futuristic scrollbar */
  .futuristic-scrollbar::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }

  .futuristic-scrollbar::-webkit-scrollbar-track {
    background: linear-gradient(
      to bottom,
      transparent,
      rgba(99, 102, 241, 0.05),
      transparent
    );
    border-radius: 10px;
  }

  .futuristic-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(
      135deg,
      rgba(99, 102, 241, 0.4),
      rgba(168, 85, 247, 0.4)
    );
    border-radius: 10px;
    border: 2px solid transparent;
    background-clip: padding-box;
    transition: all 0.3s ease;
  }

  .futuristic-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(
      135deg,
      rgba(99, 102, 241, 0.6),
      rgba(168, 85, 247, 0.6)
    );
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
  }

  /* Cyber border glow effect */
  .cyber-border {
    position: relative;
  }

  .cyber-border::before {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: 1rem;
    padding: 1px;
    background: linear-gradient(
      135deg,
      rgba(99, 102, 241, 0.3),
      rgba(168, 85, 247, 0.2),
      rgba(236, 72, 153, 0.2),
      rgba(99, 102, 241, 0.3)
    );
    -webkit-mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.5;
    pointer-events: none;
  }

  /* Glow effects for nota buttons */
  .nota-glow-green:hover {
    box-shadow:
      0 0 20px rgba(16, 185, 129, 0.5),
      0 0 40px rgba(16, 185, 129, 0.2);
  }

  .nota-glow-yellow:hover {
    box-shadow:
      0 0 20px rgba(251, 191, 36, 0.5),
      0 0 40px rgba(251, 191, 36, 0.2);
  }

  .nota-glow-red:hover {
    box-shadow:
      0 0 20px rgba(244, 63, 94, 0.5),
      0 0 40px rgba(244, 63, 94, 0.2);
  }

  /* Avatar glow animation */
  .avatar-glow {
    animation: avatar-pulse 3s ease-in-out infinite;
  }

  @keyframes avatar-pulse {
    0%,
    100% {
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    50% {
      box-shadow:
        0 4px 20px rgba(168, 85, 247, 0.5),
        0 0 30px rgba(168, 85, 247, 0.3);
    }
  }

  /* Period DEF badge glow */
  .period-def-glow {
    animation: def-pulse 2s ease-in-out infinite;
  }

  @keyframes def-pulse {
    0%,
    100% {
      box-shadow: 0 0 0 rgba(99, 102, 241, 0);
    }
    50% {
      box-shadow:
        0 0 12px rgba(99, 102, 241, 0.5),
        0 0 20px rgba(99, 102, 241, 0.2);
    }
  }

  /* Header button hover effect */
  .header-btn {
    position: relative;
    overflow: hidden;
  }

  .header-btn::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: radial-gradient(
      circle,
      rgba(99, 102, 241, 0.3) 0%,
      transparent 70%
    );
    transform: translate(-50%, -50%);
    transition:
      width 0.6s ease,
      height 0.6s ease;
  }

  .header-btn:hover::before {
    width: 300px;
    height: 300px;
  }

  /* Student row entrance animation */
  @keyframes slideInRow {
    from {
      opacity: 0;
      transform: translateX(-10px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .student-row {
    animation: slideInRow 0.4s ease-out backwards;
  }

  /* Hover scale enhancement */
  .hover\:scale-115:hover {
    transform: scale(1.15);
  }
</style>
