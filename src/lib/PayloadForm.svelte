<script lang="ts">
  import {
    payload,
    showPeriodos,
    selectedPeriodos,
    loadConcentradorData,
    toggleShowPeriodos,
  } from "./storeConcentrador";
  import type { Sede } from "./api";
  import { fetchAsignaciones, fetchPeriodos, fetchYears } from "./api";
  import type { Periodo, Year } from "./types";
  import { onMount } from "svelte";
  import { theme } from "./themeStore";
  import { slide } from "svelte/transition";

  export let disabled: boolean = false;

  const STORAGE_KEY = "concentradorIE_lastPayload";
  const allPeriods = ["UNO", "DOS", "TRES", "CUATRO", "DEF"];
  const periodOrder = { UNO: 1, DOS: 2, TRES: 3, CUATRO: 4, DEF: 5 };

  let sedes: Sede[] = [];
  let periods: Periodo[] = [];
  let years: Year[] = [];
  let niveles: string[] = [];
  let numeros: string[] = [];
  let isInitialized = false;

  // ✅ Derivamos un string para el valor del select de "Activos"
  $: activosString = $payload.activos ? "true" : "false";

  // ✅ Función para actualizar el booleano correctamente
  function setActivos(value: string) {
    $payload.activos = value === "true";
  }

  // Función para guardar el payload en localStorage
  function savePayloadToStorage() {
    if (!isInitialized) return;
    try {
      const dataToSave = {
        Asignacion: $payload.Asignacion,
        nivel: $payload.nivel,
        numero: $payload.numero,
        periodo: $payload.periodo,
        year: $payload.year,
        activos: $payload.activos,
        selectedPeriodos: $selectedPeriodos,
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));
    } catch (error) {
      console.error("Error guardando payload en localStorage:", error);
    }
  }

  // Función para cargar el payload desde localStorage
  function loadPayloadFromStorage() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (!saved) return null;
      return JSON.parse(saved);
    } catch (error) {
      console.error("Error cargando payload desde localStorage:", error);
      return null;
    }
  }

  // Guardar automáticamente cuando cambian los valores
  $: if (isInitialized) {
    // Trigger save when any payload value changes
    $payload.Asignacion;
    $payload.nivel;
    $payload.numero;
    $payload.periodo;
    $payload.year;
    $payload.activos;
    $selectedPeriodos;
    savePayloadToStorage();
  }

  onMount(async () => {
    const run = async () => {
      try {
        // Cargar valores guardados antes de fetch
        const savedPayload = loadPayloadFromStorage();

        sedes = await fetchAsignaciones();
        if (sedes.length > 0) {
          // Intentar usar el valor guardado si es válido
          if (
            savedPayload?.Asignacion &&
            sedes.some((s) => s.ind === savedPayload.Asignacion)
          ) {
            $payload.Asignacion = savedPayload.Asignacion;
          } else if (!$payload.Asignacion) {
            $payload.Asignacion = sedes[0].ind;
          }
          updateNiveles(false);
        }

        periods = await fetchPeriodos();
        if (periods.length > 0) {
          // Intentar usar el valor guardado si es válido
          if (
            savedPayload?.periodo &&
            periods.some((p) => p.nombre === savedPayload.periodo)
          ) {
            $payload.periodo = savedPayload.periodo;
          } else {
            const selectedPeriod = periods.find(
              (p) => p.selected === "selected"
            );
            if (selectedPeriod) {
              $payload.periodo = selectedPeriod.nombre;
            } else if (!$payload.periodo) {
              $payload.periodo = periods[0].nombre;
            }
          }
        }

        years = await fetchYears();
        if (years.length > 0) {
          // Intentar usar el valor guardado si es válido
          if (
            savedPayload?.year &&
            years.some((y) => y.year === savedPayload.year)
          ) {
            $payload.year = savedPayload.year;
          } else {
            const currentYear = new Date().getFullYear().toString();
            const currentYearExists = years.some((y) => y.year === currentYear);

            if (currentYearExists) {
              $payload.year = currentYear;
            } else if (!$payload.year) {
              // If current year doesn't exist in fetched data and payload.year is not set,
              // default to the first year from fetched data (assuming it's the latest/most relevant)
              $payload.year = years[0].year;
            }
          }
        } else if (!$payload.year) {
          // If no years are fetched and payload.year is not set, default to current year
          $payload.year = new Date().getFullYear().toString();
        }

        // Restaurar nivel y número si son válidos
        if (savedPayload?.nivel && niveles.includes(savedPayload.nivel)) {
          $payload.nivel = savedPayload.nivel;
          updateNumeros(false);
          if (savedPayload?.numero && numeros.includes(savedPayload.numero)) {
            $payload.numero = savedPayload.numero;
          }
        }

        // Restaurar el valor de activos
        if (savedPayload?.activos !== undefined) {
          $payload.activos = savedPayload.activos;
        }

        // Restaurar periodos seleccionados
        if (
          savedPayload?.selectedPeriodos &&
          Array.isArray(savedPayload.selectedPeriodos)
        ) {
          $selectedPeriodos = savedPayload.selectedPeriodos.filter(
            (p: string) => allPeriods.includes(p)
          );
        }

        // Marcar como inicializado para comenzar a guardar cambios
        isInitialized = true;

        // After all payload values are set, load the concentrador data
        loadConcentradorData();
      } catch (error) {
        // Error loading initial data
        isInitialized = true;
      }
    };

    if ("requestIdleCallback" in window) {
      // @ts-ignore
      window.requestIdleCallback(() => run());
    } else {
      setTimeout(() => run(), 50);
    }
  });

  function updateNiveles(resetNumero = true) {
    const selectedSede = sedes.find((s) => s.ind === $payload.Asignacion);
    if (selectedSede) {
      const uniqueNiveles = [
        ...new Set(
          selectedSede.grados.map(
            (g: { nivel: string; numero: string }) => g.nivel
          )
        ),
      ];
      niveles = uniqueNiveles;
      if (niveles.length > 0) {
        if (!$payload.nivel || !niveles.includes($payload.nivel)) {
          $payload.nivel = niveles[0];
        }
        updateNumeros(resetNumero);
      }
    }
  }

  function updateNumeros(resetNumero = true) {
    const selectedSede = sedes.find((s) => s.ind === $payload.Asignacion);
    if (selectedSede) {
      const filteredNumeros = selectedSede.grados
        .filter(
          (g: { nivel: string; numero: string }) => g.nivel === $payload.nivel
        )
        .map((g: { nivel: string; numero: string }) => g.numero);
      numeros = filteredNumeros;
      if (resetNumero && numeros.length > 0) {
        if (!$payload.numero || !numeros.includes($payload.numero)) {
          $payload.numero = numeros[0];
        }
      }
    }
  }

  function handlePeriodoChange(p: string) {
    if (!$selectedPeriodos.includes(p)) {
      $selectedPeriodos = [...$selectedPeriodos, p];
    } else {
      $selectedPeriodos = $selectedPeriodos.filter((x) => x !== p);
    }
    $selectedPeriodos = $selectedPeriodos.sort((a, b) => {
      return (
        periodOrder[a as keyof typeof periodOrder] -
        periodOrder[b as keyof typeof periodOrder]
      );
    });
  }
