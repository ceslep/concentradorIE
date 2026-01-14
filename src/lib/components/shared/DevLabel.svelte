<script lang="ts">
    import { SHOW_COMPONENT_NAMES } from "../../../constants";
    let { name, style = "" } = $props<{ name: string; style?: string }>();
    const isDev = import.meta.env.DEV && SHOW_COMPONENT_NAMES;

    // Generate a deterministic random position based on the name
    // to prevent labels from stacking on top of each other
    function getLeftPosition(str: string): number {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        // Result between 5% and 85%
        return 5 + (Math.abs(hash) % 80);
    }

    let leftPos = $derived(getLeftPosition(name));

    // Generate a color based on name for easier visual distinction
    function getColor(str: string): string {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        const hue = Math.abs(hash) % 360;
        return `hsl(${hue}, 70%, 40%)`;
    }

    let bgColor = $derived(getColor(name));
</script>

{#if isDev}
    <div
        class="dev-label"
        style="left: {leftPos}%; background-color: {bgColor}; {style}"
        title={name}
    >
        &lt;{name}&gt;
    </div>
{/if}

<style>
    .dev-label {
        position: absolute;
        top: 0;
        /* left is computed dynamically */
        transform: translateY(0);
        color: white;
        font-size: 9px;
        font-weight: bold;
        padding: 2px 6px;
        z-index: 99999;
        pointer-events: auto; /* Allow hovering */
        border-bottom-right-radius: 6px;
        border-bottom-left-radius: 6px;
        font-family: "Consolas", "Monaco", monospace;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(4px);
        opacity: 0.7;
        transition: all 0.2s ease-out;
        white-space: nowrap;
        cursor: help;
    }

    .dev-label:hover {
        opacity: 1;
        z-index: 100000;
        transform: translateY(2px) scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
    }
</style>
