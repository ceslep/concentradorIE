<!-- 
REGISTRATIONMENU.SVELTE

DESCRIPCIÓN:
Componente que renderiza dinámicamente las tarjetas de cursos y asignaturas para el registro de notas.
Utiliza la API generarMenu.php para obtener los datos del docente autenticado.

USO:
<RegistrationMenu {user} /> en App.svelte cuando concentradorType === 'registro'.

NOTAS DE DESARROLLO:
- Diseño premium con efectos de vidrio (glassmorphism) y gradientes.
- Microinteracciones responsivas y accesibles.
- Soporte total para modo claro/oscuro.
-->

<script lang="ts">
    import { onMount } from "svelte";
    import {
        fetchRegistrationMenu,
        type RegistrationMenuItem,
    } from "../../../api";
    import { fade, scale } from "svelte/transition";

    export let user: any;

    let items: RegistrationMenuItem[] = [];
    let loading = true;
    let error: string | null = null;
    let hoveredIndex: number | null = null;

    const currentYear = new Date().getFullYear().toString();

    onMount(async () => {
        try {
            const docenteId = user?.id || user?.identificacion;
            if (docenteId) {
                items = await fetchRegistrationMenu(docenteId, currentYear);
            } else {
                error = "Usuario no identificado.";
            }
        } catch (e: any) {
            error = e.message || "Error al cargar el menú de registro.";
        } finally {
            loading = false;
        }
    });

    function handleCardClick(item: RegistrationMenuItem, asignatura: string) {
        console.log(`Registrar notas para: ${item.gradoA} - ${asignatura}`);
        // Aquí se dispararía la navegación o apertura del módulo de registro específico
    }
</script>

<div
    class="registration-menu-container w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
>
    <!-- Encabezado de Sección -->
    <div
        class="mb-10 text-center sm:text-left flex flex-col sm:flex-row sm:items-end justify-between gap-4"
        in:fade
    >
        <div>
            <h2
                class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3"
            >
                <span
                    class="p-2 bg-indigo-600 rounded-xl text-white shadow-lg shadow-indigo-600/20"
                    >📝</span
                >
                Menú de Registro
            </h2>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                Selecciona un grupo para comenzar el registro de calificaciones
                de {currentYear}.
            </p>
        </div>

        {#if !loading && items.length > 0}
            <div
                class="px-4 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-md rounded-2xl border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 shadow-sm"
            >
                Total: <span
                    class="text-indigo-600 dark:text-indigo-400 font-bold"
                    >{items.length}</span
                > grupos asignados
            </div>
        {/if}
    </div>

    {#if loading}
        <!-- Loading Grid -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
            {#each Array(8) as _}
                <div
                    class="h-48 rounded-[2rem] bg-gray-200 dark:bg-gray-800 animate-pulse"
                ></div>
            {/each}
        </div>
    {:else if error}
        <!-- Error State -->
        <div
            class="flex flex-col items-center justify-center py-20 px-6 glass-effect rounded-[2.5rem] border-2 border-rose-100 dark:border-rose-900/30 text-center"
            in:scale
        >
            <div
                class="w-16 h-16 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center text-3xl mb-4"
            >
                ⚠️
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                Oops! Algo salió mal
            </h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md">{error}</p>
            <button
                on:click={() => window.location.reload()}
                class="mt-6 px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-rose-600/20"
            >
                Reintentar
            </button>
        </div>
    {:else if items.length === 0}
        <!-- Empty State -->
        <div
            class="flex flex-col items-center justify-center py-20 px-6 glass-effect rounded-[2.5rem] border-2 border-indigo-100 dark:border-indigo-900/30 text-center"
            in:fade
        >
            <div
                class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center text-3xl mb-4"
            >
                🔍
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                No se encontraron grupos
            </h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md">
                No tienes asignaciones registradas para el año {currentYear}.
            </p>
        </div>
    {:else}
        <!-- Data Grid -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
            {#each items as item, i}
                <div
                    class="card-group group relative bg-white dark:bg-gray-900/40 rounded-[2rem] border-2 border-transparent hover:border-indigo-500/40 dark:hover:border-indigo-400/40 shadow-xl shadow-gray-200/50 dark:shadow-none hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 overflow-hidden"
                    on:mouseenter={() => (hoveredIndex = i)}
                    on:mouseleave={() => (hoveredIndex = null)}
                    role="listitem"
                    in:fade={{ delay: i * 50 }}
                >
                    <!-- Header Tarjeta -->
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between mb-4">
                            <span
                                class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-black rounded-lg uppercase tracking-wider"
                            >
                                GRADO {item.grado}
                            </span>
                            <div
                                class="text-2xl opacity-40 group-hover:opacity-100 transition-opacity"
                            >
                                🏫
                            </div>
                        </div>

                        <h3
                            class="text-2xl font-black text-gray-900 dark:text-white leading-none"
                        >
                            {item.gradoA}
                        </h3>
                    </div>

                    <!-- Lista de Asignaturas -->
                    <div class="px-4 pb-6">
                        <div class="flex flex-col gap-2">
                            {#each item.asignaturas as asig}
                                <button
                                    on:click={() =>
                                        handleCardClick(item, asig.asignatura)}
                                    class="w-full text-left p-3 rounded-2xl bg-gray-50/50 dark:bg-white/5 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 transition-all duration-300 flex items-center justify-between group/btn shadow-sm hover:shadow-md"
                                    aria-label="Registrar notas para {asig.asignatura}"
                                >
                                    <span
                                        class="font-bold text-sm truncate pr-2"
                                        >{asig.asignatura}</span
                                    >
                                    <span
                                        class="text-lg opacity-0 group-hover/btn:opacity-100 transition-opacity"
                                        >→</span
                                    >
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- Decoración inferior -->
                    <div
                        class="absolute bottom-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"
                    ></div>
                </div>
            {/each}
        </div>
    {/if}
</div>

<style>
    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    :global(.dark) .glass-effect {
        background: rgba(17, 24, 39, 0.7);
    }

    .card-group {
        animation: cardEnter 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
    }

    @keyframes cardEnter {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
