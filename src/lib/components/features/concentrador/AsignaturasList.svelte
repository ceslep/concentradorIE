<!-- 
ASIGNATURASLIST.SVELTE

DESCRIPCIÓN:
Componente que muestra la lista de asignaturas asignadas para un año específico.

USO:
<AsignaturasList /> en ConfigurationMenu.svelte o similar.
-->

<script lang="ts">
    import { onMount } from "svelte";
    import { fade, fly } from "svelte/transition";
    import { GET_ASIGNACION_ASIGNATURAS_ENDPOINT } from "../../../../constants";

    interface AsignaturaAsignacion {
        id: string; // orden
        asignatura: string; // nombre de la asignatura
        docente: string; // id del docente
        docente_nombre?: string; // nombre del docente (joined)
        nivel: string;
        numero: string;
        sede: string;
        year: string;
        visible: string; // "1" or "0"
        orden: string;
        grados: string;
    }

    let asignaturas: AsignaturaAsignacion[] = [];
    let loading = true;
    let error = "";
    let currentYear = new Date().getFullYear();

    // Check development mode to force year 2025
    if (import.meta.env.DEV) {
        currentYear = 2025;
    }

    async function fetchAsignaturas() {
        loading = true;
        error = "";
        try {
            const res = await fetch(
                `${GET_ASIGNACION_ASIGNATURAS_ENDPOINT}?year=${currentYear}`,
            );
            if (!res.ok) {
                throw new Error(`Error ${res.status}: ${res.statusText}`);
            }
            const data = await res.json();
            if (data.success) {
                asignaturas = data.data;
            } else {
                throw new Error(
                    data.error || "Error desconocido al cargar asignaturas",
                );
            }
        } catch (err: any) {
            console.error("Error fetching asignaturas:", err);
            error = err.message;
        } finally {
            loading = false;
        }
    }

    onMount(() => {
        fetchAsignaturas();
    });
</script>

<div
    class="w-full bg-white/50 dark:bg-gray-800/50 backdrop-blur-md rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50"
>
    <div class="flex items-center justify-between mb-6">
        <h2
            class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3"
        >
            <span
                class="p-2 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-lg"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                </svg>
            </span>
            Asignación Académica {currentYear}
        </h2>

        <button
            onclick={fetchAsignaturas}
            class="p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/50"
            title="Recargar"
        >
            <svg
                class="w-5 h-5 {loading ? 'animate-spin' : ''}"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
            </svg>
        </button>
    </div>

    {#if loading}
        <div class="flex justify-center items-center py-12">
            <div class="relative w-16 h-16">
                <div
                    class="absolute inset-0 border-4 border-indigo-100 dark:border-indigo-900/30 rounded-full"
                ></div>
                <div
                    class="absolute inset-0 border-4 border-indigo-500 rounded-full border-t-transparent animate-spin"
                ></div>
            </div>
        </div>
    {:else if error}
        <div
            class="p-4 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-xl border border-rose-100 dark:border-rose-900/30 flex items-center gap-3"
        >
            <svg
                class="w-5 h-5 shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <span>{error}</span>
        </div>
    {:else if asignaturas.length === 0}
        <div
            class="text-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-gray-100 dark:border-gray-700/30"
        >
            <svg
                class="w-12 h-12 mx-auto mb-3 opacity-50"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
            </svg>
            <p>No se encontraron asignaturas para el año {currentYear}</p>
        </div>
    {:else}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700"
                    >
                        <th class="px-4 py-3">Sede</th>
                        <th class="px-4 py-3 text-center">Nivel</th>
                        <th class="px-4 py-3 text-center">Número</th>
                        <th class="px-4 py-3">Asignatura</th>
                        <th class="px-4 py-3">Docente</th>
                        <th class="px-4 py-3 text-center">Year</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    {#each asignaturas as asig, i}
                        <tr
                            class="group hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors text-sm"
                            in:fly={{ y: 20, delay: i * 30, duration: 400 }}
                        >
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-400"
                            >
                                {asig.sede}
                            </td>
                            <td
                                class="px-4 py-3 text-center font-medium text-gray-900 dark:text-white"
                            >
                                <span
                                    class="px-2 py-1 rounded bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 font-bold text-xs"
                                >
                                    {asig.nivel}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-center text-gray-600 dark:text-gray-400"
                            >
                                {asig.numero}
                            </td>
                            <td
                                class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"
                            >
                                {asig.asignatura}
                            </td>
                            <td
                                class="px-4 py-3 text-gray-600 dark:text-gray-300"
                            >
                                {#if asig.docente_nombre}
                                    {asig.docente_nombre}
                                {:else}
                                    <span class="italic text-gray-400"
                                        >{asig.docente}</span
                                    >
                                {/if}
                            </td>
                            <td
                                class="px-4 py-3 text-center text-gray-500 dark:text-gray-500"
                            >
                                {asig.year}
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}
</div>
