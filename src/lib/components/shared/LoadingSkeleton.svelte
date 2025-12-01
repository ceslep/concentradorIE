<script lang="ts">
    import Skeleton from "./Skeleton.svelte";
    import { theme } from "../../themeStore";
    import type { AsignaturaOrdenItem } from "../../types";

    export let loading: boolean;
    export let parsed: any;
    export let currentOrden: AsignaturaOrdenItem[];
</script>

{#if loading && !parsed}
    {#if currentOrden && currentOrden.length}
        {@const cols = currentOrden.length + 1}
        {@const rest = 70 / Math.max(1, currentOrden.length)}
        {@const widths = [
            "30%",
            ...Array(currentOrden.length).fill(`${rest}%`),
        ]}
        <Skeleton
            rows={8}
            columns={cols}
            headerWidth="30%"
            columnWidths={widths}
            theme={$theme}
        />
    {:else}
        <Skeleton rows={8} columns={5} headerWidth="33%" theme={$theme} />
    {/if}
{/if}
