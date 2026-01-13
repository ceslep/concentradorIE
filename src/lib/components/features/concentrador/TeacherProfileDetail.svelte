<script lang="ts">
    import { onMount, createEventDispatcher } from "svelte";
    import { fly, fade, scale } from "svelte/transition";
    import { fetchTeacherDetail } from "../../../api";
    import type { TeacherDetail } from "../../../types";

    export let teacherId: string;
    export let isOpen: boolean = false;

    const dispatch = createEventDispatcher<{ close: void }>();

    let loading = true;
    let error: string | null = null;
    let detail: TeacherDetail | null = null;
    let revealedFields: Record<string, boolean> = {
        pass: false,
        maestra: false,
        clave_acceso: false,
    };

    onMount(async () => {
        if (teacherId) {
            await loadDetail();
        }
    });

    async function loadDetail() {
        loading = true;
        error = null;
        try {
            const rawDetail = await fetchTeacherDetail(teacherId);
            // Limpiar correo de saltos de línea
            detail = {
                ...rawDetail,
                correo: rawDetail.correo.replace(/[\r\n]+/g, "").trim(),
            };
        } catch (e: any) {
            error = e.message || "Error al cargar detalles";
        } finally {
            loading = false;
        }
    }

    function toggleReveal(field: string) {
        revealedFields[field] = !revealedFields[field];
    }

    function close() {
        dispatch("close");
    }

    const toggleFields: (keyof TeacherDetail)[] = [
        "activo",
        "solocitaCodigo",
        "verEstudiantes",
        "banda",
        "acceso_total",
        "soloexcusas",
    ];

    const labels: Record<keyof TeacherDetail, string> = {
        activo: "Cuenta Activa",
        solocitaCodigo: "Solicita Código",
        verEstudiantes: "Ver Estudiantes",
        banda: "Modo Banda",
        acceso_total: "Acceso Total",
        soloexcusas: "Solo Excusas",
    } as Record<keyof TeacherDetail, string>;
</script>

