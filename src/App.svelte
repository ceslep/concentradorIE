<script lang="ts">
  import PayloadForm from "./lib/PayloadForm.svelte";
  import {
    loadConcentradorData,
    loading,
    error,
    parsed,
    showPeriodos,
    selectedPeriodos,
    currentOrden,
    lastDuration,
    exportCSV,
    exportExcel,
    payload,
    concentradorType,
    selectedAsignatura,
  } from "./lib/storeConcentrador";
  import type {
    EstudianteRow,
    NotasDetalladoPayload,
    NotaDetalle,
    ConcentradorParsed,
    ConcentradorAreasParsed,
    EstudianteRowArea,
  } from "./lib/types";
  import { onMount } from "svelte";
  import { get } from "svelte/store";
  import NotasDetalleDialog from "./lib/NotasDetalleDialog.svelte";
  import Skeleton from "./lib/Skeleton.svelte";
  import { theme } from "./lib/themeStore";
  import { fetchNotasDetallado, fetchNotasDetalladoAreas } from "./lib/api";
  import InfoCantDialog from "./lib/InfoCantDialog.svelte";
  import InasistenciasDetallado from "./lib/InasistenciasDetallado.svelte"; // Import the new component
  import ConcentradorAsignaturasTable from "./lib/ConcentradorAsignaturasTable.svelte";
  import ConcentradorAreasTable from "./lib/ConcentradorAreasTable.svelte";
  import AppHeader from "./lib/AppHeader.svelte";
  import GradesTableDialog from "./lib/GradesTableDialog.svelte"; // New import
  import Login from "./lib/Login.svelte"; // Import Login component

  let showNotasDetalleDialog = false;
  let showInfoCantDialog = false;
  let showGradesTableDialog = false; // New state variable
  let selectedDocenteId: string = ""; // New state variable for docenteId
  let showInasistenciasDetallado = false; // New state variable for InasistenciasDetallado
  let showConvivenciaDialog = false; // New state variable for ConvivenciaDialog
  let currentNotasDetalle: NotaDetalle[] = [];
  let notasDetalleLoading = false;
  let notasDetalleError: string | null = null;
  let selectedStudentName = "";
  let selectedEstudianteId = "";
  let selectedAsignaturaNombre = "";
  let selectedPeriodoForDialog = ""; // New variable to store the clicked period for the dialog
  let inasistenciasEstudianteId = "";
  let inasistenciasNombres = "";
  let inasistenciasAsignatura = "";
  let inasistenciasPeriodo = "";
  let showPayloadForm = true; // Controla visibilidad del formulario

  // Authentication State
  let isAuthenticated = false;
  let user: any = null;

  function handleShowInasistencias(
    estudianteId: string,
    nombres: string,
    asignatura: string,
    periodo: string
  ) {
    inasistenciasEstudianteId = estudianteId;
    inasistenciasNombres = nombres;
    inasistenciasAsignatura = asignatura;
    inasistenciasPeriodo = periodo;
    showInasistenciasDetallado = true;
  }

  function handleHeaderClick(docenteId: string) {
    selectedDocenteId = docenteId;
    showGradesTableDialog = true;
  }

  onMount(() => {
    // loadConcentrador() will now be called from PayloadForm.svelte after it initializes
    // Check for existing session
    const storedUser = sessionStorage.getItem("user");
    if (storedUser) {
      user = JSON.parse(storedUser);
      isAuthenticated = true;
    }
  });

  function handleLoginSuccess(event: CustomEvent) {
    user = event.detail.user;
    isAuthenticated = true;
    sessionStorage.setItem("user", JSON.stringify(user));
  }

  function handleLogout() {
    isAuthenticated = false;
    user = null;
    sessionStorage.removeItem("user");
  }

  async function handleValoracionClick(
    est: EstudianteRow | EstudianteRowArea,
    itemAbrev: string,
    periodo: string,
    valoracion: string
  ) {
    if (!valoracion || valoracion === "-") return;

    showNotasDetalleDialog = true;
    currentNotasDetalle = [];
    notasDetalleLoading = true;
    notasDetalleError = null;

    let selectedItemName = itemAbrev;
    const currentConcentradorType = get(concentradorType);

    if ($parsed) {
      if (currentConcentradorType === "asignaturas") {
        const p = $parsed as ConcentradorParsed;
        const selectedAsignatura = p.asignaturas?.find(
          (a) => a.abreviatura === itemAbrev
        );
        selectedItemName = selectedAsignatura?.nombre || itemAbrev;
      } else {
        const p = $parsed as ConcentradorAreasParsed;
        const selectedArea = p.areas?.find((a) => a.abreviatura === itemAbrev);
        selectedItemName = selectedArea?.nombre || itemAbrev;
      }
    }

    selectedEstudianteId = est.id;
    selectedStudentName = est.nombres;
    selectedAsignatura.set(itemAbrev); // Update the store
    selectedAsignaturaNombre = selectedItemName;
    selectedPeriodoForDialog = periodo; // Set the clicked period

    const payloadDetalle: NotasDetalladoPayload = {
      estudiante: est.id,
      nombres: est.nombres,
      asignatura: selectedItemName,
      asignat: itemAbrev,
      valoracion: valoracion,
      periodo: periodo,
      year: $payload.year,
      asignacion: $payload.Asignacion,
      nivel: $payload.nivel,
      numero: $payload.numero,
    };

    try {
      let data: NotaDetalle[];
      if (currentConcentradorType === "asignaturas") {
        data = await fetchNotasDetallado(payloadDetalle);
      } else {
        data = await fetchNotasDetalladoAreas(payloadDetalle);
      }
      currentNotasDetalle = data;
    } catch (e: any) {
      notasDetalleError = e.message || "Error al cargar el detalle de notas.";
    } finally {
      notasDetalleLoading = false;
    }
  }
