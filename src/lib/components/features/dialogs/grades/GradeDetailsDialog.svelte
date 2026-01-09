<!-- 
GRADEDETAILSDIALOG.SVELTE

DESCRIPCIÓN:
Diálogo ligero (info modal) para mostrar metadatos de una calificación específica: aspecto evaluado, porcentaje de la nota y fechas de asignación/registro.

USO:
<GradeDetailsDialog {show} {columnName} {aspecto} ... {onClose} />

PROPS/EMIT:
- Prop: `show` → boolean → Visibilidad.
- Prop: `columnName` → string → Título del aspecto (ej: 'N1').
- Prop: `onClose` → function → Callback para cerrar el modal.

RELACIONES:
- Llamado por: GradesTable2.svelte.

NOTAS DE DESARROLLO:
- Diseño minimalista y autocontenido con sus propios estilos CSS.
- Accesible mediante la tecla 'Escape'.

ESTILOS:
- Implementa un overlay con desenfoque (`backdrop-filter: blur(8px)`) y gradientes de marca.
-->

<script lang="ts">
    import { fade } from "svelte/transition";

    // Props
    export let show: boolean = false;
    export let columnName: string = "";
    export let aspecto: string | null = null;
    export let porcentaje: string | null = null;
    export let fechaa: string | null = null;
    export let fecha: string | null = null;
    export let onClose: () => void;

    // Handle escape key
    /**
     * Emite el evento de cierre al componente padre.
     */
    function handleKeydown(e: KeyboardEvent) {
        if (e.key === "Escape") {
            onClose();
        }
    }
</script>

{#if show}
    <div
        class="dialog-overlay"
        role="button"
        tabindex="0"
        on:click={onClose}
        on:keydown={handleKeydown}
        transition:fade={{ duration: 200 }}
        aria-label="Close dialog"
    >
        <div
            class="dialog-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="dialog-title"
            tabindex="-1"
            on:click|stopPropagation
            on:keydown|stopPropagation
        >
            <div class="dialog-header">
                <div class="dialog-header-left">
                    <span class="material-symbols-rounded dialog-icon"
                        >info</span
                    >
                    <h2 id="dialog-title" class="dialog-title">
                        Detalles de {columnName}
                    </h2>
                </div>
                <button class="dialog-close" on:click={onClose}>
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div class="dialog-body">
                <div class="detail-item">
                    <div class="detail-label">
                        <span class="material-symbols-rounded">description</span
                        >
                        <span>Aspecto</span>
                    </div>
                    <div class="detail-value">{aspecto || "No disponible"}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <span class="material-symbols-rounded">percent</span>
                        <span>Porcentaje</span>
                    </div>
                    <div class="detail-value">
                        {porcentaje || "No disponible"}
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-item">
                        <div class="detail-label">
                            <span class="material-symbols-rounded">event</span>
                            <span>Fecha Asignación</span>
                        </div>
                        <div class="detail-value">
                            {fechaa || "No disponible"}
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <span class="material-symbols-rounded"
                                >calendar_today</span
                            >
                            <span>Fecha Nota</span>
                        </div>
                        <div class="detail-value">
                            {fecha || "No disponible"}
                        </div>
                    </div>
                </div>
            </div>

            <div class="dialog-footer">
                <button class="btn-close" on:click={onClose}> Cerrar </button>
            </div>
        </div>
    </div>
{/if}

<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

    /* ===== DIALOG MODAL STYLES ===== */
    .dialog-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
        cursor: pointer;
    }

    :global(.dark) .dialog-overlay {
        background: rgba(0, 0, 0, 0.85);
    }

    .dialog-content {
        background: white;
        border-radius: 20px;
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.25),
            0 0 0 1px rgba(0, 0, 0, 0.05);
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        cursor: default;
        font-family: "Inter", sans-serif;
        animation: slideIn 0.3s ease-out;
    }

    :global(.dark) .dialog-content {
        background: #1e293b;
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px) scale(0.95);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    /* Dialog Header */
    .dialog-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 28px;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .dialog-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dialog-icon {
        font-size: 28px;
        color: white;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dialog-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: white;
        letter-spacing: -0.02em;
    }

    .dialog-close {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        border-radius: 10px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: white;
    }

    .dialog-close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.05);
    }

    .dialog-close:active {
        transform: scale(0.95);
    }

    .dialog-close .material-symbols-rounded {
        font-size: 24px;
    }

    /* Dialog Body */
    .dialog-body {
        padding: 28px;
        max-height: calc(90vh - 200px);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    :global(.dark) .dialog-body {
        background: #1e293b;
    }

    /* Scrollbar for dialog body */
    .dialog-body::-webkit-scrollbar {
        width: 8px;
    }

    .dialog-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    :global(.dark) .dialog-body::-webkit-scrollbar-track {
        background: #0f172a;
    }

    .dialog-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    :global(.dark) .dialog-body::-webkit-scrollbar-thumb {
        background: #475569;
    }

    /* Detail Items */
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .detail-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        border-color: #c7d2fe;
    }

    :global(.dark) .detail-item {
        background: linear-gradient(135deg, #334155 0%, #475569 100%);
        border-color: #475569;
    }

    :global(.dark) .detail-item:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    }

    .detail-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #6366f1;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    :global(.dark) .detail-label {
        color: #a5b4fc;
    }

    .detail-label .material-symbols-rounded {
        font-size: 20px;
    }

    .detail-value {
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.6;
        padding-left: 28px;
    }

    :global(.dark) .detail-value {
        color: #e2e8f0;
    }

    /* Detail Row (for side-by-side items) */
    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 640px) {
        .detail-row {
            grid-template-columns: 1fr;
        }
    }

    /* Dialog Footer */
    .dialog-footer {
        padding: 20px 28px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    :global(.dark) .dialog-footer {
        background: #0f172a;
        border-top-color: #334155;
    }

    .btn-close {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 12px 32px;
        font-size: 15px;
        font-weight: 600;
        font-family: "Inter", sans-serif;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-close:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(99, 102, 241, 0.4);
    }

    .btn-close:active {
        transform: translateY(0);
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .dialog-content {
            margin: 0 12px;
            border-radius: 16px;
        }

        .dialog-header {
            padding: 20px;
        }

        .dialog-title {
            font-size: 18px;
        }

        .dialog-body {
            padding: 20px;
        }

        .detail-item {
            padding: 14px 16px;
        }

        .dialog-footer {
            padding: 16px 20px;
        }

        .btn-close {
            width: 100%;
        }
    }
</style>
