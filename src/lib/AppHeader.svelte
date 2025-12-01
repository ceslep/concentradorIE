<script lang="ts">
  import { createEventDispatcher } from "svelte";
  import { theme } from "./themeStore";
  import {
    loadConcentradorData,
    loading,
    lastDuration,
    exportCSV,
    exportExcel,
    concentradorType,
  } from "./storeConcentrador";

  export let showPayloadForm: boolean;
  export let showInfoCantDialog: boolean;

  const dispatch = createEventDispatcher();

  function handleConcentradorTypeChange(event: Event) {
    const target = event.target as HTMLInputElement;
    concentradorType.set(target.checked ? "areas" : "asignaturas");
    loadConcentradorData(); // Reload data when concentrador type changes
  }

  function handleLogout() {
    dispatch("logout");
  }
</script>

<!-- Premium Header with Glassmorphism -->
<header
  class="glass-effect rounded-xl p-1.5 sm:p-2 mb-2 shadow-premium-xl animate-slide-in-down"
  style="font-family: var(--font-body);"
>
  <div
    class="flex flex-col sm:flex-row justify-between items-center gap-1 sm:gap-1.5"
  >
    <!-- Top Row: Logo/Title + Theme Toggle -->
    <div class="flex justify-between items-center">
      <!-- Logo/Title Section -->
      <div class="flex items-center gap-2">
        <div
          class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-indigo-500/30 animate-scale-in"
        >
          <span class="material-symbols-rounded text-lg sm:text-xl text-white"
            >school</span
          >
        </div>
        <div class="animate-fade-in">
          <h1
            class="text-sm sm:text-lg font-bold text-gradient"
            style="font-family: var(--font-heading);"
          >
            Concentrador IE
          </h1>
          <p
            class="text-[9px] sm:text-[10px] {$theme === 'dark'
              ? 'text-gray-400'
              : 'text-gray-500'} hidden sm:block"
          >
            Sistema de Gestión Académica
          </p>
        </div>
      </div>
    </div>

    <!-- Controls Section -->
    <div
      class="flex flex-wrap gap-1.5 sm:gap-2 items-center justify-center sm:justify-end w-full sm:w-auto"
    >
      <!-- Theme Toggle Button -->
      <button
        on:click={theme.toggle}
        class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300 hover:scale-105 shadow-md {$theme ===
        'dark'
          ? 'bg-gradient-to-br from-gray-700 to-gray-800 text-gray-200 hover:from-gray-600 hover:to-gray-700'
          : 'bg-gradient-to-br from-white to-gray-50 text-gray-700 hover:from-gray-50 hover:to-gray-100'}"
        aria-label="Toggle theme"
        title={$theme === "dark" ? "Modo Claro" : "Modo Oscuro"}
      >
        {#if $theme === "dark"}
          <span class="material-symbols-rounded text-base sm:text-lg"
            >light_mode</span
          >
        {:else}
          <span class="material-symbols-rounded text-base sm:text-lg"
            >dark_mode</span
          >
        {/if}
      </button>
      <!-- Visibility Toggle Button -->
      <button
        on:click={() => (showPayloadForm = !showPayloadForm)}
        class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300 hover:scale-105 shadow-md {$theme ===
        'dark'
          ? 'bg-gradient-to-br from-indigo-600 to-purple-600 text-white hover:from-indigo-500 hover:to-purple-500'
          : 'bg-gradient-to-br from-indigo-500 to-purple-500 text-white hover:from-indigo-600 hover:to-purple-600'}"
        title={showPayloadForm ? "Ocultar Controles" : "Mostrar Controles"}
      >
        {#if showPayloadForm}
          <span class="material-symbols-rounded text-base sm:text-lg"
            >visibility_off</span
          >
        {:else}
          <span class="material-symbols-rounded text-base sm:text-lg"
            >visibility</span
          >
        {/if}
      </button>

      <!-- Tipo de Concentrador Switch -->
      <div
        class="flex items-center gap-1 px-2 py-1 rounded-lg {$theme === 'dark'
          ? 'bg-gray-800/50'
          : 'bg-gray-100/80'} shadow-sm"
      >
        <span
          class="text-[10px] sm:text-xs font-medium {$theme === 'dark'
            ? 'text-gray-300'
            : 'text-gray-600'} hidden sm:inline"
          style="font-family: var(--font-heading);"
        >
          Asignaturas
        </span>
        <span
          class="text-[10px] font-medium {$theme === 'dark'
            ? 'text-gray-300'
            : 'text-gray-600'} sm:hidden"
          style="font-family: var(--font-heading);"
        >
          Asig
        </span>
        <label class="relative inline-flex items-center cursor-pointer group">
          <input
            type="checkbox"
            class="sr-only peer"
            on:change={handleConcentradorTypeChange}
            checked={$concentradorType === "areas"}
          />
          <div
            class="w-8 h-4 sm:w-10 sm:h-5 bg-gradient-to-r from-gray-300 to-gray-400 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gradient-to-r dark:from-gray-600 dark:to-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 sm:after:h-4 sm:after:w-4 after:transition-all after:shadow-md peer-checked:bg-gradient-to-r peer-checked:from-indigo-500 peer-checked:to-purple-500"
          ></div>
        </label>
        <span
          class="text-[10px] font-medium {$theme === 'dark'
            ? 'text-gray-300'
            : 'text-gray-600'} sm:hidden"
          style="font-family: var(--font-heading);"
        >
          Área
        </span>
        <span
          class="text-[10px] sm:text-xs font-medium {$theme === 'dark'
            ? 'text-gray-300'
            : 'text-gray-600'} hidden sm:inline"
          style="font-family: var(--font-heading);"
        >
          Áreas
        </span>
      </div>

      <!-- Divider - Hidden on mobile -->
      <div
        class="h-6 w-px {$theme === 'dark'
          ? 'bg-gray-700'
          : 'bg-gray-200'} hidden sm:block"
      ></div>

      <!-- Action Buttons Group -->
      <div class="flex gap-1 sm:gap-1.5">
        <!-- Export CSV -->
        <button
          on:click={exportCSV}
          disabled={$loading}
          class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-300 hover:scale-105 shadow-md disabled:opacity-50 disabled:cursor-not-allowed {$theme ===
          'dark'
            ? 'bg-gradient-to-br from-emerald-600 to-teal-600 text-white hover:from-emerald-500 hover:to-teal-500'
            : 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white hover:from-emerald-600 hover:to-teal-600'}"
          aria-label="Exportar CSV"
          title="Exportar a CSV"
        >
          <span class="material-symbols-rounded text-base sm:text-lg"
            >file_download</span
          >
        </button>

        <!-- Export Excel -->
        <button
          on:click={exportExcel}
          disabled={$loading}
          class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-300 hover:scale-105 shadow-md disabled:opacity-50 disabled:cursor-not-allowed {$theme ===
          'dark'
            ? 'bg-gradient-to-br from-green-600 to-emerald-600 text-white hover:from-green-500 hover:to-emerald-500'
            : 'bg-gradient-to-br from-green-500 to-emerald-500 text-white hover:from-green-600 hover:to-emerald-600'}"
          aria-label="Exportar Excel"
          title="Exportar a Excel"
        >
          <span class="material-symbols-rounded text-base sm:text-lg"
            >table_view</span
          >
        </button>

        <!-- Reload Button -->
        <button
          on:click={() => loadConcentradorData()}
          disabled={$loading}
          class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300 hover:scale-105 hover:rotate-180 shadow-md disabled:opacity-50 disabled:cursor-not-allowed {$theme ===
          'dark'
            ? 'bg-gradient-to-br from-blue-600 to-cyan-600 text-white hover:from-blue-500 hover:to-cyan-500'
            : 'bg-gradient-to-br from-blue-500 to-cyan-500 text-white hover:from-blue-600 hover:to-cyan-600'}"
          aria-label="Recargar Datos"
          title="Recargar Datos"
        >
          <span
            class="material-symbols-rounded text-base sm:text-lg {$loading
              ? 'animate-spin'
              : ''}">refresh</span
          >
        </button>

        <!-- Info Button -->
        <button
          on:click={() => (showInfoCantDialog = true)}
          class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all duration-300 hover:scale-105 shadow-md {$theme ===
          'dark'
            ? 'bg-gradient-to-br from-amber-600 to-orange-600 text-white hover:from-amber-500 hover:to-orange-500'
            : 'bg-gradient-to-br from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600'}"
          aria-label="Mostrar Información de Cantidades"
          title="Información de Cantidades"
        >
          <span class="material-symbols-rounded text-base sm:text-lg">info</span
          >
        </button>
      </div>

      <!-- Divider - Hidden on mobile -->
      <div
        class="h-6 w-px {$theme === 'dark'
          ? 'bg-gray-700'
          : 'bg-gray-200'} hidden sm:block"
      ></div>

      <!-- Logout Button -->
      <button
        on:click={handleLogout}
        class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-300 hover:scale-105 shadow-md {$theme ===
        'dark'
          ? 'bg-gradient-to-br from-red-600 to-rose-600 text-white hover:from-red-500 hover:to-rose-500'
          : 'bg-gradient-to-br from-red-500 to-rose-500 text-white hover:from-red-600 hover:to-rose-600'}"
        aria-label="Cerrar Sesión"
        title="Cerrar Sesión"
      >
        <span class="material-symbols-rounded text-base sm:text-lg">logout</span
        >
      </button>
    </div>
  </div>

  <!-- Load Time Indicator (if present) -->
  {#if $lastDuration}
    <div
      class="mt-1 pt-1 border-t {$theme === 'dark'
        ? 'border-gray-700/50'
        : 'border-gray-200/50'}"
    >
      <div class="flex items-center justify-end gap-1.5">
        <span
          class="material-symbols-rounded text-xs {$theme === 'dark'
            ? 'text-gray-500'
            : 'text-gray-400'}">schedule</span
        >
        <span
          class="text-[10px] font-medium {$theme === 'dark'
            ? 'text-gray-400'
            : 'text-gray-500'}"
          style="font-family: var(--font-heading);"
        >
          Tiempo de carga: <span class="text-gradient font-bold"
            >{$lastDuration.toFixed(2)}ms</span
          >
        </span>
      </div>
    </div>
  {/if}
</header>
