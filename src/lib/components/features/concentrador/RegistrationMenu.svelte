<!-- 
REGISTRATIONMENU.SVELTE

DESCRIPCIÓN:
Componente de "Registro de Calificaciones" de próxima generación.
Implementa un Dashboard futurista con lógica de datos encadenada:
1. Intenta obtener el menú específico del docente (generarMenu.php).
2. Si falla o está vacío, intenta obtener información general (getInfoDocentes.php).

USO:
<RegistrationMenu {user} /> en App.svelte.
-->

<script lang="ts">
    import { onMount, createEventDispatcher } from "svelte";
    import { fetchRegistrationMenu, fetchInfoDocentes } from "../../../api";
    import type { RegistrationMenuItem, TeacherInfo } from "../../../types";
    import { fade, scale, fly } from "svelte/transition";
    import { quintOut } from "svelte/easing";
    import { theme } from "../../../themeStore";
    import { resetToDashboard } from "../../../storeConcentrador";
    import TeacherProfileDetail from "./TeacherProfileDetail.svelte";

    export let user: any;
    const dispatch = createEventDispatcher();

    let items: RegistrationMenuItem[] = [];
    let teachers: TeacherInfo[] = [];
    let loading = true;
    let error: string | null = null;
    let fallbackMode = false;
    let searchQuery = "";

    const currentYear = new Date().getFullYear().toString();

    let selectedTeacherId: string | null = null;
    let isProfileOpen = false;

    // Derived filtered teachers for fallback mode
    $: filteredTeachers = teachers.filter(
        (t) =>
            t.nombres.toLowerCase().includes(searchQuery.toLowerCase()) ||
            t.identificacion.includes(searchQuery) ||
            t.sede.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    async function loadData() {
        loading = true;
        error = null;
        try {
            const docenteIdRaw = user?.id || user?.identificacion || user;
            const docenteId =
                docenteIdRaw &&
                (typeof docenteIdRaw === "string" ||
                    typeof docenteIdRaw === "number")
                    ? String(docenteIdRaw)
                    : null;

            // Si tenemos un ID, intentamos cargar su menú específico
            if (docenteId) {
                items = await fetchRegistrationMenu(docenteId, currentYear);
            }

            // Si no hay ítems (o no hay docenteId), entramos en modo fallback (lista de todos los docentes)
            if (items.length === 0) {
                fallbackMode = true;
                teachers = await fetchInfoDocentes();
            } else {
                fallbackMode = false;
            }
        } catch (e: any) {
            error = e.message || "Error al conectar con el servidor";
        } finally {
            loading = false;
        }
    }

    onMount(loadData);

    function handleCourseClick(item: RegistrationMenuItem, asignatura: string) {
        console.log(
            `Navigating to grades registration: ${item.gradoA} - ${asignatura}`,
        );
    }

    function handleTeacherClick(teacher: TeacherInfo) {
        selectedTeacherId = teacher.identificacion;
        isProfileOpen = true;
    }

    function handleLogout() {
        dispatch("logout");
    }
</script>

<div
    class="registration-dashboard w-full max-w-7xl mx-auto py-8 md:py-12 px-4 md:px-8 min-h-screen"
>
    <!-- Independent Navigation Bar -->
    <nav
        class="flex flex-col sm:flex-row items-center justify-between mb-8 md:mb-12 bg-white/40 dark:bg-gray-800/40 backdrop-blur-xl p-4 md:p-6 rounded-[2rem] md:rounded-[3rem] border border-white/20 dark:border-gray-700/50 shadow-xl shadow-indigo-500/5 gap-4"
        in:fly={{ y: -20, duration: 600 }}
    >
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button
                on:click={resetToDashboard}
                class="flex items-center gap-2 px-5 md:px-6 py-2.5 md:py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl transition-all font-bold text-sm shadow-lg shadow-indigo-600/20 active:scale-95 group"
            >
                <svg
                    class="w-4 h-4 group-hover:-translate-x-1 transition-transform"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    /></svg
                >
                <span class="hidden md:inline">Volver al</span> Dashboard
            </button>
        </div>

        <!-- Search Bar (Visible in fallback mode) -->
        {#if fallbackMode && !loading && !error}
            <div class="relative w-full max-w-md mx-2" in:fade>
                <span
                    class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                >
                    <svg
                        class="h-5 w-5 text-indigo-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </span>
                <input
                    type="search"
                    bind:value={searchQuery}
                    placeholder="Buscar docente o sede..."
                    class="block w-full pl-11 pr-4 py-3 bg-white/50 dark:bg-gray-700/30 border border-indigo-100 dark:border-indigo-900/40 rounded-2xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:text-white"
                />
            </div>
        {/if}

        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            <!-- Theme Toggle -->
            <button
                on:click={theme.toggle}
                class="p-3 md:p-3.5 rounded-xl bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 hover:scale-105 transition-all shadow-sm"
                title={$theme === "dark" ? "Modo Claro" : "Modo Oscuro"}
            >
                {#if $theme === "dark"}
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        ><path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                        /></svg
                    >
                {:else}
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        ><path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        /></svg
                    >
                {/if}
            </button>

            <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <!-- Logout -->
            <button
                on:click={handleLogout}
                class="p-3 md:p-3.5 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white rounded-xl transition-all shadow-inner group"
                title="Cerrar Sesión"
            >
                <svg
                    class="w-5 h-5 group-hover:scale-110 transition-transform"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    ><path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    /></svg
                >
            </button>
        </div>
    </nav>

    <!-- Header Section -->
    <header
        class="relative mb-12 md:mb-16"
        in:fly={{ y: -20, duration: 800, easing: quintOut, delay: 100 }}
    >
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-8 md:gap-12"
        >
            <div class="space-y-4">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-[9px] md:text-[11px] font-black tracking-[0.2em] uppercase"
                >
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"
                        ></span>
                        <span
                            class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"
                        ></span>
                    </span>
                    Control Académico Central
                </div>
                <h1
                    class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tighter text-gray-900 dark:text-white leading-[1.1]"
                >
                    Registro de <span
                        class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 animate-gradient-x"
                    >
                        Notas</span
                    >
                </h1>
                <p
                    class="text-base md:text-lg text-gray-500 dark:text-gray-400 max-w-2xl font-medium leading-relaxed"
                >
                    Sincronización de calificaciones para el periodo escolar.
                    {#if fallbackMode}
                        <span
                            class="text-indigo-600 dark:text-indigo-400 font-bold block mt-2 text-sm uppercase tracking-widest"
                            >→ Consola de Supervisión Docente</span
                        >
                    {/if}
                </p>
            </div>

            {#if !loading && (items.length > 0 || teachers.length > 0)}
                <div
                    class="flex items-center gap-4 md:gap-6 bg-white/60 dark:bg-gray-800/60 backdrop-blur-2xl p-3 md:p-4 rounded-[2rem] md:rounded-[3rem] border border-white/40 dark:border-gray-700/50 shadow-2xl shadow-indigo-500/10 self-start md:self-auto"
                >
                    <div
                        class="px-5 md:px-7 border-r border-gray-100 dark:border-gray-700/50"
                    >
                        <div
                            class="text-2xl md:text-3xl font-black text-indigo-600 dark:text-indigo-400 leading-none tracking-tighter"
                        >
                            {fallbackMode
                                ? filteredTeachers.length
                                : items.length}
                        </div>
                        <div
                            class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1.5"
                        >
                            {fallbackMode ? "Docentes" : "Grupos"}
                        </div>
                    </div>
                    <button
                        on:click={loadData}
                        class="p-4 md:p-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] md:rounded-[1.8rem] transition-all duration-300 shadow-xl shadow-indigo-600/30 active:scale-95 group"
                        title="Actualizar Datos"
                    >
                        <svg
                            class="w-5 h-5 md:w-6 md:h-6 group-hover:rotate-180 transition-transform duration-700"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </button>
                </div>
            {/if}
        </div>
    </header>

    {#if loading}
        <!-- Skeleton Grid -->
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8"
        >
            {#each Array(8) as _}
                <div
                    class="h-80 rounded-[3rem] md:rounded-[4rem] bg-gray-100 dark:bg-gray-800/40 animate-pulse border border-white/10"
                ></div>
            {/each}
        </div>
    {:else if error}
        <!-- Error State -->
        <div
            class="flex flex-col items-center justify-center py-32 px-6 bg-white/40 dark:bg-gray-900/40 backdrop-blur-xl rounded-[4rem] border-2 border-rose-500/10 text-center"
            in:scale
        >
            <div
                class="w-28 h-28 bg-rose-500/10 text-rose-500 rounded-[2.5rem] flex items-center justify-center text-5xl mb-10 shadow-2xl shadow-rose-500/20"
            >
                <svg
                    class="w-14 h-14"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
            </div>
            <h3 class="text-4xl font-black text-gray-900 dark:text-white mb-6">
                Error de Conexión
            </h3>
            <p
                class="text-gray-500 dark:text-gray-400 max-w-md text-lg font-medium"
            >
                {error}
            </p>
            <button
                on:click={loadData}
                class="mt-12 px-12 py-5 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl transition-all shadow-xl shadow-rose-600/30 active:scale-95"
            >
                REINTENTAR
            </button>
        </div>
    {:else if fallbackMode && filteredTeachers.length === 0}
        <!-- No Search Results -->
        <div
            class="flex flex-col items-center justify-center py-40 text-center"
            in:fade
        >
            <div class="text-7xl mb-8 opacity-20">🔎</div>
            <h3
                class="text-3xl font-black text-gray-400 dark:text-gray-600 uppercase tracking-tighter"
            >
                No hay coincidencias
            </h3>
            <p class="text-gray-500 mt-4 font-medium">
                No encontramos resultados para "{searchQuery}"
            </p>
        </div>
    {:else}
        <!-- Main Grid -->
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 pb-32"
        >
            {#if fallbackMode}
                {#each filteredTeachers as teacher (teacher.identificacion)}
                    <div
                        class="teacher-card group relative bg-white/60 dark:bg-gray-900/60 backdrop-blur-xl p-7 rounded-[2.5rem] border border-white dark:border-gray-800/50 hover:border-indigo-500/50 transition-all duration-700 hover:shadow-[0_40px_80px_-15px_rgba(99,102,241,0.25)] flex flex-col justify-between h-full"
                        in:fly={{ y: 20, duration: 500 }}
                        on:click={() => handleTeacherClick(teacher)}
                        on:keydown={(e) =>
                            e.key === "Enter" && handleTeacherClick(teacher)}
                        role="button"
                        tabindex="0"
                    >
                        <div>
                            <div class="flex items-start gap-4 mb-6">
                                <div
                                    class="w-13 h-13 rounded-[1.4rem] bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 flex items-center justify-center text-white font-black text-xl shadow-xl flex-shrink-0"
                                >
                                    {teacher.nombres.charAt(0)}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="text-[9px] font-black text-indigo-500 tracking-[0.2em] uppercase mb-0.5 truncate"
                                    >
                                        {teacher.sede}
                                    </h4>
                                    <h3
                                        class="text-lg md:text-xl font-black text-gray-950 dark:text-white leading-tight tracking-tight break-words"
                                    >
                                        {teacher.nombres}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div
                                class="flex justify-between items-center py-3.5 border-y border-gray-100 dark:border-gray-800/30"
                            >
                                <span
                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest"
                                    >ID Registro</span
                                >
                                <span
                                    class="text-xs font-bold text-gray-700 dark:text-gray-300 font-mono tracking-tighter"
                                    >{teacher.identificacion}</span
                                >
                            </div>
                            <button
                                on:click|stopPropagation={() =>
                                    handleTeacherClick(teacher)}
                                class="w-full py-3.5 bg-gray-950 dark:bg-indigo-600 text-white rounded-[1.4rem] font-black text-[9px] uppercase tracking-[0.2em] transition-all hover:scale-[1.03] group-hover:shadow-2xl group-hover:shadow-indigo-500/40 flex items-center justify-center gap-2 active:scale-95"
                            >
                                Ver Expediente <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    ><path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6"
                                    /></svg
                                >
                            </button>
                        </div>
                    </div>
                {/each}
            {:else}
                {#each items as item, i}
                    <div
                        class="course-card group relative bg-white/60 dark:bg-gray-900/60 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white dark:border-gray-800/50 hover:border-purple-500/50 transition-all duration-700 hover:shadow-[0_40px_80px_-15px_rgba(168,85,247,0.25)] flex flex-col justify-between"
                        in:fly={{ y: 20, duration: 500, delay: i * 50 }}
                    >
                        <div>
                            <div class="flex justify-between items-start mb-8">
                                <span
                                    class="px-4 py-1.5 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 text-[9px] font-black uppercase tracking-[0.2em] border border-purple-500/20"
                                >
                                    GRAD {item.grado}
                                </span>
                                <div class="text-3xl">🏛️</div>
                            </div>

                            <h3
                                class="text-2xl md:text-3xl font-black text-gray-950 dark:text-white mb-8 tracking-tighter leading-[0.9] group-hover:text-purple-600 transition-colors"
                            >
                                {item.gradoA}
                            </h3>
                        </div>

                        <div class="space-y-3.5">
                            {#each item.asignaturas as asig}
                                <button
                                    on:click={() =>
                                        handleCourseClick(
                                            item,
                                            asig.asignatura,
                                        )}
                                    class="w-full group/btn relative flex items-center justify-between p-5 rounded-[1.8rem] bg-indigo-50/30 dark:bg-white/5 border border-transparent hover:border-purple-500/20 hover:bg-white dark:hover:bg-white/10 transition-all duration-500 overflow-hidden shadow-sm shadow-indigo-500/5"
                                >
                                    <span
                                        class="relative z-10 font-bold text-gray-700 dark:text-gray-300 text-xs md:text-sm group-hover/btn:text-purple-600 transition-colors truncate pr-2"
                                    >
                                        {asig.asignatura}
                                    </span>
                                    <div
                                        class="relative z-10 w-9 h-9 rounded-xl bg-white dark:bg-gray-800 shadow-xl flex items-center justify-center transition-all duration-500 group-hover/btn:rotate-90 group-hover/btn:bg-purple-600 group-hover/btn:text-white"
                                    >
                                        <svg
                                            class="w-4.5 h-4.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </div>
                                    <div
                                        class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-purple-500 to-indigo-600 transform -translate-x-full group-hover/btn:translate-x-0 transition-transform duration-500"
                                    ></div>
                                </button>
                            {/each}
                        </div>
                    </div>
                {/each}
            {/if}
        </div>
    {/if}

    {#if isProfileOpen && selectedTeacherId}
        <TeacherProfileDetail
            teacherId={selectedTeacherId}
            isOpen={isProfileOpen}
            on:close={() => {
                isProfileOpen = false;
                selectedTeacherId = null;
            }}
        />
    {/if}
</div>

<style>
    .registration-dashboard {
        background: radial-gradient(
                circle at 10% 10%,
                rgba(99, 102, 241, 0.1) 0%,
                transparent 40%
            ),
            radial-gradient(
                circle at 90% 90%,
                rgba(168, 85, 247, 0.1) 0%,
                transparent 40%
            ),
            radial-gradient(
                circle at 50% 50%,
                rgba(236, 72, 153, 0.05) 0%,
                transparent 70%
            );
    }

    .teacher-card,
    .course-card {
        cursor: pointer;
        outline: none;
    }

    .teacher-card:hover,
    .course-card:hover {
        transform: translateY(-16px) scale(1.02);
    }

    /* Custom Scrollbar for independent context */
    :global(body) {
        scrollbar-gutter: stable;
    }

    @keyframes gradient-x {
        0%,
        100% {
            background-size: 200% 200%;
            background-position: left center;
        }
        50% {
            background-size: 200% 200%;
            background-position: right center;
        }
    }
    .animate-gradient-x {
        animation: gradient-x 15s ease infinite;
    }
</style>
