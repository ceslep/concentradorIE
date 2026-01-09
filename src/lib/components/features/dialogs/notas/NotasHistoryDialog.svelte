<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { GET_NOTAS_HISTORY_ENDPOINT } from "../../../../../constants";
    import type { NotaHistory } from "../../../../types";
    import Skeleton from "../../../shared/Skeleton.svelte";
    import { theme } from "../../../../themeStore";
    import Tooltip from "../../../shared/Tooltip.svelte";

    export let showDialog: boolean = false;
    export let studentId: string;
    export let subject: string;
    export let periodo: string;
    export let year: string;

    let notasHistory: NotaHistory[] = [];
    let loading: boolean = false;
    let error: string | null = null;

    // Siempre mostrar todas las columnas N1..N12
    let columnNames: (keyof NotaHistory)[] = [
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
        // Extract the number from "notaX"
        const num = colName.replace("nota", "");
        const aspectoKey = `aspecto${num}` as keyof NotaHistory;
        const fechaKey = `fecha${num}` as keyof NotaHistory;

        const aspecto = nota[aspectoKey] ?? "N/A";
        const fecha = nota[fechaKey] ?? "N/A";
        return `Aspecto: ${aspecto}\nFecha: ${fecha}`;
    }

    function shouldBlinkRed(value: string | null | undefined): boolean {
        if (value === null || value === undefined || value === "") return false;
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
                ? 'bg-[#1f1f1f] text-[#e8eaed]'
                : 'bg-white text-[#1f1f1f]'}"
            on:click|stopPropagation
            role="dialog"
            aria-modal="true"
            tabindex="-1"
            on:keydown={(e) => {
                if (e.key === "Escape") closeDialog();
            }}
        >
            <div
                class="px-6 pt-6 pb-5 border-b {$theme === 'dark'
                    ? 'border-[#3c4043]'
                    : 'border-[#e8eaed]'}"
            >
                <h2
                    class="text-xl font-semibold leading-7 {$theme === 'dark'
                        ? 'text-[#e8eaed]'
                        : 'text-[#1f1f1f]'}"
                >
                    Historial de Notas
                </h2>
                <p
                    class="text-sm mt-1.5 {$theme === 'dark'
                        ? 'text-[#9aa0a6]'
                        : 'text-[#5f6368]'}"
                >
                    {studentId} • {subject}
                </p>
            </div>

            <div class="flex-1 overflow-hidden relative">
                {#if loading}
                    <div class="p-6">
                        <Skeleton rows={5} columns={6} theme={$theme} />
                    </div>
                {:else if error}
                    <div
                        class="p-6 text-center rounded-lg mx-6 mt-6 {$theme ===
                        'dark'
                            ? 'bg-red-900/20'
                            : 'bg-red-50'}"
                    >
                        <p class="text-[#d93025] font-medium">Error: {error}</p>
                    </div>
                {:else if notasHistory.length > 0}
                    <div class="table-container custom-scrollbar">
                        <table class="w-full text-sm text-left">
                            <thead
                                class="sticky top-0 z-10 {$theme === 'dark'
                                    ? 'bg-[#25262a]'
                                    : 'bg-[#f8f9fa]'}"
                            >
                                <tr>
                                    {#each columnNames as colName}
                                        <th
                                            class="font-semibold text-xs uppercase tracking-wider px-6 py-4 whitespace-nowrap {$theme ===
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
                                {#each filteredNotasHistory as nota, index}
                                    <tr
                                        class="border-b last:border-0 transition-all duration-200 {$theme ===
                                        'dark'
                                            ? 'border-[#3c4043] hover:bg-[#292a2d]'
                                            : 'border-[#e8eaed] hover:bg-[#f8f9fa]'} {index %
                                            2 ===
                                        0
                                            ? $theme === 'dark'
                                                ? ''
                                                : ''
                                            : $theme === 'dark'
                                              ? 'bg-[#25262a]/30'
                                              : 'bg-[#fafbfc]'}"
                                    >
                                        {#each columnNames as colName}
                                            <td
                                                class="px-5 py-4 whitespace-nowrap"
                                            >
                                                <Tooltip
                                                    content={getHintText(
                                                        nota,
                                                        colName,
                                                    )}
                                                >
                                                    <span
                                                        class:low-grade={shouldBlinkRed(
                                                            nota[
                                                                colName as keyof NotaHistory
                                                            ],
                                                        )}
                                                        class={!shouldBlinkRed(
                                                            nota[
                                                                colName as keyof NotaHistory
                                                            ],
                                                        )
                                                            ? "font-medium"
                                                            : ""}
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
                            class="text-base {$theme === 'dark'
                                ? 'text-[#9aa0a6]'
                                : 'text-[#5f6368]'}"
                        >
                            No hay historial disponible.
                        </p>
                    </div>
                {/if}
            </div>

            <div
                class="px-6 py-4 flex justify-end border-t {$theme === 'dark'
                    ? 'border-[#3c4043]'
                    : 'border-[#e8eaed]'}"
            >
                <button
                    on:click={closeDialog}
                    class="px-6 py-2.5 min-h-[44px] rounded-full text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 {$theme ===
                    'dark'
                        ? 'text-[#8ab4f8] hover:bg-[#8ab4f8]/10 focus:ring-[#8ab4f8] focus:ring-offset-[#1f1f1f]'
                        : 'text-[#1a73e8] hover:bg-[#1a73e8]/10 focus:ring-[#1a73e8] focus:ring-offset-white'} active:scale-95"
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
        background: linear-gradient(
            135deg,
            rgba(0, 0, 0, 0.35),
            rgba(0, 0, 0, 0.28)
        );
        backdrop-filter: blur(6px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        animation: fadeIn 0.25s ease-out;
    }

    .dialog-content {
        width: 90%;
        max-width: 800px;
        max-height: 85vh;
        border-radius: 28px;
        box-shadow:
            0 28px 56px -16px rgba(0, 0, 0, 0.24),
            0 8px 16px -8px rgba(0, 0, 0, 0.12);
        overflow: hidden;
        animation: scaleIn 0.25s cubic-bezier(0.2, 0, 0, 1);
    }

    .table-container {
        overflow: auto;
        height: 100%;
    }

    /* Custom Scrollbar with refined aesthetics */
    .custom-scrollbar::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.15);
        border-radius: 5px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0, 0, 0, 0.25);
    }
    :global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.15);
    }
    :global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(255, 255, 255, 0.25);
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
            transform: scale(0.92) translateY(8px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes gentlePulse {
        0%,
        100% {
            background-color: rgba(217, 48, 37, 0.12);
            box-shadow: 0 0 0 0 rgba(217, 48, 37, 0.4);
        }
        50% {
            background-color: rgba(217, 48, 37, 0.18);
            box-shadow: 0 0 0 3px rgba(217, 48, 37, 0);
        }
    }

    .low-grade {
        color: #d93025;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        animation: gentlePulse 2s ease-in-out infinite;
        display: inline-block;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .dialog-content {
            width: 95%;
            max-height: 90vh;
        }
    }
</style>
