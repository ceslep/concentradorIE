<!-- 
CONFIGURATIONMENU.SVELTE

DESCRIPCIÓN:
Componente para la configuración de Asignaturas y Áreas.

USO:
<ConfigurationMenu {user} /> en App.svelte.
-->

<script lang="ts">
    import { fade, fly } from "svelte/transition";
    import { theme } from "../../../themeStore";
    import { resetToDashboard } from "../../../storeConcentrador";
    import DevLabel from "../../shared/DevLabel.svelte";
    import AsignaturasList from "./AsignaturasList.svelte";

    let { user, onLogout } = $props<{
        user: any;
        onLogout?: () => void;
    }>();

    function handleLogout() {
        if (onLogout) onLogout();
    }
</script>

<div
    class="configuration-dashboard w-full max-w-7xl mx-auto py-8 md:py-12 px-4 md:px-8 min-h-screen relative"
>
    <DevLabel name="ConfigurationAsignaturasMenu.svelte" />
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
        </div>

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

    <div class="py-6" in:fade>
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">
                Configuración
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">
                Gestión de Asignaturas y Áreas Académicas
            </p>
        </div>

        <AsignaturasList />
    </div>
</div>

<style>
    .configuration-dashboard {
        background: radial-gradient(
                circle at 10% 10%,
                rgba(59, 130, 246, 0.1) 0%,
                transparent 40%
            ),
            radial-gradient(
                circle at 90% 90%,
                rgba(168, 85, 247, 0.1) 0%,
                transparent 40%
            );
    }
</style>
