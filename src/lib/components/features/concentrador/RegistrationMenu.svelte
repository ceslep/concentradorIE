<!-- 
REGISTRATIONMENU.SVELTE

DESCRIPCIÓN:
Componente de "Registro de Calificaciones" de próxima generación.
Implementa un Dashboard futurista con lógica de datos encadenada:
1. Intenta obtener el menú específico del docente (generarMenu.php).
2. Si falla o está vacío, intenta obtener información general (getInfoDocentes.php).

USO:
<RegistrationMenu {user} /> en App.svelte.

LLAMADO POR:
- App.svelte
-->

<script lang="ts">
    import { onMount } from "svelte";
    import { fetchRegistrationMenu, fetchInfoDocentes } from "../../../api";
    import type { RegistrationMenuItem, TeacherInfo } from "../../../types";
    import { asignturastoN } from "../../../../constants";
    import { fade, scale, fly } from "svelte/transition";
    import { quintOut } from "svelte/easing";
    import { theme } from "../../../themeStore";
    import { resetToDashboard, accesoTotal } from "../../../storeConcentrador";
    import TeacherProfileDetail from "./TeacherProfileDetail.svelte";
    import DocenteAsignaturaSelection from "./DocenteAsignaturaSelection.svelte";
    import Loader from "../../shared/Loader.svelte";

    let { user, onLogout } = $props<{
        user: any;
        onLogout?: () => void;
    }>();

    let items = $state<RegistrationMenuItem[]>([]);
    let teachers = $state<TeacherInfo[]>([]);
    let loading = $state(true);
    let error = $state<string | null>(null);
    let fallbackMode = $state(false);
    let searchQuery = $state("");

    const currentYear = import.meta.env.DEV
        ? "2025"
        : new Date().getFullYear().toString();

    let selectedTeacherId = $state<string | null>(null);
    let isProfileOpen = $state(false);

    // Derived filtered teachers for fallback mode (Svelte 5 Rune)
    let filteredTeachers = $derived(
        teachers.filter(
            (t) =>
                t.nombres.toLowerCase().includes(searchQuery.toLowerCase()) ||
                t.identificacion.includes(searchQuery) ||
                t.sede.toLowerCase().includes(searchQuery.toLowerCase()),
        ),
    );

    /**
     * Obtiene la ruta del icono de la asignatura
     */
    function getSubjectIcon(shortName: string): string {
        const found = asignturastoN.find((a) => a.asignatura === shortName);
        return found ? found.ruta : "";
    }

    /**
     * Traduce el nombre corto de la asignatura a su versión completa
     */
    function getFullSubjectName(shortName: string): string {
        const found = asignturastoN.find((a) => a.asignatura === shortName);
        return found ? found.nombrec : shortName;
    }

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

    async function viewTeacherSubjects(teacher: TeacherInfo) {
        loading = true;
        error = null;
        try {
            const data = await fetchRegistrationMenu(
                teacher.identificacion,
                currentYear,
            );
            if (data && data.length > 0) {
                items = data;
                fallbackMode = false;
            } else {
                // Mantener en modo fallback para permitir seleccionar otro docente
                error = `El docente ${teacher.nombres} no tiene asignaturas registradas para ${currentYear}. Por favor selecciona otro docente.`;
                fallbackMode = true;
            }
        } catch (e: any) {
            error = e.message || "Error al cargar las asignaturas del docente.";
            fallbackMode = true; // Volver a lista de docentes en caso de error
        } finally {
            loading = false;
        }
    }

    function handleLogout() {
        if (onLogout) onLogout();
    }

    import DevLabel from "../../shared/DevLabel.svelte";
</script>

<div
    class="registration-dashboard w-full max-w-7xl mx-auto py-8 md:py-12 px-4 md:px-8 min-h-screen relative"
