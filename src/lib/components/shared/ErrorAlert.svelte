<!-- 
ERRORALERT.SVELTE

DESCRIPCIÓN:
Componente de alerta para mostrar mensajes de error con una estética disruptiva y animada (shake). Utiliza glassmorphism fuerte para destacar sobre otros elementos.

USO:
<ErrorAlert error={$error} /> en App.svelte o cualquier contenedor de errores.

DEPENDENCIAS:
- Store: theme (themeStore.ts).

PROPS/EMIT:
- Prop: `error` → string | null → El mensaje de error a mostrar. Si es null, el componente no se renderiza.

RELACIONES:
- Llamado por: App.svelte.

NOTAS DE DESARROLLO:
- Incluye una animación 'animate-shake' para captar la atención del usuario.
- El gradiente de fondo es reactivo al tema.

ESTILOS:
- Clase 'glass-effect-strong' para máxima legibilidad sobre fondos complejos.
- Bordes semitransparentes con colores de alerta.
-->

<script lang="ts">
    import { fade } from "svelte/transition";
    import { theme } from "../../themeStore";

    export let error: string | null = null;
</script>

{#if error}
    <div
        transition:fade={{ duration: 300 }}
        class="glass-effect-strong border-2 border-red-500/50 px-6 py-4 rounded-2xl relative animate-shake shadow-premium-xl mx-4 my-4 flex items-center gap-3"
        role="alert"
    >
        <div class="flex items-start gap-3 w-full">
            <div
                class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg"
            >
                <span class="material-symbols-rounded text-white text-xl"
                    >error</span
                >
            </div>
            <div class="flex-1">
                <strong
                    class="font-bold text-red-600 dark:text-red-400 block mb-1"
                    style="font-family: var(--font-heading);">Error</strong
                >
                <span
                    class="text-sm {$theme === 'dark'
                        ? 'text-red-200'
                        : 'text-red-800'}">{error}</span
                >
            </div>
        </div>
    </div>
{/if}
