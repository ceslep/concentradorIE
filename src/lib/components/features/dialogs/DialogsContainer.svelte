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

    // NotasDetalleDialog props
    export let showNotasDetalleDialog: boolean;
    export let currentNotasDetalle: NotaDetalle[];
    export let notasDetalleLoading: boolean;
    export let notasDetalleError: string | null;
    export let payloadYear: string;
    export let selectedPeriodoForDialog: string;
    export let selectedEstudianteId: string;
    export let selectedAsignaturaNombre: string;
    export let selectedStudentName: string;
    export let onShowInasistencias: (
        estudianteId: string,
        nombres: string,
        asignatura: string,
        periodo: string,
    ) => void;
    export let showConvivenciaDialog: boolean;

    // InfoCantDialog props
    export let showInfoCantDialog: boolean;

    // InasistenciasDetallado props
    export let showInasistenciasDetallado: boolean;
    export let inasistenciasEstudianteId: string;
    export let inasistenciasNombres: string;
    export let inasistenciasAsignatura: string;
    export let inasistenciasPeriodo: string;

    // GradesTableDialog props
    export let showGradesTableDialog: boolean;
    export let selectedDocenteId: string;
    export let payloadPeriodo: string;
</script>

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