</script>

{#if !isAuthenticated}
  <Login on:loginSuccess={handleLoginSuccess} />
{:else}
  <!-- Premium Background with Subtle Pattern -->
  <div
    class="min-h-screen p-3 sm:p-4 lg:p-5 space-y-3 flex flex-col relative overflow-hidden transition-colors duration-300"
    style="font-family: var(--font-body); background: {$theme === 'dark'
      ? 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.08) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.08) 0%, transparent 50%), #0f172a'
      : 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(240, 147, 251, 0.05) 0%, transparent 50%), #ffffff'};"
  >
    <!-- Decorative Background Elements -->
    <div
      class="fixed inset-0 pointer-events-none opacity-30"
      style="background-image: radial-gradient({$theme === 'dark'
        ? 'rgba(148, 163, 184, 0.15)'
        : 'rgba(203, 213, 225, 0.3)'} 1px, transparent 1px); background-size: 40px 40px;"
    ></div>

    <!-- === CABECERA === -->
    <AppHeader
      bind:showPayloadForm
      bind:showInfoCantDialog
      on:logout={handleLogout}
    />
    <!-- Optional: Add Logout Button here or in AppHeader if requested later -->

    <!-- === FORMULARIO COLAPSABLE === -->
    {#if showPayloadForm}
      <section
        class="glass-effect-strong rounded-xl p-4 shadow-premium-xl animate-slide-in-down relative overflow-hidden border-2"
        style="border-color: {$theme === 'dark'
          ? 'rgba(102, 126, 234, 0.2)'
          : 'rgba(102, 126, 234, 0.15)'};"
      >
        <!-- Decorative gradient overlay -->
        <div
          class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-full blur-3xl -z-10"
        ></div>
        <PayloadForm disabled={$loading} />
      </section>
    {/if}

    <!-- === CONTROLES === -->
    <div class="flex flex-wrap gap-3 items-center animate-fade-in">
      <label
        class="flex items-center gap-2 px-3 py-2 rounded-lg glass-effect shadow-md hover:shadow-lg transition-all duration-300 cursor-pointer group"
      >
        <!-- Custom Checkbox -->
        <div class="relative">
          <input
            type="checkbox"
            bind:checked={$showPeriodos}
            class="peer sr-only"
          />
          <div
            class="w-6 h-6 rounded-lg border-2 {$theme === 'dark'
              ? 'border-gray-600 bg-gray-800'
              : 'border-gray-300 bg-white'} peer-checked:bg-gradient-to-br peer-checked:from-indigo-500 peer-checked:to-purple-500 peer-checked:border-transparent transition-all duration-300 flex items-center justify-center shadow-sm"
          >
            <span
              class="material-symbols-rounded text-white text-lg opacity-0 peer-checked:opacity-100 transition-opacity duration-300"
              >check</span
            >
          </div>
        </div>
        <span
          class="text-sm font-medium {$theme === 'dark'
            ? 'text-gray-200'
            : 'text-gray-700'} group-hover:text-indigo-500 transition-colors"
          style="font-family: var(--font-heading);"
        >
          Mostrar todos los periodos
        </span>
      </label>
    </div>

    <!-- === MENSAJE DE ERROR === -->
    {#if $error}
      <div
        class="glass-effect-strong border-2 border-red-500/50 px-6 py-4 rounded-2xl relative animate-shake shadow-premium-xl"
        role="alert"
        style="background: {$theme === 'dark'
          ? 'linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(185, 28, 28, 0.15))'
          : 'linear-gradient(135deg, rgba(254, 202, 202, 0.5), rgba(252, 165, 165, 0.5))'};"
      >
        <div class="flex items-start gap-3">
          <div
            class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg"
          >
            <span class="material-symbols-rounded text-white text-xl"
              >error</span
            >
          </div>
          <div class="flex-1">
            <strong
              class="font-bold text-red-600 dark:text-red-400 block mb-1"
              style="font-family: var(--font-heading);">Error</strong
            >
            <span
              class="text-sm {$theme === 'dark'
                ? 'text-red-200'
                : 'text-red-800'}">{$error}</span
            >
          </div>
        </div>
      </div>
    {/if}

    <!-- === TABLA DE ESTUDIANTES === -->
    {#if $concentradorType === "asignaturas"}
      <ConcentradorAsignaturasTable
        {handleValoracionClick}
        onHeaderClick={handleHeaderClick}
      />
    {:else}
      <ConcentradorAreasTable {handleValoracionClick} />
    {/if}

    <!-- === SKELETON (cargando) === -->
    {#if $loading && !$parsed}
      {#if $currentOrden && $currentOrden.length}
        {@const cols = $currentOrden.length + 1}
        {@const rest = 70 / Math.max(1, $currentOrden.length)}
        {@const widths = [
          "30%",
          ...Array($currentOrden.length).fill(`${rest}%`),
        ]}
        <Skeleton
          rows={8}
          columns={cols}
          headerWidth="30%"
          columnWidths={widths}
          theme={$theme}
        />
      {:else}
        <Skeleton rows={8} columns={5} headerWidth="33%" theme={$theme} />
      {/if}
    {/if}

    <!-- === DIALOGO DE NOTAS DETALLADAS === -->
    <NotasDetalleDialog
      bind:showDialog={showNotasDetalleDialog}
      notasDetalle={currentNotasDetalle}
      loading={notasDetalleLoading}
      error={notasDetalleError}
      year={$payload.year}
      periodo={selectedPeriodoForDialog}
      estudianteId={selectedEstudianteId}
      asignatura={selectedAsignaturaNombre}
      studentName={selectedStudentName}
      onShowInasistencias={handleShowInasistencias}
      bind:showConvivenciaDialog
    />

    <!-- === DIALOGO DE INFORMACIÓN DE CANTIDADES === -->
    <InfoCantDialog bind:showDialog={showInfoCantDialog} />

    {#if showInasistenciasDetallado}
      <InasistenciasDetallado
        estudianteId={inasistenciasEstudianteId}
        nombres={inasistenciasNombres}
        asignatura={inasistenciasAsignatura}
        periodo={inasistenciasPeriodo}
        bind:showDialog={showInasistenciasDetallado}
      />
    {/if}

    <GradesTableDialog
      bind:showDialog={showGradesTableDialog}
      docenteId={selectedDocenteId}
    />
  </div>
{/if}
