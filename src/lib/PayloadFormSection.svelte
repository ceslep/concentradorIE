<script lang="ts">
  import PayloadForm from "./PayloadForm.svelte";
  import { theme } from "./themeStore";

  export let show: boolean = true;
  export let loading: boolean = false;
</script>

{#if show}
  <section
    class="glass-effect-strong rounded-xl p-3 sm:p-4 shadow-premium-xl animate-slide-in-down relative overflow-hidden border-2"
    style="border-color: {$theme === 'dark'
      ? 'rgba(102, 126, 234, 0.2)'
      : 'rgba(102, 126, 234, 0.15)'};"
  >
    <!-- Decorative gradient overlay -->
    <div
      class="absolute top-0 right-0 w-32 h-32 sm:w-48 sm:h-48 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-full blur-3xl -z-10"
    ></div>
    <PayloadForm disabled={loading} />
  </section>
{/if}
