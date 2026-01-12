<!-- 
PAYLOADFORM.SVELTE

DESCRIPCIÓN:
Motor de parámetros de la aplicación. Gestiona la selección de Sede, Nivel, Grupo, Periodo y Año. Incluye persistencia local para recordar la última selección del usuario.

USO:
<PayloadForm disabled={loading} /> usado internamente por PayloadFormSection.svelte.

DEPENDENCIAS:
- Store: storeConcentrador (payload, showPeriodos, selectedPeriodos, loadConcentradorData), theme (themeStore.ts).
- API: fetchAsignaciones, fetchPeriodos, fetchYears.

PROPS/EMIT:
- Prop: `disabled` → boolean → Bloquea la interacción durante procesos de carga.

RELACIONES:
- Llamado por: PayloadFormSection.svelte.
- Almacena datos en: localStorage (`concentradorIE_lastPayload`).

NOTAS DE DESARROLLO:
- Sincroniza dinámicamente los niveles y grupos base a la sede seleccionada.
- Orquestador principal de la carga inicial de datos mediante `loadConcentradorData()`.

ESTILOS:
- Diseño compacto con 'cyber-border-form' y efectos de foco ('select-glow').
- Checkboxes personalizados para selección múltiple de periodos.
-->

<script lang="ts">
  import {
    payload,
    showPeriodos,
    selectedPeriodos,
    loadConcentradorData,
    toggleShowPeriodos,
  } from "../../../storeConcentrador";
  import type { Sede } from "../../../api";
  import { fetchAsignaciones, fetchPeriodos, fetchYears } from "../../../api";
  import type { Periodo, Year } from "../../../types";
  import { onMount } from "svelte";
  import { theme } from "../../../themeStore";
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
              (p) => p.selected === "selected",
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
            (p: string) => allPeriods.includes(p),
          );
        }

        // Marcar como inicializado para comenzar a guardar cambios
        isInitialized = true;

        // After all payload values are set, load the concentrador data
        // loadConcentradorData(); // Comentado para permitir que DashboardSelection maneje la carga inicial
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
            (g: { nivel: string; numero: string }) => g.nivel,
          ),
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
          (g: { nivel: string; numero: string }) => g.nivel === $payload.nivel,
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
  class="w-full max-w-full mx-auto font-sans payload-form-container"
  style="font-family: 'Inter', sans-serif;"