{#if isOpen}
    <!-- Overlay -->
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <div
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-6 backdrop-blur-3xl bg-gray-950/40"
        in:fade={{ duration: 300 }}
        on:click|self={close}
    >
        <div
            class="relative w-full max-w-5xl bg-gray-950/80 border border-white/10 rounded-[2.5rem] shadow-[0_0_100px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col max-h-[90vh]"
            in:fly={{ y: 40, duration: 600, opacity: 0 }}
        >
            <!-- Glowing accent -->
            <div
                class="absolute -top-24 -left-24 w-64 h-64 bg-cyan-500/10 blur-[100px] rounded-full"
            ></div>
            <div
                class="absolute -bottom-24 -right-24 w-64 h-64 bg-purple-500/10 blur-[100px] rounded-full"
            ></div>

            <!-- Header -->
            <div
                class="relative px-8 py-8 border-b border-white/5 flex items-center justify-between bg-white/5 backdrop-blur-md"
            >
                <div class="flex items-center gap-6">
                    {#if loading}
                        <div
                            class="w-16 h-16 rounded-2xl bg-white/5 animate-pulse"
                        ></div>
                        <div class="space-y-2">
                            <div
                                class="w-48 h-8 bg-white/5 animate-pulse rounded-lg"
                            ></div>
                            <div
                                class="w-24 h-4 bg-white/5 animate-pulse rounded-md"
                            ></div>
                        </div>
                    {:else if detail}
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-3xl font-black shadow-[0_0_20px_rgba(6,182,212,0.3)]"
                        >
                            {detail.nombres.charAt(0)}
                        </div>
                        <div>
                            <h2
                                class="text-2xl md:text-3xl font-black tracking-tighter text-white uppercase italic"
                            >
                                {detail.nombres}
                            </h2>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full {detail.activo ===
                                        'S'
                                            ? 'bg-cyan-400'
                                            : 'bg-red-400'} opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2 w-2 {detail.activo ===
                                        'S'
                                            ? 'bg-cyan-500'
                                            : 'bg-red-500'}"
                                    ></span>
                                </span>
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-gray-400"
                                >
                                    {detail.activo === "S"
                                        ? "Verified Official"
                                        : "Account Inactive"}
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>

                <button
                    on:click={close}
                    class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all border border-white/5 active:scale-90 group"
                    aria-label="Cerrar detalles"
                >
                    <svg
                        class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Body (Scrollable) -->
            <div
                class="flex-1 overflow-y-auto custom-scrollbar p-8 md:p-10 space-y-12"
            >
                {#if loading}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {#each Array(3) as _}
                            <div class="space-y-4">
                                <div
                                    class="w-32 h-4 bg-white/5 animate-pulse rounded"
                                ></div>
                                <div
                                    class="w-full h-48 bg-white/5 animate-pulse rounded-[2rem]"
                                ></div>
                            </div>
                        {/each}
                    </div>
                {:else if error}
                    <div
                        class="flex flex-col items-center justify-center py-20 text-center"
                    >
                        <div
                            class="w-20 h-20 rounded-full bg-red-500/10 flex items-center justify-center text-red-500 mb-6 border border-red-500/20"
                        >
                            <svg
                                class="w-10 h-10"
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
                        <h3 class="text-xl font-bold text-white mb-2">
                            Error de Sincronización
                        </h3>
                        <p class="text-gray-400 max-w-sm">{error}</p>
                        <button
                            on:click={loadDetail}
                            class="mt-8 px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition-all border border-white/10"
                            >Reintentar Conexión</button
                        >
                    </div>
                {:else if detail}
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                    >
                        <!-- Sección 1: Información Personal -->
                        <div
                            class="space-y-6"
                            in:fly={{ y: 20, duration: 600, delay: 100 }}
                        >
                            <h3
                                class="flex items-center gap-3 text-cyan-400 font-black text-xs uppercase tracking-[0.3em]"
                            >
                                <span class="w-8 h-px bg-cyan-400/30"></span> Información
                                Personal
                            </h3>
                            <div
                                class="bg-white/5 border border-white/5 p-6 rounded-[2rem] space-y-4 backdrop-blur-sm"
                            >
                                <div class="group">
                                    <label
                                        for="identificacion"
                                        class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1 block"
                                        >Identificación</label
                                    >
                                    <p
                                        id="identificacion"
                                        class="font-mono text-white text-lg tracking-tighter"
                                    >
                                        {detail.identificacion}
                                    </p>
                                </div>
                                <div class="group">
                                    <label
                                        for="correo"
                                        class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1 block"
                                        >Correo Electrónico</label
                                    >
                                    <p
                                        id="correo"
                                        class="text-white font-medium break-all"
                                    >
                                        {detail.correo || "No registrado"}
                                    </p>
                                </div>
                                <div class="group">
                                    <label
                                        for="sede"
                                        class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1 block"
                                        >Asignación Sede</label
                                    >
                                    <div
                                        id="sede"
                                        class="inline-flex items-center px-4 py-1 rounded-full bg-cyan-500/10 text-cyan-400 text-xs font-black border border-cyan-500/20"
                                    >
                                        SECTOR {detail.asignacion}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Permisos de Sistema -->
                        <div
                            class="space-y-6"
                            in:fly={{ y: 20, duration: 600, delay: 200 }}
                        >
                            <h3
                                class="flex items-center gap-3 text-purple-400 font-black text-xs uppercase tracking-[0.3em]"
                            >
                                <span class="w-8 h-px bg-purple-400/30"></span> Permisos
                                de Sistema
                            </h3>
                            <div
                                class="bg-white/5 border border-white/5 p-6 rounded-[2rem] space-y-3 backdrop-blur-sm"
                            >
                                {#each toggleFields as field}
                                    <div
                                        class="flex items-center justify-between p-3 rounded-2xl bg-white/2 hover:bg-white/5 transition-colors group"
                                    >
                                        <span
                                            class="text-[11px] font-bold text-gray-300 tracking-tight"
                                            >{labels[field]}</span
                                        >
                                        <div
                                            class="w-12 h-6 rounded-full relative transition-all duration-300 {detail[
                                                field
                                            ] === 'S'
                                                ? 'bg-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.4)]'
                                                : 'bg-gray-800'}"
                                        >
                                            <div
                                                class="absolute top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 {detail[
                                                    field
                                                ] === 'S'
                                                    ? 'left-7'
                                                    : 'left-1'}"
                                            ></div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>

                        <!-- Sección 3: Credenciales de Seguridad -->
                        <div
                            class="space-y-6"
                            in:fly={{ y: 20, duration: 600, delay: 300 }}
                        >
                            <h3
                                class="flex items-center gap-3 text-pink-500 font-black text-xs uppercase tracking-[0.3em]"
                            >
                                <span class="w-8 h-px bg-pink-500/30"></span> Credenciales
                                Alpha
                            </h3>
                            <div
                                class="bg-white/5 border border-white/5 p-6 rounded-[2rem] space-y-6 backdrop-blur-sm"
                            >
                                {#each ["pass", "maestra", "clave_acceso"] as field}
                                    {@const detailField =
                                        field as keyof TeacherDetail}
                                    <div class="space-y-2">
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <label
                                                for={field}
                                                class="text-[9px] font-black uppercase tracking-widest {field ===
                                                'clave_acceso'
                                                    ? 'text-pink-500'
                                                    : 'text-gray-500'}"
                                            >
                                                {field === "pass"
                                                    ? "Contraseña Portal"
                                                    : field === "maestra"
                                                      ? "Master Token"
                                                      : "Encrypted Access Key"}
                                            </label>
                                            <button
                                                type="button"
                                                on:click={() =>
                                                    toggleReveal(field)}
                                                class="text-gray-500 hover:text-white transition-colors"
                                                aria-label="Toggle reveal {field}"
                                            >
                                                {#if revealedFields[field]}
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        ><path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"
                                                        /></svg
                                                    >
                                                {:else}
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        ><path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        /><path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        /></svg
                                                    >
                                                {/if}
                                            </button>
                                        </div>
                                        <div class="relative group">
                                            <div
                                                class="absolute inset-0 bg-cyan-500/5 blur-xl group-focus-within:bg-cyan-500/20 transition-all rounded-xl"
                                            ></div>
                                            <input
                                                id={field}
                                                readonly
                                                type={revealedFields[field]
                                                    ? "text"
                                                    : "password"}
                                                value={detail[detailField]}
                                                class="relative w-full bg-black/40 border border-white/5 focus:border-cyan-500/50 rounded-xl px-4 py-3 text-sm font-mono tracking-wider text-white outline-none transition-all {revealedFields[
                                                    field
                                                ]
                                                    ? ''
                                                    : 'text-gray-600 blur-[2px] cursor-not-allowed select-none'}"
                                            />
                                        </div>
                                    </div>
                                {/each}

                                <div
                                    class="bg-indigo-500/10 border border-indigo-500/20 p-4 rounded-2xl"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                ><path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 20c4.478 0 8.268-2.943 9.543-7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                                /></svg
                                            >
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] font-black text-indigo-300 uppercase leading-none"
                                            >
                                                ID Identificador
                                            </p>
                                            <p
                                                class="text-[9px] font-mono text-indigo-400/80 mt-1 truncate max-w-[150px]"
                                            >
                                                {detail.idn}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>

            <!-- Footer -->
            <div
                class="bg-black/50 border-t border-white/5 px-8 py-4 flex items-center justify-between backdrop-blur-md"
            >
                <div class="flex items-center gap-4 text-gray-500">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"
                        ></span>
                        <span
                            class="text-[9px] font-mono font-bold uppercase tracking-widest"
                            >Sys_Status: Stable</span
                        >
                    </div>
                    <span class="h-3 w-px bg-white/10"></span>
                    {#if detail}
                        <div class="flex items-center gap-2">
                            <svg
                                class="w-3.5 h-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                ><path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                /></svg
                            >
                            <span class="text-[9px] font-mono font-medium"
                                >Last Sync: {detail.fechaactualizacion}</span
                            >
                        </div>
                    {/if}
                </div>
                <div
                    class="text-[9px] font-mono text-cyan-500/50 tracking-widest hidden md:block"
                >
                    PRO_GRADE_SYSTEM_ENCRYPTION_v4.0
                </div>
            </div>
        </div>
    </div>
{/if}

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    input:read-only {
        pointer-events: none;
    }

    /* Toggle Switches are purely visual in this readonly version, 
       but we keep the logic for future interaction */
</style>
