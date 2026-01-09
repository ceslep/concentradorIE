<!-- 
APP.SVELTE

DESCRIPCIÓN:
Componente raíz de la aplicación Concentrador IE. Orquestra la autenticación, la carga de datos maestros, la navegación entre vistas (Tablas vs Tarjetas) y la gestión centralizada de diálogos/modales.

USO:
Punto de entrada principal configurado en main.ts.

DEPENDENCIAS:
- Stores: parsed, loading, error, payload, viewMode, etc.
- Componentes: AppHeader, PayloadFormSection, ConcentradorAsignaturasTable, ConcentradorAreasTable, StudentCardsView, DialogsContainer.
- API: fetchNotasDetallado, fetchNotasDetalladoAreas.

PROPS/EMIT:
No recibe props externas significativas al ser el componente raíz.

RELACIONES:
- Actúa como contenedor padre de toda la jerarquía de componentes.
- Gestiona el estado compartido que fluye hacia DialogsContainer.

NOTAS DE DESARROLLO:
- Centraliza la lógica de `handleValoracionClick` para uniformizar la carga de detalles de notas desde cualquier vista.
- Implementa la persistencia de sesión básica mediante `sessionStorage`.

ESTILOS:
- Envuelve el contenido en BackgroundDecorations para establecer la estética global (Cyber-Mechanical).
-->

<script lang="ts">
  import { onMount } from "svelte";
  import { get } from "svelte/store";
  import ConcentradorAsignaturasTable from "./lib/components/features/concentrador/ConcentradorAsignaturasTable.svelte";
  import ConcentradorAreasTable from "./lib/components/features/concentrador/ConcentradorAreasTable.svelte";
  import ControlsSection from "./lib/components/features/concentrador/ControlsSection.svelte";
  import DialogsContainer from "./lib/components/features/dialogs/DialogsContainer.svelte";
  import ErrorAlert from "./lib/components/shared/ErrorAlert.svelte";
  import LoadingSkeleton from "./lib/components/shared/LoadingSkeleton.svelte";
  import PayloadFormSection from "./lib/components/features/concentrador/PayloadFormSection.svelte";
  import AppHeader from "./lib/components/layout/AppHeader.svelte";
  import BackgroundDecorations from "./lib/components/layout/BackgroundDecorations.svelte";
  import Login from "./lib/components/features/auth/Login.svelte";
  import StudentCardsView from "./lib/components/features/concentrador/StudentCardsView.svelte"; // Re-added import
  import {
    parsed,
    loading,
    error,
    payload,
    showPeriodos,
    concentradorType,
    selectedAsignatura,
    currentOrden,
    viewMode, // Re-added import
  } from "./lib/storeConcentrador";
  import { fetchNotasDetallado, fetchNotasDetalladoAreas } from "./lib/api";
  import type {
    NotaDetalle,
    EstudianteRow,
    EstudianteRowArea,
    ConcentradorParsed,
    ConcentradorAreasParsed,
    NotasDetalladoPayload,
  } from "./lib/types";

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
  let showGradesTableDialog = false;
  let showNotasDetalleDialog = false;
  let showInfoCantDialog = false;

  // Authentication State
  let isAuthenticated = false;
  let user: any = null;

  /**
   * Gestiona la visualización de inasistencias detalladas para un estudiante.
   */
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

  /**
   * Orquestador central para la carga de detalles de notas.
   * Se activa al hacer clic en una valoración específica en cualquier tabla o tarjeta.
   * Realiza la llamada a la API y abre el diálogo correspondiente.
   */
  async function handleValoracionClick(
    est: EstudianteRow | EstudianteRowArea,
    itemAbrev: string,
    periodo: string,
    valoracion: string,
    itemNombre: string = itemAbrev,
  ) {
    // Si la valoración está vacía o es '-', verificar si es una solicitud desde una tarjeta de asignatura
    if ((!valoracion || valoracion === "-") && itemNombre) {
      // Es una solicitud desde el botón de la tarjeta, proceder a cargar notas
    } else if (!valoracion || valoracion === "-") {
      return;
    }

    showNotasDetalleDialog = true;
    currentNotasDetalle = [];
    notasDetalleLoading = true;
    notasDetalleError = null;

    let selectedItemName = itemNombre;
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
    selectedAsignatura.set(itemAbrev);
    selectedAsignaturaNombre = selectedItemName;
    selectedPeriodoForDialog = periodo;

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

  // Function to handle the custom event from StudentCardsView
  function handleOpenNotasDetalleEvent(event: CustomEvent) {
    const { student, itemAbrev, periodo, valoracion, itemNombre } =
      event.detail;
    handleValoracionClick(student, itemAbrev, periodo, valoracion, itemNombre);
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
    <ControlsSection />

    <!-- === MENSAJE DE ERROR === -->
    <ErrorAlert error={$error} />

    <!-- === TABLA DE ESTUDIANTES / VISTA DE TARJETAS === -->
    {#if $viewMode === "cards-view"}
      <StudentCardsView on:openNotasDetalle={handleOpenNotasDetalleEvent} />
    {:else if $concentradorType === "asignaturas"}
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
      payloadPeriodo={$payload.periodo}
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
