<!-- 
BACKGROUNDDECORATIONS.SVELTE

DESCRIPCIÓN:
Componente de envoltura (wrapper) que proporciona el fondo decorativo premium y el sistema de rejilla sutil para toda la aplicación. Gestiona los degradados radiales según el tema activo.

USO:
<BackgroundDecorations> ... contenido ... </BackgroundDecorations> en App.svelte.

DEPENDENCIAS:
- Store: theme (themeStore.ts).

PROPS/EMIT:
- Slot: Contenido principal de la aplicación.

RELACIONES:
- Llamado por: App.svelte como contenedor principal.

NOTAS DE DESARROLLO:
- Utiliza 'radial-gradient' dinámico que cambia entre modo claro y oscuro.
- La rejilla decorativa es fija y no interfiere con los eventos de ratón (pointer-events-none).

ESTILOS:
- Implementa transiciones suaves de color (duration-300).
- Usa variables CSS para la tipografía corporativa.
-->

<script lang="ts">
    import { theme } from "../../themeStore";
    import DevLabel from "../shared/DevLabel.svelte";
</script>

<!-- Premium Background with Subtle Pattern -->
<div
    class="min-h-screen p-3 sm:p-4 lg:p-5 space-y-3 flex flex-col relative overflow-hidden transition-colors duration-300"
    style="font-family: var(--font-body); background: {$theme === 'dark'
        ? 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.08) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.08) 0%, transparent 50%), #0f172a'
        : 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(240, 147, 251, 0.05) 0%, transparent 50%), #ffffff'};"
>
    <DevLabel name="BackgroundDecorations.svelte" />
    <!-- Decorative Background Elements -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <!-- Efectos de gradiente radial para el fondo "Digital Aurora" -->
        <div
            class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500/10 blur-[120px] dark:bg-indigo-600/20"
        ></div>
        <div
            class="absolute bottom-[-10%] right-[-10%] w-[45%] h-[45%] rounded-full bg-purple-500/10 blur-[130px] dark:bg-purple-600/25"
        ></div>

        <!-- Patrón de cuadrícula sutil para profundidad -->
        <div
            class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05] brightness-125"
            style="background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0); background-size: 40px 40px;"
        ></div>
    </div>

    <!-- Contenedor principal para el contenido de la aplicación -->
    <main class="relative min-h-screen flex-grow">
        <slot />
    </main>
</div>
