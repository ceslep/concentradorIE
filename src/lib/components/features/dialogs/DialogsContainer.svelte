<!-- 
DIALOGSCONTAINER.SVELTE

DESCRIPCIÓN:
Orquestador central de diálogos. Actúa como capa de abstracción para renderizar múltiples modales (Notas, Información, Inasistencias, Estadísticas) centralizando sus propiedades y estados.

USO:
<DialogsContainer {...props} /> en App.svelte.

DEPENDENCIAS:
- Componentes: NotasDetalleDialog, InfoCantDialog, InasistenciasDetallado, GradesTableDialog.
- Tipos: NotaDetalle (types.ts).

PROPS/EMIT:
- Recibe múltiples props para cada diálogo hijo, facilitando la limpieza del componente App.svelte.

RELACIONES:
- Llamado por: App.svelte.
- Envuelve a: Todos los componentes de diálogo principales.

NOTAS DE DESARROLLO:
- Este componente no maneja lógica propia, solo actúa como pasarela de props (prop drilling controlado) para mantener App.svelte organizado.
-->

<script lang="ts">
    import NotasDetalleDialog from "./notas/NotasDetalleDialog.svelte";
    import InfoCantDialog from "./info/InfoCantDialog.svelte";
    import InasistenciasDetallado from "./inasistencias/InasistenciasDetallado.svelte";
    import GradesTableDialog from "./grades/GradesTableDialog.svelte";
    import type { NotaDetalle } from "../../../types";
    import DevLabel from "../../shared/DevLabel.svelte";

    let {
        showNotasDetalleDialog = $bindable(false),
        currentNotasDetalle,
        notasDetalleLoading,
        notasDetalleError,
        payloadYear,
        selectedPeriodoForDialog,
        selectedEstudianteId,
        selectedAsignaturaNombre,
        selectedStudentName,
        onShowInasistencias,
        showConvivenciaDialog = $bindable(false),
        showInfoCantDialog = $bindable(false),
        showInasistenciasDetallado = $bindable(false),
        inasistenciasEstudianteId,
        inasistenciasNombres,
        inasistenciasAsignatura,
        inasistenciasPeriodo,
        showGradesTableDialog = $bindable(false),
        selectedDocenteId,
        payloadPeriodo,
    } = $props<{
        showNotasDetalleDialog?: boolean;
        currentNotasDetalle: NotaDetalle[];
        notasDetalleLoading: boolean;
        notasDetalleError: string | null;
        payloadYear: string;
        selectedPeriodoForDialog: string;
        selectedEstudianteId: string;
        selectedAsignaturaNombre: string;
        selectedStudentName: string;
        onShowInasistencias: (
            estudianteId: string,
            nombres: string,
            asignatura: string,
            periodo: string,
        ) => void;
        showConvivenciaDialog?: boolean;
        showInfoCantDialog?: boolean;
        showInasistenciasDetallado?: boolean;
        inasistenciasEstudianteId: string;
        inasistenciasNombres: string;
        inasistenciasAsignatura: string;
        inasistenciasPeriodo: string;
        showGradesTableDialog?: boolean;
        selectedDocenteId: string;
        payloadPeriodo: string;
    }>();
</script>

<DevLabel name="DialogsContainer.svelte" />

<!-- === DIALOGO DE NOTAS DETALLADAS === -->
<NotasDetalleDialog
    bind:showDialog={showNotasDetalleDialog}
    notasDetalle={currentNotasDetalle}
    loading={notasDetalleLoading}
    error={notasDetalleError}
    year={payloadYear}
    periodo={selectedPeriodoForDialog}
    estudianteId={selectedEstudianteId}
    asignatura={selectedAsignaturaNombre}
    studentName={selectedStudentName}
    {onShowInasistencias}
    bind:showConvivenciaDialog
/>

<!-- === DIALOGO DE INFORMACIÓN DE CANTIDADES === -->
<InfoCantDialog bind:showDialog={showInfoCantDialog} />

<!-- === DIALOGO DE INASISTENCIAS DETALLADO === -->
{#if showInasistenciasDetallado}
    <InasistenciasDetallado
        estudianteId={inasistenciasEstudianteId}
        nombres={inasistenciasNombres}
        asignatura={inasistenciasAsignatura}
        periodo={inasistenciasPeriodo}
        year={payloadYear}
        bind:showDialog={showInasistenciasDetallado}
    />
{/if}

<!-- === DIALOGO DE TABLA DE NOTAS POR DOCENTE === -->
<GradesTableDialog
    bind:showDialog={showGradesTableDialog}
    docenteId={selectedDocenteId}
    periodo={payloadPeriodo}
/>