>
  <div
    class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl rounded-2xl shadow-2xl border-2 border-gray-200/50 dark:border-gray-700/50 overflow-hidden transition-all duration-300 cyber-border-form relative"
  >
    <!-- Decorative gradient overlay -->
    <div
      class="absolute inset-0 opacity-30 dark:opacity-20 pointer-events-none"
    >
      <div
        class="absolute top-0 left-0 w-64 h-64 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-transparent blur-3xl animate-pulse"
      ></div>
      <div
        class="absolute bottom-0 right-0 w-64 h-64 bg-gradient-to-tl from-pink-500/20 via-purple-500/20 to-transparent blur-3xl animate-pulse"
        style="animation-delay: 1s;"
      ></div>
    </div>

    <!-- Subtle top gradient line -->
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-indigo-500/60 to-transparent"
    ></div>

    <div class="p-4 relative z-10">
      <div
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-4"
      >
        <!-- Sede -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 0ms;"
        >
          <label
            for="fld-asignacion"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 group-focus-within:bg-gradient-to-r group-focus-within:from-indigo-600 group-focus-within:to-purple-600 dark:group-focus-within:from-indigo-400 dark:group-focus-within:to-purple-400 group-focus-within:bg-clip-text group-focus-within:text-transparent transition-all duration-300"
          >
            <span
              class="material-symbols-rounded text-base drop-shadow-sm group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400"
              >business</span
            >
            Sede
          </label>
          <div class="relative">
            <select
              id="fld-asignacion"
              class="w-full appearance-none bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-2 border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 select-glow"
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
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Nivel -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 50ms;"
        >
          <label
            for="fld-nivel"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 group-focus-within:bg-gradient-to-r group-focus-within:from-indigo-600 group-focus-within:to-purple-600 dark:group-focus-within:from-indigo-400 dark:group-focus-within:to-purple-400 group-focus-within:bg-clip-text group-focus-within:text-transparent transition-all duration-300"
          >
            <span
              class="material-symbols-rounded text-base drop-shadow-sm group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400"
              >school</span
            >
            Nivel
          </label>
          <div class="relative">
            <select
              id="fld-nivel"
              class="w-full appearance-none bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-2 border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 select-glow"
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
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Número -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 100ms;"
        >
          <label
            for="fld-numero"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 group-focus-within:bg-gradient-to-r group-focus-within:from-indigo-600 group-focus-within:to-purple-600 dark:group-focus-within:from-indigo-400 dark:group-focus-within:to-purple-400 group-focus-within:bg-clip-text group-focus-within:text-transparent transition-all duration-300"
          >
            <span
              class="material-symbols-rounded text-base drop-shadow-sm group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400"
              >numbers</span
            >
            Número
          </label>
          <div class="relative">
            <select
              id="fld-numero"
              class="w-full appearance-none bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-2 border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 select-glow"
              bind:value={$payload.numero}
              on:change={() => loadConcentradorData()}
              {disabled}
            >
              {#each numeros as numero}
                <option value={numero}>{numero}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Periodo -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 150ms;"
        >
          <label
            for="fld-periodo"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 group-focus-within:bg-gradient-to-r group-focus-within:from-indigo-600 group-focus-within:to-purple-600 dark:group-focus-within:from-indigo-400 dark:group-focus-within:to-purple-400 group-focus-within:bg-clip-text group-focus-within:text-transparent transition-all duration-300"
          >
            <span
              class="material-symbols-rounded text-base drop-shadow-sm group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400"
              >calendar_month</span
            >
            Periodo
          </label>
          <div class="relative">
            <select
              id="fld-periodo"
              class="w-full appearance-none bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-2 border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 select-glow"
              bind:value={$payload.periodo}
              on:change={() => loadConcentradorData()}
              {disabled}
            >
              {#each periods as period}
                <option value={period.nombre}>{period.nombre}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Año -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 200ms;"
        >
          <label
            for="fld-year"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 group-focus-within:bg-gradient-to-r group-focus-within:from-indigo-600 group-focus-within:to-purple-600 dark:group-focus-within:from-indigo-400 dark:group-focus-within:to-purple-400 group-focus-within:bg-clip-text group-focus-within:text-transparent transition-all duration-300"
          >
            <span
              class="material-symbols-rounded text-base drop-shadow-sm group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400"
              >calendar_today</span
            >
            Año
          </label>
          <div class="relative">
            <select
              id="fld-year"
              class="w-full appearance-none bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-2 border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 select-glow"
              bind:value={$payload.year}
              on:change={() => loadConcentradorData()}
              {disabled}
            >
              {#each years as yearOption}
                <option value={yearOption.year}>{yearOption.year}</option>
              {/each}
            </select>
            <div
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Activos -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 250ms;"
        >
          <label
            for="fld-activos"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 group-focus-within:bg-gradient-to-r group-focus-within:from-indigo-600 group-focus-within:to-purple-600 dark:group-focus-within:from-indigo-400 dark:group-focus-within:to-purple-400 group-focus-within:bg-clip-text group-focus-within:text-transparent transition-all duration-300"
          >
            <span
              class="material-symbols-rounded text-base drop-shadow-sm transition-all duration-300 {activosString ===
              'true'
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-gray-500 dark:text-gray-500'}"
            >
              {activosString === "true" ? "toggle_on" : "toggle_off"}
            </span>
            Activos
          </label>
          <div class="relative">
            <select
              id="fld-activos"
              class="w-full appearance-none bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-sm border-2 border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all duration-300 cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-lg hover:shadow-indigo-500/10 select-glow"
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
              class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500 dark:text-gray-400 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors"
            >
              <span class="material-symbols-rounded text-xl">expand_more</span>
            </div>
          </div>
        </div>

        <!-- Toggle Periodos Button -->
        <div
          class="flex flex-col gap-2 group form-field-animated"
          style="animation-delay: 300ms;"
        >
          <label
            for="btn-toggle-periodos"
            class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 transition-colors"
          >
            <span class="material-symbols-rounded text-base drop-shadow-sm"
              >tune</span
            >
            Periodos
          </label>
          <button
            id="btn-toggle-periodos"
            type="button"
            on:click={toggleShowPeriodos}
            class="w-full appearance-none bg-gradient-to-r from-indigo-500/90 to-purple-500/90 hover:from-indigo-600 hover:to-purple-600 border-2 border-indigo-400/50 dark:border-indigo-500/50 text-white text-xs font-bold rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 transition-all duration-300 cursor-pointer hover:shadow-lg hover:shadow-indigo-500/30 flex items-center justify-between gap-2 active:scale-95 toggle-glow"
            {disabled}
          >
            <span>{$showPeriodos ? "Ocultar" : "Mostrar"}</span>
            <span
              class="material-symbols-rounded text-base transition-transform duration-300 {$showPeriodos
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
          class="mt-4 pt-4 border-t-2 border-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20"
          transition:slide={{ duration: 300 }}
        >
          <h3
            class="text-sm font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent mb-4 flex items-center gap-2"
          >
            <span
              class="material-symbols-rounded text-indigo-500 dark:text-indigo-400 drop-shadow-sm"
              >checklist</span
            >
            Seleccionar Periodos
          </h3>
          <div class="flex flex-wrap gap-3">
            {#each allPeriods as p (p)}
              <label
                class="relative cursor-pointer group periodo-badge-wrapper"
              >
                <input
                  type="checkbox"
                  checked={$selectedPeriodos.includes(p)}
                  on:change={() => handlePeriodoChange(p)}
                  class="peer sr-only"
                />
                <div
                  class="px-4 py-2 rounded-xl border-2 font-bold text-xs transition-all duration-300 transform
                            bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-sm
                            border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400
                            peer-checked:scale-105
                            {p === 'UNO'
                    ? 'peer-checked:bg-gradient-to-br peer-checked:from-blue-500 peer-checked:to-blue-600 peer-checked:border-blue-400 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-blue-500/40 periodo-glow-blue'
                    : ''}
                            {p === 'DOS'
                    ? 'peer-checked:bg-gradient-to-br peer-checked:from-green-500 peer-checked:to-green-600 peer-checked:border-green-400 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-green-500/40 periodo-glow-green'
                    : ''}
                            {p === 'TRES'
                    ? 'peer-checked:bg-gradient-to-br peer-checked:from-yellow-500 peer-checked:to-yellow-600 peer-checked:border-yellow-400 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-yellow-500/40 periodo-glow-yellow'
                    : ''}
                            {p === 'CUATRO'
                    ? 'peer-checked:bg-gradient-to-br peer-checked:from-purple-500 peer-checked:to-purple-600 peer-checked:border-purple-400 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-purple-500/40 periodo-glow-purple'
                    : ''}
                            {p === 'DEF'
                    ? 'peer-checked:bg-gradient-to-br peer-checked:from-indigo-600 peer-checked:to-indigo-700 peer-checked:border-indigo-400 peer-checked:text-white peer-checked:shadow-xl peer-checked:shadow-indigo-500/50 periodo-glow-def font-extrabold'
                    : ''}
                            group-hover:scale-105 group-hover:border-indigo-300 dark:group-hover:border-indigo-600"
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

<style>
  /* Cyber border glow effect for form container */
  .cyber-border-form {
    position: relative;
  }

  .cyber-border-form::before {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: 1rem;
    padding: 2px;
    background: linear-gradient(
      135deg,
      rgba(99, 102, 241, 0.4),
      rgba(168, 85, 247, 0.3),
      rgba(236, 72, 153, 0.3),
      rgba(99, 102, 241, 0.4)
    );
    -webkit-mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.6;
    pointer-events: none;
  }

  /* Form field entrance animations */
  @keyframes slideInField {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .form-field-animated {
    animation: slideInField 0.5s ease-out backwards;
  }

  /* Select glow effect on focus */
  .select-glow:focus {
    box-shadow:
      0 0 0 3px rgba(99, 102, 241, 0.1),
      0 4px 12px rgba(99, 102, 241, 0.15);
  }

  /* Toggle button glow */
  .toggle-glow:hover {
    box-shadow:
      0 0 20px rgba(99, 102, 241, 0.4),
      0 0 40px rgba(168, 85, 247, 0.2);
  }

  /* Periodo badge glow effects */
  .periodo-glow-blue:hover {
    box-shadow:
      0 0 20px rgba(59, 130, 246, 0.6),
      0 0 30px rgba(59, 130, 246, 0.3);
  }

  .periodo-glow-green:hover {
    box-shadow:
      0 0 20px rgba(34, 197, 94, 0.6),
      0 0 30px rgba(34, 197, 94, 0.3);
  }

  .periodo-glow-yellow:hover {
    box-shadow:
      0 0 20px rgba(234, 179, 8, 0.6),
      0 0 30px rgba(234, 179, 8, 0.3);
  }

  .periodo-glow-purple:hover {
    box-shadow:
      0 0 20px rgba(168, 85, 247, 0.6),
      0 0 30px rgba(168, 85, 247, 0.3);
  }

  .periodo-glow-def:hover {
    box-shadow:
      0 0 25px rgba(99, 102, 241, 0.7),
      0 0 40px rgba(99, 102, 241, 0.4);
  }

  /* Badge pulse animation on select */
  @keyframes badgePulse {
    0%,
    100% {
      transform: scale(1.05);
    }
    50% {
      transform: scale(1.08);
    }
  }

  .periodo-badge-wrapper input:checked + div {
    animation: badgePulse 0.3s ease-out;
  }
</style>
