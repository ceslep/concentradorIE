<!-- 
LOGOUTCONFIRMDIALOG.SVELTE

DESCRIPCIÓN:
Diálogo de confirmación premium para el cierre de sesión. Utiliza glassmorphism, 
animaciones suaves y un diseño ultramoderno para una experiencia de usuario superior.

USO:
<LogoutConfirmDialog bind:showDialog={showLogoutDialog} onConfirm={handleLogout} />

LLAMADO POR: 
- App.svelte
-->

<script lang="ts">
    import { fade, scale } from "svelte/transition";
    import { theme } from "../../../themeStore";

    let { showDialog = $bindable(false), onConfirm } = $props<{
        showDialog?: boolean;
        onConfirm: () => void;
    }>();

    function closeDialog() {
        showDialog = false;
    }

    function handleConfirm() {
        onConfirm();
        closeDialog();
    }

    import DevLabel from "../../shared/DevLabel.svelte";
</script>

{#if showDialog}
    <div
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4 font-sans"
        transition:fade={{ duration: 200 }}
    >
        <DevLabel name="LogoutConfirmDialog.svelte" />
        <!-- Backdrop with enhanced blur -->
        <!-- svelte-ignore a11y_no_static_element_interactions -->
        <div
            class="absolute inset-0 bg-gray-900/60 backdrop-blur-md"
            onclick={closeDialog}
            onkeydown={(e) => e.key === "Escape" && closeDialog()}
        ></div>

        <!-- Dialog Card -->
        <div
            class="relative w-full max-w-md bg-white dark:bg-[#1a1b1e] rounded-[2.5rem] shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden transform transition-all"
            transition:scale={{ start: 0.9, duration: 250 }}
        >
            <!-- Decorative element -->
            <div
                class="absolute -top-24 -right-24 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl"
            ></div>
            <div
                class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"
            ></div>

            <div class="relative p-8 flex flex-col items-center text-center">
                <!-- Icon section -->
                <div class="mb-6 relative">
                    <div
                        class="absolute inset-0 bg-rose-500/20 rounded-full blur-xl animate-pulse"
                    ></div>
                    <div
                        class="relative w-20 h-20 bg-gradient-to-br from-rose-500 to-pink-600 rounded-3xl flex items-center justify-center shadow-lg shadow-rose-500/30 rotate-3 transition-transform hover:rotate-0"
                    >
                        <span
                            class="material-symbols-rounded text-4xl text-white"
                            >logout</span
                        >
                    </div>
                </div>

                <h3
                    class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent mb-3"
                    style="font-family: var(--font-heading);"
                >
                    ¿Cerrar sesión ahora?
                </h3>

                <p
                    class="text-gray-600 dark:text-gray-400 mb-8 leading-relaxed"
                >
                    Estás a punto de salir del sistema. Asegúrate de haber
                    guardado todos tus cambios antes de continuar.
                </p>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 w-full">
                    <button
                        onclick={closeDialog}
                        class="flex-1 px-6 py-3.5 rounded-2xl font-bold text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200 active:scale-95"
                    >
                        Cancelar
                    </button>

                    <button
                        onclick={handleConfirm}
                        class="flex-1 px-6 py-3.5 rounded-2xl font-bold text-sm text-white bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 shadow-lg shadow-rose-500/25 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2"
                    >
                        Saldré ahora
                        <span class="material-symbols-rounded text-lg"
                            >arrow_forward</span
                        >
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}

<style>
    /* Custom styles if needed beyond Tailwind */
</style>
