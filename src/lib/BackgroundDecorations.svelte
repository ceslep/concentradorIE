<script lang="ts">
    import { theme } from "./themeStore";
</script>

<!-- Premium Background with Subtle Pattern -->
<div
    class="min-h-screen p-3 sm:p-4 lg:p-5 space-y-3 flex flex-col relative overflow-hidden transition-colors duration-300"
    style="font-family: var(--font-body); background: {$theme === 'dark'
        ? 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.08) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.08) 0%, transparent 50%), #0f172a'
        : 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(240, 147, 251, 0.05) 0%, transparent 50%), #ffffff'};"
>
    <!-- Decorative Background Elements -->
    <div
        class="fixed inset-0 pointer-events-none opacity-30"
        style="background-image: radial-gradient({$theme === 'dark'
            ? 'rgba(148, 163, 184, 0.15)'
            : 'rgba(203, 213, 225, 0.3)'} 1px, transparent 1px); background-size: 40px 40px;"
    ></div>

    <slot />
</div>
