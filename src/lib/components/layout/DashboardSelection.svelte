<!-- 
DASHBOARDSELECTION.SVELTE

DESCRIPCIÓN:
Pantalla de selección inicial tras el login que permite al usuario elegir entre el Concentrador de Asignaturas o el Concentrador de Áreas. Diseñada con UX premium, responsive y con transiciones suaves.

USO:
<DashboardSelection onLogout={handleLogout} /> en App.svelte.

DEPENDENCIAS:
- Stores: concentradorType, viewMode, loadConcentradorData (storeConcentrador.ts).

RELACIONES:
- Actúa como el puente inicial entre la autenticación y la visualización de datos.
- Desencadena la carga inicial de datos configurando el tipo de vista preferido.

NOTAS DE DESARROLLO:
- Utiliza Tailwind CSS para un diseño moderno y responsive.
- Implementa animaciones de entrada 'animate-fade-in' para mejorar la experiencia percibida.
- Los iconos y tarjetas tienen efectos hover avanzados (transform, shadow-lg, glow).
-->

<script lang="ts">
    import {
        concentradorType,
        viewMode,
        loadConcentradorData,
    } from "../../storeConcentrador";

    let { onLogout, user = null } = $props<{
        onLogout?: () => void;
        user?: any;
    }>();

    /**
     * Establece el tipo de concentrador, el modo de vista por defecto y dispara la carga de datos.
     * @param type - El tipo de concentrador seleccionado ('asignaturas' | 'areas' | 'registro').
     */
    function handleSelection(type: "asignaturas" | "areas" | "registro") {
        concentradorType.set(type);
        viewMode.set("table-view"); // Por defecto iniciamos en vista de tabla tras la selección
        if (type !== "registro") {
            loadConcentradorData();
        }
    }

    function handleLogout() {
        if (onLogout) onLogout();
    }
</script>

<div
    class="flex flex-col items-center justify-center min-h-[85vh] py-12 px-4 sm:px-6 lg:px-8 animate-fade-in relative"
>
    <!-- Botón de Salir -->
    {#if onLogout}
        <div class="absolute top-0 right-0 py-4 sm:py-0">
            <button
                onclick={handleLogout}
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-gray-500 hover:text-rose-500 dark:text-gray-400 dark:hover:text-rose-400 bg-white/50 dark:bg-gray-800/50 backdrop-blur-md rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300 group"
            >
                <span class="group-hover:-translate-x-1 transition-transform"
                    >←</span
                >
                Salir / Logout
            </button>
        </div>
    {/if}

    <div class="max-w-6xl w-full text-white">
        <!-- Título Principal -->
        <div class="text-center mb-12">
            <h1
                class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight"
            >
                <span
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent"
                >
                    {user?.nombres ? `¡Hola, ${user.nombres}!` : "Bienvenido"} ¿Qué
                    deseas realizar hoy?
                </span>
            </h1>
            <p
                class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed"
            >
                Selecciona una de las opciones principales para comenzar a
                gestionar tu información académica.
            </p>
        </div>

        <!-- Grid de Tarjetas -->
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-2 sm:px-0"
        >
            <!-- Tarjeta: Concentrador de Asignaturas -->
            <button
                onclick={() => handleSelection("asignaturas")}
                class="group relative flex flex-col items-center p-8 bg-blue-50/50 hover:bg-white dark:bg-blue-900/10 dark:hover:bg-gray-800 rounded-[2.5rem] border-2 border-blue-100 dark:border-blue-900/30 hover:border-blue-400 dark:hover:border-blue-500 shadow-lg hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500 transform hover:scale-[1.03] overflow-hidden text-center"
            >
                <!-- Decoración de fondo -->
                <div
                    class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-colors"
                ></div>

                <div
                    class="w-20 h-20 bg-blue-500 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-xl shadow-blue-500/20 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500"
                >
                    📚
                </div>

                <h2
                    class="text-2xl font-bold text-blue-900 dark:text-blue-300 mb-2"
                >
                    Asignaturas
                </h2>
                <p
                    class="text-blue-700/70 dark:text-blue-400/70 mb-6 leading-relaxed"
                >
                    Consulta y descarga las notas detalladas organizadas por
                    asignaturas de todos tus grupos.
                </p>

                <div
                    class="mt-auto flex items-center gap-2 font-bold text-blue-600 dark:text-blue-400"
                >
                    Acceder <span
                        class="group-hover:translate-x-2 transition-transform duration-300"
                        >→</span
                    >
                </div>
            </button>

            <!-- Tarjeta: Registro de Notas -->
            <button
                onclick={() => handleSelection("registro")}
                class="group relative flex flex-col items-center p-8 bg-indigo-50/50 hover:bg-white dark:bg-indigo-900/10 dark:hover:bg-gray-800 rounded-[2.5rem] border-2 border-indigo-100 dark:border-indigo-900/30 hover:border-indigo-400 dark:hover:border-indigo-500 shadow-lg hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 transform hover:scale-[1.03] overflow-hidden text-center"
            >
                <!-- Decoración de fondo -->
                <div
                    class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl group-hover:bg-indigo-500/20 transition-colors"
                ></div>

                <div
                    class="w-20 h-20 bg-indigo-600 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-xl shadow-indigo-600/20 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500"
                >
                    📝
                </div>

                <h2
                    class="text-2xl font-bold text-indigo-900 dark:text-indigo-300 mb-2"
                >
                    Registrar Notas
                </h2>
                <p
                    class="text-indigo-700/70 dark:text-indigo-400/70 mb-6 leading-relaxed"
                >
                    Ingresa y actualiza las calificaciones periódicas de tus
                    estudiantes de manera rápida y segura.
                </p>

                <div
                    class="mt-auto flex items-center gap-2 font-bold text-indigo-600 dark:text-indigo-400"
                >
                    Ingresar <span
                        class="group-hover:translate-x-2 transition-transform duration-300"
                        >→</span
                    >
                </div>
            </button>

            <!-- Tarjeta: Concentrador de Áreas -->
            <button
                onclick={() => handleSelection("areas")}
                class="group relative flex flex-col items-center p-8 bg-emerald-50/50 hover:bg-white dark:bg-emerald-900/10 dark:hover:bg-gray-800 rounded-[2.5rem] border-2 border-emerald-100 dark:border-emerald-900/30 hover:border-emerald-400 dark:hover:border-emerald-500 shadow-lg hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500 transform hover:scale-[1.03] overflow-hidden text-center"
            >
                <!-- Decoración de fondo -->
                <div
                    class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-colors"
                ></div>

                <div
                    class="w-20 h-20 bg-emerald-500 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-xl shadow-emerald-500/20 group-hover:scale-110 group-hover:-rotate-3 transition-all duration-500"
                >
                    🧩
                </div>

                <h2
                    class="text-2xl font-bold text-emerald-900 dark:text-emerald-300 mb-2"
                >
                    Áreas Académicas
                </h2>
                <p
                    class="text-emerald-700/70 dark:text-emerald-400/70 mb-6 leading-relaxed"
                >
                    Revisa el rendimiento agrupado por áreas del conocimiento
                    para una vista estratégica.
                </p>

                <div
                    class="mt-auto flex items-center gap-2 font-bold text-emerald-600 dark:text-emerald-400"
                >
                    Acceder <span
                        class="group-hover:translate-x-2 transition-transform duration-300"
                        >→</span
                    >
                </div>
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