>
    <DevLabel name="RegistrationMenu.svelte" />
    <!-- Independent Navigation Bar -->
    <nav
        class="flex flex-col sm:flex-row items-center justify-between mb-8 md:mb-12 bg-white/40 dark:bg-gray-800/40 backdrop-blur-xl p-4 md:p-6 rounded-[2rem] md:rounded-[3rem] border border-white/20 dark:border-gray-700/50 shadow-xl shadow-indigo-500/5 gap-4"
        in:fly={{ y: -20, duration: 600 }}
    >
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button
                onclick={resetToDashboard}
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

            {#if $accesoTotal && !fallbackMode}
                <button
                    onclick={() => {
                        items = [];
                        fallbackMode = true;
                        selectedTeacherId = null;
                        searchQuery = "";
                    }}
                    class="flex items-center gap-2 px-5 md:px-6 py-2.5 md:py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl transition-all font-bold text-sm shadow-lg shadow-emerald-600/20 active:scale-95 group"
                >
                    <svg
                        class="w-4 h-4 group-hover:-translate-x-1 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    <span class="hidden md:inline">Volver a</span> Docentes
                </button>
            {/if}
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
                onclick={theme.toggle}
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
                onclick={handleLogout}
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
                        onclick={loadData}
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
        <Loader
            message={fallbackMode
                ? "Cargando lista de docentes..."
                : "Cargando asignaturas..."}
            size="lg"
        />
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
                onclick={loadData}
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
                        onclick={() => handleTeacherClick(teacher)}
                        onkeydown={(e) =>
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
                                onclick={(e) => {
                                    e.stopPropagation();
                                    viewTeacherSubjects(teacher);
                                }}
                                class="w-full py-3.5 bg-indigo-600/10 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 rounded-[1.4rem] font-black text-[9px] uppercase tracking-[0.2em] transition-all hover:bg-indigo-600 hover:text-white group-hover:shadow-xl group-hover:shadow-indigo-500/20 flex items-center justify-center gap-2 active:scale-95 border border-indigo-500/20 hover:border-transparent"
                            >
                                Ver Asignaturas <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    ><path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                    /></svg
                                >
                            </button>

                            <button
                                onclick={(e) => {
                                    e.stopPropagation();
                                    handleTeacherClick(teacher);
                                }}
                                class="w-full py-3.5 bg-gray-950 dark:bg-gray-800 text-white rounded-[1.4rem] font-black text-[9px] uppercase tracking-[0.2em] transition-all hover:scale-[1.03] group-hover:shadow-2xl group-hover:shadow-indigo-500/40 flex items-center justify-center gap-2 active:scale-95"
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
            {:else if items.length > 0}
                {#each items as item, i}
                    <DocenteAsignaturaSelection
                        {item}
                        {i}
                        {getFullSubjectName}
                        {getSubjectIcon}
                        onCourseClick={(detail) =>
                            handleCourseClick(detail.item, detail.asignatura)}
                    />
                {/each}
            {:else if !fallbackMode && items.length === 0}
                <div
                    class="col-span-full py-20 flex flex-col items-center justify-center text-center space-y-6"
                    in:fade
                >
                    <div
                        class="w-32 h-32 bg-gray-100 dark:bg-gray-800/50 rounded-full flex items-center justify-center mb-4 border border-gray-200 dark:border-gray-700/50 shadow-inner"
                    >
                        <svg
                            class="w-16 h-16 text-gray-400 dark:text-gray-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3
                            class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter"
                        >
                            Sin Asignaturas
                        </h3>
                        <p
                            class="text-gray-500 dark:text-gray-400 text-sm max-w-sm mx-auto"
                        >
                            No se encontraron cursos registrados para este
                            docente en el año {currentYear}.
                        </p>
                    </div>
                    <button
                        onclick={() => {
                            items = [];
                            fallbackMode = true;
                        }}
                        class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl font-bold text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-3"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Volver a Docentes
                    </button>
                </div>
            {/if}
        </div>
    {/if}

    {#if isProfileOpen && selectedTeacherId}
        <TeacherProfileDetail
            teacherId={selectedTeacherId}
            isOpen={isProfileOpen}
            onClose={() => {
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

    .teacher-card {
        cursor: pointer;
        outline: none;
    }

    .teacher-card:hover {
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
