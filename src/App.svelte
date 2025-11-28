<script lang="ts">
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
  import { theme } from "./lib/themeStore";
  import { fetchNotasDetallado, fetchNotasDetalladoAreas } from "./lib/api";
  import ConcentradorAsignaturasTable from "./lib/ConcentradorAsignaturasTable.svelte";
  import ConcentradorAreasTable from "./lib/ConcentradorAreasTable.svelte";
  import AppHeader from "./lib/AppHeader.svelte";
  import Login from "./lib/Login.svelte";
  // New modular components
  import BackgroundDecorations from "./lib/BackgroundDecorations.svelte";
  import PayloadFormSection from "./lib/PayloadFormSection.svelte";
  import ControlsSection from "./lib/ControlsSection.svelte";
  import ErrorAlert from "./lib/ErrorAlert.svelte";
  import LoadingSkeleton from "./lib/LoadingSkeleton.svelte";
  import DialogsContainer from "./lib/DialogsContainer.svelte";

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
    periodo: string,
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
    valoracion: string,
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
          (a) => a.abreviatura === itemAbrev,
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
  <BackgroundDecorations>
    <!-- === CABECERA === -->
    <AppHeader
      bind:showPayloadForm
      bind:showInfoCantDialog
      on:logout={handleLogout}
    />

    <!-- === FORMULARIO COLAPSABLE === -->
    <PayloadFormSection show={showPayloadForm} loading={$loading} />

    <!-- === CONTROLES === -->
    <ControlsSection {showPeriodos} />

    <!-- === MENSAJE DE ERROR === -->
    <ErrorAlert error={$error} />

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
    <LoadingSkeleton
      loading={$loading}
      parsed={$parsed}
      currentOrden={$currentOrden}
    />

    <!-- === TODOS LOS DIÁLOGOS === -->
    <DialogsContainer
      bind:showNotasDetalleDialog
      {currentNotasDetalle}
      {notasDetalleLoading}
      {notasDetalleError}
      payloadYear={$payload.year}
      {selectedPeriodoForDialog}
      {selectedEstudianteId}
      {selectedAsignaturaNombre}
      {selectedStudentName}
      onShowInasistencias={handleShowInasistencias}
      bind:showConvivenciaDialog
      bind:showInfoCantDialog
      bind:showInasistenciasDetallado
      {inasistenciasEstudianteId}
      {inasistenciasNombres}
      {inasistenciasAsignatura}
      {inasistenciasPeriodo}
      bind:showGradesTableDialog
      {selectedDocenteId}
    />
  </BackgroundDecorations>
{/if}
