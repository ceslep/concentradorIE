<script lang="ts">
    import { theme } from "./themeStore";
    import type { Writable } from "svelte/store";

    export let showPeriodos: Writable<boolean>;
</script>

<div class="flex flex-wrap gap-3 items-center animate-fade-in">
    <label
        class="flex items-center gap-2 px-3 py-2 rounded-lg glass-effect shadow-md hover:shadow-lg transition-all duration-300 cursor-pointer group"
    >
        <!-- Custom Checkbox -->
        <div class="relative">
            <input
                type="checkbox"
                bind:checked={$showPeriodos}
                class="peer sr-only"
            />
            <div
                class="w-6 h-6 rounded-lg border-2 {$theme === 'dark'
                    ? 'border-gray-600 bg-gray-800'
                    : 'border-gray-300 bg-white'} peer-checked:bg-gradient-to-br peer-checked:from-indigo-500 peer-checked:to-purple-500 peer-checked:border-transparent transition-all duration-300 flex items-center justify-center shadow-sm"
            >
                <span
                    class="material-symbols-rounded text-white text-lg opacity-0 peer-checked:opacity-100 transition-opacity duration-300"
                    >check</span
                >
            </div>
        </div>
        <span
            class="text-sm font-medium {$theme === 'dark'
                ? 'text-gray-200'
                : 'text-gray-700'} group-hover:text-indigo-500 transition-colors"
            style="font-family: var(--font-heading);"
        >
            Mostrar todos los periodos
        </span>
    </label>
</div>
