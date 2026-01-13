<!-- 
APPHEADER.SVELTE

DESCRIPCIÓN:
Cabecera premium del sistema 'Concentrador IE'. Proporciona navegación principal, controles de vista, cambio de tema, exportación de datos y cierre de sesión. Utiliza glassmorphism para un diseño moderno y fluido.

USO:
<AppHeader bind:showPayloadForm bind:showInfoCantDialog on:logout={handleLogout} /> en App.svelte.

DEPENDENCIAS:
- Store: theme (themeStore.ts), storeConcentrador (loading, lastDuration, concentradorType, viewMode).
- Utilidades: loadConcentradorData, exportCSV, exportExcel (storeConcentrador.ts).

PROPS/EMIT:
- Prop: `showPayloadForm` → boolean → Controla la visibilidad del formulario de parámetros.
- Prop: `showInfoCantDialog` → boolean → Controla la visibilidad del diálogo de estadísticas.
- Evento emitido: `logout` → Notifica al padre para limpiar la sesión.

RELACIONES:
- Llamado por: App.svelte.
- Depende de: themeStore.ts y storeConcentrador.ts.

NOTAS DE DESARROLLO:
- Implementa animaciones 'animate-slide-in-down' y 'animate-scale-in'.
- El switch de tipo (Asignaturas/Áreas) dispara automáticamente la recarga de datos.

ESTILOS:
- Usa clases de utilidad 'glass-effect', 'btn-premium', y 'text-gradient'.
- Adaptable (responsive) con ocultamiento de etiquetas en móviles.
-->

<script lang="ts">
  import { theme } from "../../themeStore";
  import {
    loadConcentradorData,
    loading,
    lastDuration,
    exportCSV,
    exportExcel,
    concentradorType,
    viewMode,
    resetToDashboard,
  } from "../../storeConcentrador";

  let {
    showPayloadForm = $bindable(),
    showInfoCantDialog = $bindable(),
    user = null,
    onLogout,
  } = $props<{
    showPayloadForm: boolean;
    showInfoCantDialog: boolean;
    user?: any;
    onLogout?: () => void;
  }>();

  /**
   * Maneja el cambio de tipo de concentrador (Asignaturas <-> Áreas).
   * @param event - Evento de cambio del checkbox.
   */
  function handleConcentradorTypeChange(event: Event) {
    const target = event.target as HTMLInputElement;
    concentradorType.set(target.checked ? "areas" : "asignaturas");
    loadConcentradorData(); // Recarga los datos al cambiar el tipo
  }

  /**
   * Alterna la visibilidad del formulario de parámetros (Sede, Nivel, etc.).
   */
  function togglePayloadForm() {
    showPayloadForm = !showPayloadForm;
  }

  /**
   * Cambia el tipo de concentrador entre 'asignaturas' y 'areas'.
   * @param type - El tipo de vista seleccionado.
   */
  function handleTypeChange(type: "asignaturas" | "areas") {
    concentradorType.set(type);
  }

  /**
   * Ejecuta la recarga de datos del concentrador.
   */
  function handleReload() {
    loadConcentradorData();
  }

  /**
   * Solicita el cierre de sesión mediante el despacho de un evento.
   */
  function handleLogout() {
    if (onLogout) onLogout();
  }

  /**
   * Dispara el diálogo de información de cantidades poblacionales.
   */
  function openInfoCant() {
    showInfoCantDialog = true;
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
          <div class="flex flex-col">
            <p
              class="text-[9px] sm:text-[10px] {$theme === 'dark'
                ? 'text-gray-400'
                : 'text-gray-500'} hidden sm:block leading-tight"
            >
              Sistema de Gestión Académica
            </p>
            {#if user?.nombres}
              <p
                class="text-[8px] sm:text-[9px] font-medium text-indigo-500 dark:text-indigo-400 leading-tight"
              >
                Usuario: {user.nombres}
              </p>
            {/if}
          </div>
        </div>
      </div>
    </div>

    <!-- Controls Section -->
    <div
      class="flex flex-wrap gap-1.5 sm:gap-2 items-center justify-center sm:justify-end w-full sm:w-auto"
    >
      <!-- Theme Toggle Button -->
      <button
        onclick={theme.toggle}
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
        onclick={() => (showPayloadForm = !showPayloadForm)}
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
            onchange={handleConcentradorTypeChange}
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

      <!-- View Mode Toggle -->
      <div
        class="flex items-center gap-1 px-2 py-1 rounded-lg {$theme === 'dark'
          ? 'bg-gray-800/50'
          : 'bg-gray-100/80'} shadow-sm"
      >
        <button
          onclick={() => viewMode.set("table-view")}
          class="p-1.5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500
                    {$viewMode === 'table-view'
            ? 'bg-gradient-to-br from-indigo-500 to-purple-500 text-white shadow-md'
            : 'text-gray-500 hover:bg-gray-200/50 dark:hover:bg-gray-700/50'}"
          title="Vista de Tabla"
          aria-label="Cambiar a vista de tabla"
        >
          <span class="material-symbols-rounded text-base sm:text-lg"
            >table_rows</span
          >
        </button>
        <button
          onclick={() => viewMode.set("cards-view")}
          class="p-1.5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500
                    {$viewMode === 'cards-view'
            ? 'bg-gradient-to-br from-indigo-500 to-purple-500 text-white shadow-md'
            : 'text-gray-500 hover:bg-gray-200/50 dark:hover:bg-gray-700/50'}"
          title="Vista de Tarjetas"
          aria-label="Cambiar a vista de tarjetas"
        >
          <span class="material-symbols-rounded text-base sm:text-lg"
            >grid_view</span
          >
        </button>
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
          onclick={exportCSV}
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
          onclick={exportExcel}
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
          onclick={() => loadConcentradorData()}
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
          onclick={() => (showInfoCantDialog = true)}
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

      <!-- Dashboard Button -->
      <button
        onclick={resetToDashboard}
        class="btn-premium p-1.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300 hover:scale-105 shadow-md {$theme ===
        'dark'
          ? 'bg-gradient-to-br from-indigo-600 to-purple-600 text-white hover:from-indigo-500 hover:to-purple-500'
          : 'bg-gradient-to-br from-indigo-500 to-purple-500 text-white hover:from-indigo-600 hover:to-purple-600'}"
        aria-label="Volver al Dashboard"
        title="Volver al Dashboard"
      >
        <span class="material-symbols-rounded text-base sm:text-lg">apps</span>
      </button>

      <!-- Logout Button -->
      <button
        onclick={handleLogout}
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
