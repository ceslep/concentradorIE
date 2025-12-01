<script lang="ts">
    import { theme } from "../../themeStore";

    export let error: string | null = null;
</script>

{#if error}
    <div
        class="glass-effect-strong border-2 border-red-500/50 px-6 py-4 rounded-2xl relative animate-shake shadow-premium-xl"
        role="alert"
        style="background: {$theme === 'dark'
            ? 'linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(185, 28, 28, 0.15))'
            : 'linear-gradient(135deg, rgba(254, 202, 202, 0.5), rgba(252, 165, 165, 0.5))'};"
    >
        <div class="flex items-start gap-3">
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
