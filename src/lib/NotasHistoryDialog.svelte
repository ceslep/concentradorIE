<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { GET_NOTAS_HISTORY_ENDPOINT } from "../../constants";
    import type { NotaHistory } from "./types";
    import Skeleton from "./Skeleton.svelte";
    import { theme } from "./themeStore";
    import Tooltip from "./Tooltip.svelte";

    export let showDialog: boolean = false;
    export let studentId: string;
    export let subject: string;
    export let periodo: string;
    export let year: string;

    let notasHistory: NotaHistory[] = [];
    let loading: boolean = false;
    let error: string | null = null;

    // Siempre mostrar todas las columnas N1..N12
    let columnNames: string[] = [
        "nota1",
        "nota2",
        "nota3",
        "nota4",
        "nota5",
        "nota6",
        "nota7",
        "nota8",
        "nota9",
        "nota10",
        "nota11",
        "nota12",
    ];

    const dispatch = createEventDispatcher();

    function areNotasEqual(nota1: NotaHistory, nota2: NotaHistory): boolean {
        for (let i = 1; i <= 12; i++) {
            const notaKey = `nota${i}` as keyof NotaHistory;
            if (nota1[notaKey] !== nota2[notaKey]) {
                return false;
            }
        }
        return true;
    }

    function getUniqueNotas(notas: NotaHistory[]): NotaHistory[] {
        const uniqueNotas: NotaHistory[] = [];
        for (const nota of notas) {
            if (
                !uniqueNotas.some((uniqueNota) =>
                    areNotasEqual(uniqueNota, nota),
                )
            ) {
                uniqueNotas.push(nota);
            }
        }
        return uniqueNotas;
    }

    async function fetchNotasHistory() {
        loading = true;
        error = null;
        try {
            const response = await fetch(GET_NOTAS_HISTORY_ENDPOINT, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    estudiante: studentId,
                    asignatura: subject,
                    periodo: periodo,
                    year: year,
                }),
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const fetchedNotas: NotaHistory[] = await response.json();
            notasHistory = getUniqueNotas(fetchedNotas);
        } catch (e: any) {
            error = e.message;
        } finally {
            loading = false;
        }
    }

    // Evitar fetch duplicado
    let lastFetchedKey: string | null = null;
    $: {
        const key = `${studentId}-${subject}-${periodo}-${year}`;
        if (showDialog && studentId && subject && periodo && year) {
            if (key !== lastFetchedKey) {
                lastFetchedKey = key;
                fetchNotasHistory();
            }
        } else {
            lastFetchedKey = null;
        }
    }

    function closeDialog() {
        showDialog = false;
        dispatch("close");
    }

    function isNotaHistoryRowEmpty(nota: NotaHistory): boolean {
        for (let i = 1; i <= 12; i++) {
            const notaKey = `nota${i}` as keyof NotaHistory;
            if (nota[notaKey] !== null && nota[notaKey] !== "") {
                return false;
            }
        }
        return true;
    }

    function getHintText(nota: NotaHistory, colName: string): string {
        const aspectoKey = colName.replace("nota", "aspecto");
        const fechaKey = colName.replace("nota", "fecha");
        // Usamos 'as any' solo si confiamos en la estructura; alternativa: definir tipo completo
        const aspecto = (nota as any)[aspectoKey] ?? "N/A";
        const fecha = (nota as any)[fechaKey] ?? "N/A";
        return `Aspecto: ${aspecto}\nFecha: ${fecha}`;
    }

    function shouldBlinkRed(value: string | null): boolean {
        if (value === null || value === "") return false;
        const num = parseFloat(value);
        return !isNaN(num) && num < 3;
    }

    $: filteredNotasHistory = notasHistory.filter(
        (nota) => !isNotaHistoryRowEmpty(nota),
    );
</script>

