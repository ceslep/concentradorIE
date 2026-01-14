<!-- 
LOADINGSKELETON.SVELTE

DESCRIPCIÓN:
Componente de alto nivel que gestiona la visualización de estados de carga (skeletons) para las tablas de datos. Adapta dinámicamente el número de columnas basándose en el orden actual de asignaturas o áreas.

USO:
<LoadingSkeleton loading={$loading} parsed={$parsed} currentOrden={$currentOrden} /> en App.svelte.

DEPENDENCIAS:
- Componente hijo: Skeleton.svelte.
- Store: theme (themeStore.ts).
- Tipos: AsignaturaOrdenItem (types.ts).

PROPS/EMIT:
- Prop: `loading` → boolean → Estado de carga global.
- Prop: `parsed` → any → Datos parseados (si ya existen, el skeleton no se muestra).
- Prop: `currentOrden` → AsignaturaOrdenItem[] → Estructura de columnas para dimensionar el skeleton.

RELACIONES:
- Llamado por: App.svelte.
- Depende de: Skeleton.svelte para el renderizado base.

NOTAS DE DESARROLLO:
- Calcula automáticamente el ancho de las columnas (`rest%`) para una apariencia equilibrada.

ESTILOS:
- Sin estilos propios, depende de las utilidades de Skeleton.svelte.
-->

<script lang="ts">
    import Skeleton from "./Skeleton.svelte";
    import { theme } from "../../themeStore";
    import type { AsignaturaOrdenItem } from "../../types";

    export let loading: boolean;
    export let parsed: any;
    export let currentOrden: AsignaturaOrdenItem[];

    import DevLabel from "./DevLabel.svelte";
</script>

{#if loading && !parsed}
    <DevLabel name="LoadingSkeleton.svelte" />
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
