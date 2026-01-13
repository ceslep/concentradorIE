<!-- 
NOTASDETALLEDIALOG.SVELTE

DESCRIPCIÓN:
Diálogo central para la revisión profunda de calificaciones de un estudiante en una asignatura. Permite ver el desglose por tipo de nota, acceder al historial de cambios, inasistencias y detalles adicionales del estudiante.

USO:
<NotasDetalleDialog {...props} /> como parte de DialogsContainer.

DEPENDENCIAS:
- API: fetchStudentDetails.
- Componentes: NotasHistoryDialog, ConvivenciaDialog, StudentDetailsModal.

PROPS/EMIT:
- Prop: `notasDetalle` → NotaDetalle[] → Array con el desglose de notas actual.
- Prop: `onShowInasistencias` → function → Callback para redirigir a la vista de inasistencias.

RELACIONES:
- Llamado por: DialogsContainer.svelte (procedente de un click en tablas o tarjetas).
- Llama a: Múltiplos submódulos de diálogo/modales.

NOTAS DE DESARROLLO:
- Realiza el ordenamiento por fecha de las notas cargadas.
- Actúa como hub de navegación hacia otros detalles académicos del estudiante.

ESTILOS:
- Diseño inmersivo con 'backdrop-blur-md' y gradientes dinámicos en el header.
-->

<script lang="ts">
    import { fade, scale } from "svelte/transition";
    import type { NotaDetalle, EstudianteDetalle } from "../../../../types";
    import { theme } from "../../../../themeStore";
    import NotasHistoryDialog from "./NotasHistoryDialog.svelte";
    import ConvivenciaDialog from "../convivencia/ConvivenciaDialog.svelte";
    import StudentDetailsModal from "../../students/StudentDetailsModal.svelte";
    import { fetchStudentDetails } from "$lib/api";
    import Loader from "../../../shared/Loader.svelte";

    let {
        showDialog = $bindable(false),
        notasDetalle = $bindable([]),
        loading = false,
        error = $bindable(null),
        year,
        periodo,
        estudianteId,
        asignatura,
        studentName,
        onShowInasistencias,
        showConvivenciaDialog = $bindable(false),
        onClose,
    } = $props<{
        showDialog?: boolean;
        notasDetalle?: NotaDetalle[];
        loading?: boolean;
        error?: string | null;
        year: string;
        periodo: string;
        estudianteId: string;
        asignatura: string;
        studentName: string;
        onShowInasistencias: (
            estudianteId: string,
            nombres: string,
            asignatura: string,
            periodo: string,
        ) => void;
        showConvivenciaDialog?: boolean;
        onClose?: () => void;
    }>();

    let showNotasHistoryDialog: boolean = $state(false);
    let hasConvivenciaRecords: boolean = $state(false);

    let docenteName: string = $derived(
        notasDetalle.length > 0 ? notasDetalle[0].Docente : "",
    );
    let showStudentDetailsModal: boolean = $state(false);
    let estudianteDetalle: EstudianteDetalle | null = $state(null);
    let loadingStudentDetails: boolean = $state(false);

    function openNotasHistoryDialog() {
        showNotasHistoryDialog = true;
    }

    function closeDialog() {
        showDialog = false;
        notasDetalle = [];
        error = null;
    }

    /**
     * Convierte una cadena de fecha con formato "Mes Día" (español) a un objeto Date.
     * @param fechaStr - Cadena tipo "Octubre 24".
     * @param currentYear - Año actual para completar el objeto Date.
     */
    function parseFecha(fechaStr: string, currentYear: string): Date {
        if (!fechaStr || fechaStr.trim() === "") {
            return new Date(0);
        }
        const monthMap: { [key: string]: number } = {
            Enero: 0,
            Febrero: 1,
            Marzo: 2,
            Abril: 3,
            Mayo: 4,
            Junio: 5,
            Julio: 6,
            Agosto: 7,
            Septiembre: 8,
            Octubre: 9,
            Noviembre: 10,
            Diciembre: 11,
        };

        const parts = fechaStr.split(" ");
        if (parts.length !== 2) {
            return new Date(0);
        }
        const monthName = parts[0];
        const day = parseInt(parts[1], 10);
        const month = monthMap[monthName];

        if (isNaN(day) || month === undefined) {
            return new Date(0);
        }

        return new Date(parseInt(currentYear), month, day);
    }

    let sortedNotasDetalle = $derived(
        [...notasDetalle].sort((a, b) => {
            const dateA = parseFecha(a.FechaNota, year);
            const dateB = parseFecha(b.FechaNota, year);
            return dateB.getTime() - dateA.getTime();
        }),
    );

    function colorNotaDetalle(nota: string | null): string {
        if (nota === null) return "";
        const v = parseFloat(nota);
        if (isNaN(v)) return "";
        if (v < 3) {
            return "text-red-600 dark:text-red-400 font-bold";
        }
        return "text-gray-900 dark:text-gray-100 font-semibold";
    }
