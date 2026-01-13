<!-- 
SUBJECTCARD.SVELTE

DESCRIPCIÓN:
Componente que renderiza una tarjeta de curso individual en el menú de registro.
Muestra el grado, iconos dinámicos y botones para cada asignatura.

PROPS:
- item: RegistrationMenuItem (Datos del curso/grado)
- i: number (Índice para la animación)
- getFullSubjectName: Function (Traducción de nombres)
- getSubjectIcon: Function (Ruta del icono)

LLAMADO POR:
- RegistrationMenu.svelte
-->

<script lang="ts">
    import { fly } from "svelte/transition";
    import type { RegistrationMenuItem } from "../../../types";

    let { item, i, getFullSubjectName, getSubjectIcon, onCourseClick } =
        $props<{
            item: RegistrationMenuItem;
            i: number;
            getFullSubjectName: (name: string) => string;
            getSubjectIcon: (name: string) => string;
            onCourseClick: (data: {
                item: RegistrationMenuItem;
                asignatura: string;
            }) => void;
        }>();

    function handleCourseClick(asignatura: string) {
        if (onCourseClick) {
            onCourseClick({ item, asignatura });
        }
    }
</script>

<div
    class="course-card group relative bg-white/60 dark:bg-gray-900/60 backdrop-blur-xl p-6 rounded-[2.5rem] border border-white dark:border-gray-800/50 hover:border-purple-500/50 transition-all duration-700 hover:shadow-[0_40px_80px_-15px_rgba(168,85,247,0.25)] flex flex-col justify-between"
    in:fly={{ y: 20, duration: 500, delay: i * 50 }}
>
    <div>
        <div class="flex justify-between items-start mb-6 w-full">
            <div class="flex flex-col gap-2 items-start max-w-[70%]">
                <span
                    class="text-[8px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-widest"
                >
                    Asignatura(s)
                </span>
                <span
                    class="text-[8px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider line-clamp-1"
                >
                    {item.asignaturas
                        .map((a: any) => getFullSubjectName(a.asignatura))
                        .join(" • ")}
                </span>
            </div>
            <span
                class="px-3 py-1 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 text-[9px] font-black uppercase tracking-[0.2em] border border-purple-500/20 whitespace-nowrap"
            >
                {item.grado}
            </span>
        </div>

        <div
            class="flex justify-center mb-8 relative min-h-[120px] items-center"
        >
            <div
                class="absolute inset-0 bg-purple-500/5 blur-3xl rounded-full scale-110 group-hover:bg-purple-500/10 transition-colors"
            ></div>

            <div
                class="flex flex-wrap justify-center items-center gap-4 relative z-10 w-full px-4"
            >
                {#each item.asignaturas as asig}
                    {@const icon = getSubjectIcon(asig.asignatura)}
                    {#if icon}
                        <img
                            src={icon}
                            alt="Icono"
                            class="object-contain filter drop-shadow-2xl group-hover:scale-110 group-hover:-rotate-3 transition-all duration-700
                                {item.asignaturas.length === 1
                                ? 'w-24 h-24 md:w-28 md:h-28'
                                : item.asignaturas.length === 2
                                  ? 'w-16 h-16 md:w-20 md:h-20'
                                  : 'w-12 h-12 md:w-14 md:h-14'}"
                        />
                    {/if}
                {/each}
            </div>
        </div>
    </div>

    <div class="space-y-3.5">
        {#each item.asignaturas as asig}
            <button
                onclick={() => handleCourseClick(asig.asignatura)}
                class="w-full group/btn relative flex items-center justify-between p-5 rounded-[1.8rem] bg-indigo-50/30 dark:bg-white/5 border border-transparent hover:border-purple-500/20 hover:bg-white dark:hover:bg-white/10 transition-all duration-500 overflow-hidden shadow-sm shadow-indigo-500/5"
            >
                <span
                    class="relative z-10 font-bold text-gray-700 dark:text-gray-300 text-xs md:text-sm group-hover/btn:text-purple-600 transition-colors truncate pr-2"
                >
                    {getFullSubjectName(asig.asignatura)}
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

<style>
    .course-card {
        cursor: pointer;
        outline: none;
    }

    .course-card:hover {
        transform: translateY(-16px) scale(1.02);
    }
</style>