{#if showDialog}
    <div
        class="dialog-backdrop"
        on:click={closeDialog}
        role="button"
        tabindex="0"
        on:keydown={(e) => {
            if (e.key === "Escape") closeDialog();
        }}
        aria-label="Cerrar diálogo"
    >
        <div
            class="dialog-content flex flex-col {$theme === 'dark'
                ? 'bg-[#202124] text-[#e8eaed]'
                : 'bg-white text-[#202124]'}"
            on:click|stopPropagation
            role="dialog"
            aria-modal="true"
            tabindex="-1"
            on:keydown={(e) => {
                if (e.key === "Escape") closeDialog();
            }}
        >
            <div class="px-6 pt-6 pb-4">
                <h2
                    class="text-[1.375rem] leading-[1.75rem] font-normal {$theme ===
                    'dark'
                        ? 'text-[#e8eaed]'
                        : 'text-[#202124]'}"
                >
                    Historial de Notas
                </h2>
                <p
                    class="text-sm mt-1 {$theme === 'dark'
                        ? 'text-[#9aa0a6]'
                        : 'text-[#5f6368]'}"
                >
                    {studentId} • {subject}
                </p>
            </div>

            <div
                class="flex-1 overflow-hidden relative border-t {$theme ===
                'dark'
                    ? 'border-[#3c4043]'
                    : 'border-[#dadce0]'}"
            >
                {#if loading}
                    <div class="p-6">
                        <Skeleton rows={5} columns={6} theme={$theme} />
                    </div>
                {:else if error}
                    <div class="p-6 text-center">
                        <p class="text-[#d93025]">Error: {error}</p>
                    </div>
                {:else if notasHistory.length > 0}
                    <div class="table-container custom-scrollbar">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="sticky top-0 z-10 {$theme === 'dark'
                                    ? 'bg-[#202124]'
                                    : 'bg-white'}"
                            >
                                <tr>
                                    {#each columnNames as colName}
                                        <th
                                            class="font-medium px-4 py-3 whitespace-nowrap {$theme ===
                                            'dark'
                                                ? 'text-[#9aa0a6]'
                                                : 'text-[#5f6368]'}"
                                        >
                                            {colName.replace("nota", "N")}
                                        </th>
                                    {/each}
                                </tr>
                            </thead>
                            <tbody>
                                {#each filteredNotasHistory as nota}
                                    <tr
                                        class="border-b last:border-0 transition-colors duration-150 {$theme ===
                                        'dark'
                                            ? 'border-[#3c4043] hover:bg-[#303134]'
                                            : 'border-[#f1f3f4] hover:bg-[#f8f9fa]'}"
                                    >
                                        {#each columnNames as colName}
                                            <td
                                                class="px-4 py-3 whitespace-nowrap"
                                            >
                                                <Tooltip
                                                    content={getHintText(
                                                        nota,
                                                        colName,
                                                    )}
                                                >
                                                    <span
                                                        class:blink-red={shouldBlinkRed(
                                                            nota[
                                                                colName as keyof NotaHistory
                                                            ],
                                                        )}
                                                    >
                                                        {nota[
                                                            colName as keyof NotaHistory
                                                        ] || "-"}
                                                    </span>
                                                </Tooltip>
                                            </td>
                                        {/each}
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {:else}
                    <div class="p-8 text-center">
                        <p
                            class={$theme === "dark"
                                ? "text-[#9aa0a6]"
                                : "text-[#5f6368]"}
                        >
                            No hay historial disponible.
                        </p>
                    </div>
                {/if}
            </div>

            <div class="px-6 py-4 flex justify-end">
                <button
                    on:click={closeDialog}
                    class="px-6 py-2 rounded-full text-sm font-medium transition-colors duration-200 {$theme ===
                    'dark'
                        ? 'text-[#8ab4f8] hover:bg-[#8ab4f8]/10'
                        : 'text-[#1a73e8] hover:bg-[#1a73e8]/10'}"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    .dialog-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.32); /* Material scrim opacity */
        backdrop-filter: blur(2px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        animation: fadeIn 0.2s ease-out;
    }

    .dialog-content {
        width: 90%;
        max-width: 600px;
        max-height: 85vh;
        border-radius: 28px; /* Material 3 Dialog radius */
        box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.18);
        overflow: hidden;
        animation: scaleIn 0.2s cubic-bezier(0.2, 0, 0, 1);
    }

    .table-container {
        overflow: auto;
        height: 100%;
    }

    /* Custom Scrollbar for a cleaner look */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 4px;
    }
    :global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.2);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            opacity: 1;
        }
    }

    .blink-red {
        animation: blink 1s linear infinite;
        color: #d93025; /* Google Red */
        font-weight: 500;
    }
</style>