</script>

<div
  class="w-full max-w-full mx-auto font-sans"
  style="font-family: 'Inter', sans-serif;"
>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-all duration-300"
  >
    <div class="p-3">
      <div
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3"
      >
        <!-- Sede -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="fld-asignacion"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base">business</span>
            Sede
          </label>
          <div class="relative">
            <select
              id="fld-asignacion"
              class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer"
              bind:value={$payload.Asignacion}
              on:change={() => {
                updateNiveles();
                loadConcentradorData();
              }}
              {disabled}
            >
              {#each sedes as sede}
                <option value={sede.ind}>{sede.sede}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Nivel -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="fld-nivel"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base">school</span>
            Nivel
          </label>
          <div class="relative">
            <select
              id="fld-nivel"
              class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer"
              bind:value={$payload.nivel}
              on:change={() => {
                updateNumeros();
                loadConcentradorData();
              }}
              {disabled}
            >
              {#each niveles as nivel}
                <option value={nivel}>{nivel}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Número -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="fld-numero"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base">numbers</span>
            Número
          </label>
          <div class="relative">
            <select
              id="fld-numero"
              class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer"
              bind:value={$payload.numero}
              on:change={() => loadConcentradorData()}
              {disabled}
            >
              {#each numeros as numero}
                <option value={numero}>{numero}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Periodo -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="fld-periodo"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base"
              >calendar_month</span
            >
            Periodo
          </label>
          <div class="relative">
            <select
              id="fld-periodo"
              class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer"
              bind:value={$payload.periodo}
              on:change={() => loadConcentradorData()}
              {disabled}
            >
              {#each periods as period}
                <option value={period.nombre}>{period.nombre}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Año -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="fld-year"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base"
              >calendar_today</span
            >
            Año
          </label>
          <div class="relative">
            <select
              id="fld-year"
              class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer"
              bind:value={$payload.year}
              on:change={() => loadConcentradorData()}
              {disabled}
            >
              {#each years as yearOption}
                <option value={yearOption.year}>{yearOption.year}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Activos -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="fld-activos"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base">
              {activosString === "true" ? "toggle_on" : "toggle_off"}
            </span>
            Activos
          </label>
          <div class="relative">
            <select
              id="fld-activos"
              class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer"
              value={activosString}
              on:change={(e) => {
                const target = e.target as HTMLSelectElement;
                setActivos(target.value);
                loadConcentradorData();
              }}
              {disabled}
            >
              <option value="true">Sí</option>
              <option value="false">No</option>
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Toggle Periodos Button -->
        <div class="flex flex-col gap-1.5 group">
          <label
            for="btn-toggle-periodos"
            class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base">tune</span>
            Periodos
          </label>
          <button
            id="btn-toggle-periodos"
            type="button"
            on:click={toggleShowPeriodos}
            class="w-full appearance-none bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-between gap-2"
            {disabled}
          >
            <span>{$showPeriodos ? "Ocultar" : "Mostrar"}</span>
            <span
              class="material-symbols-rounded text-base transition-transform duration-200 {$showPeriodos
                ? 'rotate-180'
                : ''}"
            >
              expand_more
            </span>
          </button>
        </div>
      </div>

      <!-- Sección de selección de periodos -->
      {#if $showPeriodos}
        <div
          class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700"
          transition:slide={{ duration: 200 }}
        >
          <h3
            class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2"
          >
            <span class="material-symbols-rounded text-indigo-500"
              >checklist</span
            >
            Seleccionar Periodos
          </h3>
          <div class="flex flex-wrap gap-3">
            {#each allPeriods as p (p)}
              <label class="relative cursor-pointer group">
                <input
                  type="checkbox"
                  checked={$selectedPeriodos.includes(p)}
                  on:change={() => handlePeriodoChange(p)}
                  class="peer sr-only"
                />
                <div
                  class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-medium transition-all duration-200
                            peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600
                            dark:peer-checked:bg-indigo-900/20 dark:peer-checked:border-indigo-500 dark:peer-checked:text-indigo-400
                            group-hover:border-indigo-300 dark:group-hover:border-indigo-700"
                >
                  {p}
                </div>
              </label>
            {/each}
          </div>
        </div>
      {/if}
    </div>
  </div>
</div>
