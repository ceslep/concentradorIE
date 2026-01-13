<!-- 
LOADER.SVELTE

DESCRIPCIÓN:
Componente de carga profesional y reutilizable con animaciones premium.
Muestra un spinner animado con efectos de glassmorphism y gradientes.

USO:
<Loader message="Cargando datos..." />

PROPS:
- message: string (opcional) → Mensaje personalizado de carga
- size: 'sm' | 'md' | 'lg' (opcional, default: 'md') → Tamaño del loader

ESTILOS:
- Animaciones suaves con blur y gradientes
- Soporte para tema claro/oscuro
-->

<script lang="ts">
    import { fade, scale } from "svelte/transition";

    let {
        message = "Cargando datos...",
        size = "md",
        subtitle = "Por favor espera un momento",
    } = $props<{
        message?: string;
        size?: "sm" | "md" | "lg";
        subtitle?: string;
    }>();

    const sizeClasses = {
        sm: "text-4xl",
        md: "text-7xl",
        lg: "text-9xl",
    };

    const containerClasses = {
        sm: "h-40",
        md: "h-80",
        lg: "h-96",
    };
</script>

<div
    class="flex flex-col items-center justify-center {containerClasses[
        size
    ]} space-y-5"
    in:fade={{ duration: 200 }}
>
    <div class="relative" in:scale={{ duration: 300, start: 0.8 }}>
        <!-- Glow effect -->
        <div
            class="absolute inset-0 bg-indigo-500/20 dark:bg-indigo-400/20 rounded-full blur-2xl animate-pulse"
        ></div>

        <!-- Spinner -->
        <span
            class="material-symbols-rounded {sizeClasses[
                size
            ]} text-indigo-600 dark:text-indigo-400 animate-spin relative"
            style="animation-duration: 1s;"
        >
            progress_activity
        </span>
    </div>

    <div class="text-center space-y-2" in:fade={{ delay: 100 }}>
        <p class="text-base font-semibold text-gray-700 dark:text-gray-300">
            {message}
        </p>
        {#if subtitle}
            <p class="text-sm text-gray-500 dark:text-gray-500">
                {subtitle}
            </p>
        {/if}
    </div>
</div>

<style>
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>
