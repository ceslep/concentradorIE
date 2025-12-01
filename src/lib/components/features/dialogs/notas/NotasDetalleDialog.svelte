<script lang="ts">
    import { fade, scale } from "svelte/transition";
    import type { NotaDetalle } from "../../../../types";
    import { theme } from "../../../../themeStore";
    import NotasHistoryDialog from "./NotasHistoryDialog.svelte";
    import ConvivenciaDialog from "../convivencia/ConvivenciaDialog.svelte";

    export let showDialog: boolean;
    export let notasDetalle: NotaDetalle[] = [];
    export let loading: boolean = false;
    export let error: string | null = null;
    export let year: string;
    export let periodo: string;
    export let estudianteId: string;

    export let asignatura: string;
    export let studentName: string;
    export let onShowInasistencias: (
        estudianteId: string,
        nombres: string,
        asignatura: string,
        periodo: string,
    ) => void;

    let showNotasHistoryDialog: boolean = false;
    export let showConvivenciaDialog: boolean = false;
    let hasConvivenciaRecords: boolean = false;

    let docenteName: string = "";

    $: if (notasDetalle.length > 0) {
        docenteName = notasDetalle[0].Docente;
    }

    function closeDialog() {
        showDialog = false;
        notasDetalle = [];
        error = null;
    }

    function openNotasHistoryDialog() {
        showNotasHistoryDialog = true;
    }

    // Función auxiliar para convertir "Mes Día" a un objeto Date
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

    $: sortedNotasDetalle = [...notasDetalle].sort((a, b) => {
        const dateA = parseFecha(a.FechaNota, year);
        const dateB = parseFecha(b.FechaNota, year);
        return dateB.getTime() - dateA.getTime();
    });

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
        transition:fade={{ duration: 200 }}
        style="font-family: 'Inter', sans-serif;"
    >
        <!-- Backdrop -->
        <div
            class="absolute inset-0 w-full h-full bg-gray-900/60 backdrop-blur-sm transition-opacity cursor-default focus:outline-none"
            role="button"
            tabindex="0"
            on:click={closeDialog}
            on:keydown={(e) => e.key === "Escape" && closeDialog()}
            aria-label="Cerrar modal"
        ></div>

        <!-- Modal Container -->
        <div
            class="relative w-full max-w-5xl max-h-[90vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700 transform transition-all"
            transition:scale={{ start: 0.96, duration: 200 }}
        >
            <!-- Header -->
            <div
                class="flex-none flex flex-col md:flex-row md:items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md z-10 gap-4"
            >
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2
                            class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight leading-none"
                        >
                            Detalle de Notas
                        </h2>
                        {#if periodo}
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-900/20"
                            >
                                Periodo {periodo}
                            </span>
                        {/if}
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        {#if asignatura}
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="material-symbols-rounded text-sm text-indigo-500"
                                    >book</span
                                >
                                <span
                                    class="font-medium text-gray-700 dark:text-gray-300"
                                    >{asignatura}</span
                                >
                            </div>
                        {/if}
                        {#if studentName}
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="material-symbols-rounded text-sm text-yellow-500"
                                    >person</span
                                >
                                <span>{studentName}</span>
                            </div>
                        {/if}
                        {#if docenteName}
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="material-symbols-rounded text-sm text-blue-500"
                                    >school</span
                                >
                                <span>{docenteName}</span>
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        on:click={openNotasHistoryDialog}
                        class="p-2 rounded-lg transition-all duration-200 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/30"
                        title="Ver Historial"
                    >
                        <span class="material-symbols-rounded text-xl"
                            >history</span
                        >
                    </button>

                    <button
                        on:click={() =>
                            onShowInasistencias(
                                estudianteId,
                                studentName,
                                asignatura,
                                periodo,
                            )}
                        class="p-2 rounded-lg transition-all duration-200 text-gray-500 hover:text-orange-600 hover:bg-orange-50 dark:text-gray-400 dark:hover:text-orange-400 dark:hover:bg-orange-900/30"
                        title="Ver Inasistencias"
                    >
                        <span class="material-symbols-rounded text-xl"
                            >event_busy</span
                        >
                    </button>

                    <button
                        on:click={() => (showConvivenciaDialog = true)}
                        class="p-2 rounded-lg transition-all duration-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/30 relative"
                        title="Consolidado de Convivencia"
                    >
                        <span class="material-symbols-rounded text-xl"
                            >diversity_3</span
                        >
                        {#if hasConvivenciaRecords}
                            <span
                                class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-pulse"
                            ></span>
                        {/if}
                    </button>

                    <div
                        class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-1"
                    ></div>

                    <button
                        on:click={theme.toggle}
                        class="p-2 rounded-lg transition-all duration-200 text-gray-500 hover:text-yellow-500 hover:bg-yellow-50 dark:text-gray-400 dark:hover:text-yellow-400 dark:hover:bg-yellow-900/30"
                        title="Cambiar tema"
                    >
                        {#if $theme === "dark"}
                            <span class="material-symbols-rounded text-xl"
                                >light_mode</span
                            >
                        {:else}
                            <span class="material-symbols-rounded text-xl"
                                >dark_mode</span
                            >
                        {/if}
                    </button>

                    <button
                        on:click={closeDialog}
                        class="p-2 rounded-lg transition-all duration-200 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
                        title="Cerrar"
                    >
                        <span class="material-symbols-rounded text-2xl"
                            >close</span
                        >
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div
                class="flex-grow overflow-y-auto p-6 bg-gray-50/50 dark:bg-black/20 custom-scrollbar"
            >
                {#if loading}
                    <div
                        class="flex flex-col items-center justify-center h-64 space-y-4"
                    >
                        <div class="relative">
                            <span
                                class="material-symbols-rounded text-6xl text-indigo-500 animate-spin"
                                >progress_activity</span
                            >
                        </div>
                        <p
                            class="text-sm font-medium text-gray-500 dark:text-gray-400 animate-pulse"
                        >
                            Cargando notas...
                        </p>
                    </div>
                {:else if error}
                    <div
                        class="flex flex-col items-center justify-center h-64 text-center p-8"
                    >
                        <div
                            class="bg-red-50 dark:bg-red-900/20 p-4 rounded-full mb-4"
                        >
                            <span
                                class="material-symbols-rounded text-4xl text-red-500"
                                >error</span
                            >
                        </div>
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Error al cargar
                        </h3>
                        <p
                            class="text-gray-500 dark:text-gray-400 mt-1 max-w-sm"
                        >
                            {error}
                        </p>
                    </div>
                {:else if notasDetalle.length > 0}
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="text-xs uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-4 font-semibold"
                                            >Aspecto</th
                                        >
                                        <th
                                            scope="col"
                                            class="px-6 py-4 font-semibold"
                                            >Nota</th
                                        >
                                        <th
                                            scope="col"
                                            class="px-6 py-4 font-semibold"
                                            >Fecha Aspecto</th
                                        >
                                        <th
                                            scope="col"
                                            class="px-6 py-4 font-semibold"
                                            >Fecha Nota</th
                                        >
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-100 dark:divide-gray-700/50"
                                >
                                    {#each sortedNotasDetalle as nota}
                                        {#if nota.Nota !== null}
                                            <tr
                                                class="group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                                            >
                                                <td
                                                    class="px-6 py-4 font-medium text-gray-900 dark:text-white"
                                                >
                                                    {nota.Aspecto || "N/A"}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class={colorNotaDetalle(
                                                            nota.Nota,
                                                        )}
                                                    >
                                                        {nota.Nota || "N/A"}
                                                    </span>
                                                </td>
                                                <td
                                                    class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-xs"
                                                >
                                                    {nota.FechaAspecto}
                                                </td>
                                                <td
                                                    class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-xs"
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
                        class="flex flex-col items-center justify-center h-64 text-center p-8 opacity-60"
                    >
                        <div
                            class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4"
                        >
                            <span
                                class="material-symbols-rounded text-5xl text-gray-400"
                                >inbox</span
                            >
                        </div>
                        <p
                            class="text-lg font-medium text-gray-900 dark:text-white"
                        >
                            Sin registros
                        </p>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                            No se encontraron detalles de notas.
                        </p>
                    </div>
                {/if}
            </div>

            <!-- Footer -->
            <div
                class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 flex justify-end"
            >
                <button
                    on:click={closeDialog}
                    class="px-6 py-2.5 rounded-xl text-white font-semibold text-sm shadow-lg shadow-blue-500/30 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-blue-500/50 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
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