</script>

{#if showDialog}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-sans"
        transition:fade={{ duration: 250 }}
        style="font-family: 'Inter', sans-serif;"
    >
        <!-- Backdrop -->
        <div
            class="absolute inset-0 w-full h-full bg-gradient-to-br from-gray-900/65 via-gray-900/60 to-gray-900/65 backdrop-blur-md transition-opacity cursor-default focus:outline-none"
            role="button"
            tabindex="0"
            onclick={closeDialog}
            onkeydown={(e) => e.key === "Escape" && closeDialog()}
            aria-label="Cerrar modal"
        ></div>

        <!-- Modal Container -->
        <div
            class="relative w-full max-w-5xl max-h-[92vh] flex flex-col bg-white dark:bg-[#1a1b1e] rounded-3xl shadow-2xl overflow-hidden border border-gray-200/80 dark:border-gray-700/60 transform transition-all"
            transition:scale={{ start: 0.94, duration: 250 }}
        >
            <!-- Header -->
            <div
                class="flex-none flex flex-col lg:flex-row lg:items-center justify-between px-7 py-6 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-br from-white/95 via-white/90 to-white/95 dark:from-gray-900/95 dark:via-gray-900/90 dark:to-gray-900/95 backdrop-blur-xl z-10 gap-5"
            >
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h2
                            class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent tracking-tight leading-tight"
                        >
                            Detalle de Notas
                        </h2>
                        {#if periodo}
                            <span
                                class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/40 dark:to-green-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/30 shadow-sm"
                            >
                                Periodo {periodo}
                            </span>
                        {/if}
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-x-5 gap-y-2.5 text-sm"
                    >
                        {#if asignatura}
                            <div class="flex items-center gap-2 group">
                                <div
                                    class="p-1 rounded-md bg-indigo-50 dark:bg-indigo-900/30 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition-colors"
                                >
                                    <span
                                        class="material-symbols-rounded text-base text-indigo-600 dark:text-indigo-400"
                                        >book</span
                                    >
                                </div>
                                <span
                                    class="font-semibold text-gray-800 dark:text-gray-200"
                                    >{asignatura}</span
                                >
                            </div>
                        {/if}
                        {#if studentName}
                            <div class="flex items-center gap-2 group">
                                <div
                                    class="p-1 rounded-md bg-amber-50 dark:bg-amber-900/30 group-hover:bg-amber-100 dark:group-hover:bg-amber-900/50 transition-colors"
                                >
                                    <span
                                        class="material-symbols-rounded text-base text-amber-600 dark:text-amber-400"
                                        >person</span
                                    >
                                </div>
                                <span
                                    class="text-gray-700 dark:text-gray-300 font-medium"
                                    >{studentName}</span
                                >
                            </div>
                        {/if}
                        {#if docenteName}
                            <div class="flex items-center gap-2 group">
                                <div
                                    class="p-1 rounded-md bg-blue-50 dark:bg-blue-900/30 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors"
                                >
                                    <span
                                        class="material-symbols-rounded text-base text-blue-600 dark:text-blue-400"
                                        >school</span
                                    >
                                </div>
                                <span
                                    class="text-gray-700 dark:text-gray-300 font-medium"
                                    >{docenteName}</span
                                >
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <!-- Action Buttons Group -->
                    <div
                        class="flex items-center gap-1 p-1 rounded-xl bg-gray-100/80 dark:bg-gray-800/80"
                    >
                        <button
                            onclick={openNotasHistoryDialog}
                            class="p-2.5 rounded-lg transition-all duration-200 text-gray-600 hover:text-indigo-600 hover:bg-white dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-gray-700 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            title="Ver Historial"
                        >
                            <span class="material-symbols-rounded text-[22px]"
                                >history</span
                            >
                        </button>

                        <button
                            onclick={() =>
                                onShowInasistencias(
                                    estudianteId,
                                    studentName,
                                    asignatura,
                                    periodo,
                                )}
                            class="p-2.5 rounded-lg transition-all duration-200 text-gray-600 hover:text-orange-600 hover:bg-white dark:text-gray-400 dark:hover:text-orange-400 dark:hover:bg-gray-700 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30"
                            title="Ver Inasistencias"
                        >
                            <span class="material-symbols-rounded text-[22px]"
                                >event_busy</span
                            >
                        </button>

                        <button
                            onclick={() => (showConvivenciaDialog = true)}
                            class="p-2.5 rounded-lg transition-all duration-200 text-gray-600 hover:text-blue-600 hover:bg-white dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-gray-700 hover:shadow-sm relative focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                            title="Consolidado de Convivencia"
                        >
                            <span class="material-symbols-rounded text-[22px]"
                                >diversity_3</span
                            >
                            {#if hasConvivenciaRecords}
                                <span
                                    class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full shadow-lg shadow-red-500/50"
                                    style="animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"
                                ></span>
                            {/if}
                        </button>

                        <button
                            onclick={async () => {
                                loadingStudentDetails = true;
                                try {
                                    estudianteDetalle =
                                        await fetchStudentDetails(
                                            estudianteId,
                                            year,
                                        );
                                    showStudentDetailsModal = true;
                                } catch (err) {
                                    console.error(
                                        "Failed to fetch student details:",
                                        err,
                                    );
                                } finally {
                                    loadingStudentDetails = false;
                                }
                            }}
                            class="p-2.5 rounded-lg transition-all duration-200 text-gray-600 hover:text-green-600 hover:bg-white dark:text-gray-400 dark:hover:text-green-400 dark:hover:bg-gray-700 hover:shadow-sm disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-green-500/30"
                            title="Ver Detalles del Estudiante"
                            disabled={loadingStudentDetails}
                        >
                            {#if loadingStudentDetails}
                                <span
                                    class="material-symbols-rounded text-[22px] animate-spin"
                                    >progress_activity</span
                                >
                            {:else}
                                <span
                                    class="material-symbols-rounded text-[22px]"
                                    >person_search</span
                                >
                            {/if}
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="w-px h-7 bg-gray-300 dark:bg-gray-600"></div>

                    <!-- Theme & Close Group -->
                    <div class="flex items-center gap-1">
                        <button
                            onclick={theme.toggle}
                            class="p-2.5 rounded-lg transition-all duration-200 text-gray-600 hover:text-amber-600 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/30 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                            title="Cambiar tema"
                        >
                            {#if $theme === "dark"}
                                <span
                                    class="material-symbols-rounded text-[22px]"
                                    >light_mode</span
                                >
                            {:else}
                                <span
                                    class="material-symbols-rounded text-[22px]"
                                    >dark_mode</span
                                >
                            {/if}
                        </button>

                        <button
                            onclick={closeDialog}
                            class="p-2.5 rounded-lg transition-all duration-200 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                            title="Cerrar"
                        >
                            <span
                                class="material-symbols-rounded text-[24px] font-medium"
                                >close</span
                            >
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div
                class="flex-grow overflow-y-auto p-6 md:p-8 bg-gradient-to-br from-gray-50/80 via-gray-50/60 to-gray-50/80 dark:from-black/30 dark:via-black/20 dark:to-black/30 custom-scrollbar"
            >
                {#if loading}
                    <Loader message="Cargando notas..." />
                {:else if error}
                    <div
                        class="flex flex-col items-center justify-center h-80 text-center p-8"
                    >
                        <div
                            class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 p-5 rounded-2xl mb-5 shadow-sm"
                        >
                            <span
                                class="material-symbols-rounded text-5xl text-red-600 dark:text-red-400"
                                >error</span
                            >
                        </div>
                        <h3
                            class="text-xl font-bold text-gray-900 dark:text-white mb-2"
                        >
                            No se pudo cargar la información
                        </h3>
                        <p
                            class="text-gray-600 dark:text-gray-400 max-w-md leading-relaxed"
                        >
                            {error}
                        </p>
                    </div>
                {:else if notasDetalle.length > 0}
                    <div
                        class="bg-white dark:bg-gray-800/90 rounded-2xl shadow-lg border border-gray-200/80 dark:border-gray-700/50 overflow-hidden backdrop-blur-sm"
                    >
                        <div class="overflow-x-auto custom-table-scrollbar">
                            <table class="w-full text-sm">
                                <thead
                                    class="text-xs font-bold uppercase tracking-wider bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-900/80 dark:to-gray-900/60 text-gray-600 dark:text-gray-400 border-b-2 border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 md:px-8 py-4 text-left font-bold"
                                            >Aspecto</th
                                        >
                                        <th
                                            scope="col"
                                            class="px-6 md:px-8 py-4 text-left font-bold"
                                            >Nota</th
                                        >
                                        <th
                                            scope="col"
                                            class="px-6 md:px-8 py-4 text-left font-bold"
                                            >Fecha Aspecto</th
                                        >
                                        <th
                                            scope="col"
                                            class="px-6 md:px-8 py-4 text-left font-bold"
                                            >Fecha Nota</th
                                        >
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-100 dark:divide-gray-700/60"
                                >
                                    {#each sortedNotasDetalle as nota, index}
                                        {#if nota.Nota !== null}
                                            <tr
                                                class="group hover:bg-gradient-to-r hover:from-gray-50/80 hover:to-transparent dark:hover:from-gray-700/40 dark:hover:to-transparent transition-all duration-200 {index %
                                                    2 ===
                                                0
                                                    ? ''
                                                    : 'bg-gray-50/30 dark:bg-gray-800/30'}"
                                            >
                                                <td
                                                    class="px-6 md:px-8 py-4 font-semibold text-gray-900 dark:text-gray-100"
                                                >
                                                    {nota.Aspecto || "N/A"}
                                                </td>
                                                <td class="px-6 md:px-8 py-4">
                                                    <span
                                                        class="{colorNotaDetalle(
                                                            nota.Nota,
                                                        )} text-base"
                                                    >
                                                        {nota.Nota || "N/A"}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 md:px-8 py-4 text-gray-600 dark:text-gray-400 font-mono text-xs tracking-wide"
                                                >
                                                    {nota.FechaAspecto}
                                                </td>
                                                <td
                                                    class="px-6 md:px-8 py-4 text-gray-600 dark:text-gray-400 font-mono text-xs tracking-wide"
                                                >
                                                    {nota.FechaNota}
                                                </td>
                                            </tr>
                                        {/if}
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {:else}
                    <div
                        class="flex flex-col items-center justify-center h-80 text-center p-8"
                    >
                        <div
                            class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-full flex items-center justify-center mb-6 shadow-inner"
                        >
                            <span
                                class="material-symbols-rounded text-6xl text-gray-400 dark:text-gray-600"
                                >inbox</span
                            >
                        </div>
                        <h3
                            class="text-xl font-bold text-gray-900 dark:text-white mb-2"
                        >
                            Sin registros disponibles
                        </h3>
                        <p
                            class="text-gray-500 dark:text-gray-400 leading-relaxed"
                        >
                            No se encontraron detalles de notas para este
                            periodo.
                        </p>
                    </div>
                {/if}
            </div>

            <!-- Footer -->
            <div
                class="flex-none px-6 md:px-8 py-5 border-t border-gray-200 dark:border-gray-800 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-900/80 dark:to-gray-900/60 flex justify-end backdrop-blur-sm"
            >
                <button
                    onclick={closeDialog}
                    class="px-8 py-3 rounded-xl text-white font-bold text-sm shadow-xl shadow-indigo-500/40 dark:shadow-indigo-500/30 transition-all duration-200 transform hover:scale-[1.03] hover:shadow-2xl hover:shadow-indigo-500/50 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900 bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-600 hover:from-indigo-700 hover:via-blue-700 hover:to-indigo-700"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
{/if}

<NotasHistoryDialog
    bind:showDialog={showNotasHistoryDialog}
    studentId={estudianteId}
    subject={asignatura}
    {periodo}
    {year}
/>

<ConvivenciaDialog
    bind:showDialog={showConvivenciaDialog}
    {estudianteId}
    {year}
/>

{#if estudianteDetalle}
    <StudentDetailsModal
        bind:showModal={showStudentDetailsModal}
        estudiante={estudianteDetalle}
    />
{/if}

{#if loadingStudentDetails}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-sans bg-gray-900/60 backdrop-blur-sm"
        transition:fade={{ duration: 200 }}
    >
        <div
            class="flex flex-col items-center justify-center p-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg"
        >
            <span
                class="material-symbols-rounded text-6xl text-indigo-500 animate-spin"
                >progress_activity</span
            >
            <p
                class="mt-4 text-lg font-medium text-gray-700 dark:text-gray-300"
            >
                Cargando detalles del estudiante...
            </p>
        </div>
    </div>
{/if}
