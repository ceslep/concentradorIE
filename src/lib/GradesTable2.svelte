<script lang="ts">
    import { onMount, onDestroy, tick } from "svelte";
    import { fade } from "svelte/transition";
    import {
        TabulatorFull as Tabulator,
        type ColumnDefinition,
    } from "tabulator-tables";
    import "tabulator-tables/dist/css/tabulator.min.css"; // Import styles
    import {
        GET_NOTAS_ENDPOINT,
        GET_PERIODOS_NOTAS_ENDPOINT,
    } from "../../constants";
    import { payload, selectedAsignatura } from "./storeConcentrador";
    import GradeDetailsDialog from "./GradeDetailsDialog.svelte";

    // Props
    export let tableNotasId: string = "gradesTable2";
    export let docenteId: string;
    export let initialPeriodo: string | undefined = undefined;

    // Reactive State
    $: docente = docenteId || $payload.Asignacion;
    $: asignatura = $selectedAsignatura;
    $: nivel = $payload.nivel;
    $: numero = $payload.numero;
    $: asignation = $payload.Asignacion;
    $: year = $payload.year;

    let tableInstance: any;
    let mounted = false;
    let currentPeriodo: string = "";
    let isLoading = false;
    let currentEditingCell: any = null;

    // Dialog state
    let showDialog = false;
    let dialogData: {
        columnName: string;
        aspecto: string | null;
        porcentaje: string | null;
        fechaa: string | null;
        fecha: string | null;
    } = {
        columnName: "",
        aspecto: null,
        porcentaje: null,
        fechaa: null,
        fecha: null,
    };

    // Trigger load when dependencies change
    $: if (mounted && docente && asignatura) {
        loadTable();
    }

    // --- Helpers ---

    const toDateInputValue = (date: Date) => {
        const local = new Date(date);
        local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    };

    const countNonEmptyGroups = (array: any[]) => {
        const groups = [
            array.slice(0, 3), // N1-N3
            array.slice(3, 6), // N4-N6
            array.slice(6, 9), // N7-N9
            array.slice(9, 10), // N10
            array.slice(10, 11), // N11
        ];
        const isEmpty = (val: any) => !val || String(val).trim() === "";
        return groups.map(
            (group) => group.filter((val) => !isEmpty(val)).length,
        );
    };

    const calculateRowVal = (data: any) => {
        const noteFields = [
            "N1",
            "N2",
            "N3",
            "N4",
            "N5",
            "N6",
            "N7",
            "N8",
            "N9",
            "N10",
            "N11",
        ];
        const noteValues = noteFields.map((f) => data[f] || " ");
        const counts = countNonEmptyGroups(noteValues);

        let Saber35 = 0,
            Hacer35 = 0,
            Ser20 = 0,
            Autoe5 = 0,
            Coev5 = 0;

        noteFields.forEach((field, idx) => {
            const val = data[field];
            if (val !== " " && val !== "" && val != null) {
                const num = parseFloat(val);
                if (!isNaN(num)) {
                    const nIdx = (idx + 1).toString();
                    if (["1", "2", "3"].includes(nIdx)) Saber35 += num;
                    else if (["4", "5", "6"].includes(nIdx)) Hacer35 += num;
                    else if (["7", "8", "9"].includes(nIdx)) Ser20 += num;
                    else if (nIdx === "10") Autoe5 = num;
                    else if (nIdx === "11") Coev5 = num;
                }
            }
        });

        const saberAvg = counts[0] ? Saber35 / counts[0] : 0;
        const hacerAvg = counts[1] ? Hacer35 / counts[1] : 0;
        const serAvg = counts[2] ? Ser20 / counts[2] : 0;

        const finalVal =
            saberAvg * 0.35 +
            hacerAvg * 0.35 +
            serAvg * 0.2 +
            Autoe5 * 0.05 +
            Coev5 * 0.05;

        return finalVal.toFixed(2);
    };

    const gradeFormatter = (cell: any) => {
        const val = cell.getValue();
        if (val === " " || val === "" || val === null || val === undefined) {
            cell.getElement().style.color = ""; // Reset color
            return "";
        }
        const num = parseFloat(val);

        // Pintar de rojo si es menor a 3.0
        if (!isNaN(num) && num < 3.0) {
            cell.getElement().style.color = "#ef4444"; // red-500
            cell.getElement().style.fontWeight = "bold";
        } else {
            cell.getElement().style.color = ""; // Reset color
            cell.getElement().style.fontWeight = "";
        }

        return isNaN(num) ? val : num.toFixed(1);
    };

    const valid = (value: any, parameters: { min: number; max: number }) => {
        // Allow empty values
        if (
            value === "" ||
            value === " " ||
            value === null ||
            value === undefined
        ) {
            return true;
        }

        // Convert to number and validate range
        const numValue = parseFloat(value);

        if (isNaN(numValue)) {
            return false;
        }

        return numValue >= parameters.min && numValue <= parameters.max;
    };

    // Handle header click to show grade details
    const handleHeaderClick = (columnName: string) => {
        // Only handle N1-N12 columns
        if (!columnName.startsWith("N") || columnName === "Nombres") return;

        // Extract the number from the column name (e.g., "N1" -> "1")
        const columnNumber = columnName.replace("N", "");

        // Get the first row of data to extract the details for this column
        if (tableInstance) {
            const data = tableInstance.getData();
            if (data && data.length > 0) {
                const firstRow = data[0];
                dialogData = {
                    columnName: columnName,
                    aspecto:
                        firstRow[`aspecto${columnNumber}`] || "No disponible",
                    porcentaje:
                        firstRow[`porcentaje${columnNumber}`] ||
                        "No disponible",
                    fechaa:
                        firstRow[`fechaa${columnNumber}`] || "No disponible",
                    fecha: firstRow[`fecha${columnNumber}`] || "No disponible",
                };
                showDialog = true;
            }
        }
    };

    // --- Column Definitions ---

    function createGradeColumn(title: string, field: string): ColumnDefinition {
        return {
            title: title,
            field: field,
            editor: "number",
            editorParams: {
                verticalNavigation: "editor",
                min: 1,
                max: 5,
                step: 0.1,
                selectContents: true,
            },
            hozAlign: "right",
            validator: [{ type: valid, parameters: { min: 1, max: 5 } }],
            formatter: gradeFormatter,
            headerClick: (e: any, column: any) => {
                // Handle header click
                handleHeaderClick(column.getField());
            },
            cellClick: (e: any, cell: any) => {
                // Manually focus the cell to enable keyboard navigation
                cell.getElement().focus();

                // Prevent default action (opening editor)
                // This ensures we only edit when pressing Enter (handled by the global keydown listener)
                return false;
            },
            cellEdited: (cell: any) => {
                const row = cell.getRow();
                const data = row.getData();

                // Get the current value from the cell
                const cellValue = cell.getValue();

                // Force format to x.y (one decimal place)
                const numValue = parseFloat(cellValue);
                if (!isNaN(numValue)) {
                    data[field] = numValue.toFixed(1);
                } else {
                    data[field] = cellValue; // Keep original if not a number
                }

                // Recalculate Val
                data.Val = calculateRowVal(data);

                // Update date if missing
                const idx = field.replace("N", "");
                const fechaKey = `fecha${idx}`;
                if (!data[fechaKey])
                    data[fechaKey] = toDateInputValue(new Date());

                row.update(data);
            },
        };
    }

    // --- Main Logic ---

    const getCurrentPeriodo = async () => {
        try {
            const res = await fetch(GET_PERIODOS_NOTAS_ENDPOINT);
            const periodos = await res.json();
            return (
                periodos.find((p: any) => p.selected === "selected")?.nombre ||
                "DefaultPeriod"
            );
        } catch (error) {
            return "DefaultPeriod";
        }
    };

    const loadTable = async () => {
        if (!docente) return;

        isLoading = true; // Start loading

        await tick();
        const element = document.getElementById(tableNotasId);
        if (!element) {
            isLoading = false;
            return;
        }

        const per = await getCurrentPeriodo();
        const Elperiodo = initialPeriodo || per;

        if (tableInstance) tableInstance.destroy();

        const isMobile = window.innerWidth < 768;

        tableInstance = new Tabulator(element, {
            height: "100%",
            layout: "fitDataTable", // Allow horizontal scroll
            renderVertical: "basic", // Disable virtualization for modals
            placeholder: "No hay datos",
            reactiveData: true, // Enable reactive data
            ajaxContentType: "json", // Force JSON payload
            responsiveLayout: false, // Disable responsive layout
            keybindings: {
                navUp: "up",
                navDown: "down",
                navLeft: "left",
                navRight: "right",
            },
            columnDefaults: {
                headerSort: false, // Disable header sorting for all columns
                headerHozAlign: "center", // Center header names
            },
            columns: [
                {
                    title: "Nombres",
                    field: "Nombres",
                    width: isMobile ? 200 : 300,
                    frozen: !isMobile, // Freeze name column only on desktop
                },
                {
                    title: "Val",
                    field: "Val",
                    hozAlign: "center",
                    width: 60,
                    formatter: gradeFormatter,
                    frozen: !isMobile, // Freeze Val column only on desktop
                },
                createGradeColumn("N1", "N1"),
                createGradeColumn("N2", "N2"),
                createGradeColumn("N3", "N3"),
                createGradeColumn("N4", "N4"),
                createGradeColumn("N5", "N5"),
                createGradeColumn("N6", "N6"),
                createGradeColumn("N7", "N7"),
                createGradeColumn("N8", "N8"),
                createGradeColumn("N9", "N9"),
                createGradeColumn("N10", "N10"),
                createGradeColumn("N11", "N11"),
                createGradeColumn("N12", "N12"),
            ],
            ajaxURL: GET_NOTAS_ENDPOINT,
            ajaxParams: {
                docente,
                nivel,
                numero,
                asignatura,
                periodo: Elperiodo,
                asignacion: asignation,
                year,
            },
            ajaxConfig: {
                method: "POST",
                headers: { "Content-type": "application/json; charset=utf-8" },
            },
            ajaxResponse: (_url, _params, response: any[]) => {
                // Pre-calculate Val
                const processedData = response
                    .map((item) => {
                        item.Val = calculateRowVal(item);
                        return item;
                    })
                    .sort((a, b) => a.Nombres.localeCompare(b.Nombres));

                // Hide loading after data is processed
                setTimeout(() => {
                    isLoading = false;
                }, 300); // Small delay for smooth transition

                return processedData;
            },
        });

        // Add keyboard event handler for Enter key editing
        // Add keyboard event handler for Enter key editing
        element.addEventListener("keydown", (e: KeyboardEvent) => {
            if (e.key === "Enter") {
                const target = e.target as HTMLElement;
                // Check if the focused element is a cell
                if (target && target.classList.contains("tabulator-cell")) {
                    const field = target.getAttribute("tabulator-field");
                    // Only edit if it's a grade column (N1-N12)
                    if (field && field.startsWith("N")) {
                        const rowElement = target.closest(".tabulator-row");
                        if (rowElement) {
                            const row = tableInstance.getRow(rowElement);
                            const cell = row.getCell(field);
                            if (cell) {
                                cell.edit();
                                e.preventDefault();
                            }
                        }
                    }
                }
            }
        });
    };

    onMount(async () => {
        mounted = true;
        currentPeriodo = await getCurrentPeriodo();
    });

    onDestroy(() => {
        mounted = false;
        if (tableInstance) tableInstance.destroy();
    });
</script>

<div class="grades-table-container">
    {#if isLoading}
        <div class="loading-overlay" transition:fade={{ duration: 200 }}>
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <span class="material-symbols-rounded loading-icon">school</span
                >
            </div>
            <p class="loading-text">Cargando calificaciones...</p>
            <div class="loading-dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    {/if}
    <div id={tableNotasId} class:blur-content={isLoading}></div>
</div>

<!-- Grade Details Dialog -->
<GradeDetailsDialog
    show={showDialog}
    columnName={dialogData.columnName}
    aspecto={dialogData.aspecto}
    porcentaje={dialogData.porcentaje}
    fechaa={dialogData.fechaa}
    fecha={dialogData.fecha}
    onClose={() => (showDialog = false)}
/>

<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

    /* Container */
    .grades-table-container {
        height: 100%;
        min-height: 500px;
        width: 100%;
        overflow: hidden;
        font-family:
            "Inter",
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 20px;
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* ===== DARK MODE SUPPORT ===== */
    :global(.dark) .grades-table-container {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    /* ===== TABULATOR MAIN STYLES ===== */
    :global(.tabulator) {
        font-family: "Inter", sans-serif !important;
        font-size: 14px;
        border: none !important;
        background: transparent !important;
        border-radius: 12px;
        overflow: hidden;
    }

    /* ===== HEADER STYLING ===== */
    :global(.tabulator .tabulator-header) {
        background: linear-gradient(
            135deg,
            #6366f1 0%,
            #8b5cf6 100%
        ) !important;
        border: none !important;
        font-weight: 600;
        color: white !important;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    :global(.tabulator .tabulator-header .tabulator-col) {
        background: transparent !important;
        border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        padding: 12px 8px;
        transition: all 0.2s ease;
    }

    :global(.tabulator .tabulator-header .tabulator-col:hover) {
        background: rgba(255, 255, 255, 0.1) !important;
    }

    /* Make grade column headers look clickable */
    :global(.tabulator .tabulator-header .tabulator-col[tabulator-field^="N"]) {
        cursor: pointer;
        position: relative;
    }

    :global(
            .tabulator
                .tabulator-header
                .tabulator-col[tabulator-field^="N"]:hover
        ) {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    :global(
            .tabulator
                .tabulator-header
                .tabulator-col[tabulator-field^="N"]:active
        ) {
        background: rgba(255, 255, 255, 0.15) !important;
        transform: translateY(1px);
    }

    :global(
            .tabulator .tabulator-header .tabulator-col .tabulator-col-content
        ) {
        color: white !important;
        font-weight: 600;
        letter-spacing: 0.025em;
        text-transform: uppercase;
        font-size: 11px;
    }

    /* Header Filter Input */
    :global(.tabulator .tabulator-header-filter input) {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        color: #1e293b !important;
        transition: all 0.2s ease;
        font-family: "Inter", sans-serif;
    }

    :global(.tabulator .tabulator-header-filter input:focus) {
        outline: none;
        border-color: #fbbf24 !important;
        box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.2);
        background: white !important;
    }

    :global(.tabulator .tabulator-header-filter input::placeholder) {
        color: #94a3b8;
        font-weight: 400;
    }

    /* ===== TABLE BODY ===== */
    :global(.tabulator .tabulator-tableholder) {
        background: white;
        border-radius: 0 0 12px 12px;
    }

    :global(.dark .tabulator .tabulator-tableholder) {
        background: #1e293b;
    }

    /* ===== ROWS ===== */
    :global(.tabulator .tabulator-row) {
        background: white !important;
        border-bottom: 1px solid #e2e8f0 !important;
        transition: all 0.15s ease;
        min-height: 45px;
    }

    :global(.dark .tabulator .tabulator-row) {
        background: #1e293b !important;
        border-bottom-color: #334155 !important;
    }

    :global(.tabulator .tabulator-row:hover) {
        background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%) !important;
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.1);
    }

    :global(.dark .tabulator .tabulator-row:hover) {
        background: linear-gradient(90deg, #334155 0%, #475569 100%) !important;
    }

    :global(.tabulator .tabulator-row.tabulator-row-even) {
        background: #f9fafb !important;
    }

    :global(.dark .tabulator .tabulator-row.tabulator-row-even) {
        background: #0f172a !important;
    }

    /* ===== CELLS ===== */
    :global(.tabulator .tabulator-cell) {
        border-right: 1px solid #e2e8f0 !important;
        padding: 10px 12px;
        color: #1e293b;
        font-weight: 500;
        transition: all 0.15s ease;
    }

    :global(.dark .tabulator .tabulator-cell) {
        border-right-color: #334155 !important;
        color: #e2e8f0;
    }

    /* Nombres Column - Special Styling */
    :global(.tabulator .tabulator-cell[tabulator-field="Nombres"]) {
        font-weight: 600;
        color: #0f172a;
        background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
        font-size: 14px;
    }

    /* Grade Cells - Number columns */
    :global(.tabulator .tabulator-cell[tabulator-field^="N"]) {
        font-weight: 600;
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-size: 14px;
        color: #475569;
    }

    :global(.dark .tabulator .tabulator-cell[tabulator-field^="N"]) {
        color: #cbd5e1;
    }

    /* Editable Cell Styling */
    :global(.tabulator .tabulator-cell.tabulator-editing) {
        background: #eff6ff !important;
        border: 2px solid #3b82f6 !important;
        border-radius: 6px;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        padding: 8px 10px;
    }

    :global(.dark .tabulator .tabulator-cell.tabulator-editing) {
        background: #1e3a8a !important;
        border-color: #60a5fa !important;
    }

    :global(.tabulator .tabulator-cell.tabulator-editing input) {
        background: transparent;
        border: none;
        outline: none;
        color: #0f172a;
        font-weight: 600;
        font-family: "Inter", sans-serif;
        font-size: 14px;
    }

    /* Hide spin buttons for number inputs */
    :global(
            .tabulator
                .tabulator-cell.tabulator-editing
                input[type="number"]::-webkit-inner-spin-button
        ),
    :global(
            .tabulator
                .tabulator-cell.tabulator-editing
                input[type="number"]::-webkit-outer-spin-button
        ) {
        -webkit-appearance: none;
        margin: 0;
    }

    :global(.tabulator .tabulator-cell.tabulator-editing input[type="number"]) {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    :global(.dark .tabulator .tabulator-cell.tabulator-editing input) {
        color: #f1f5f9;
    }

    /* ===== FROZEN COLUMNS ===== */
    :global(.tabulator .tabulator-frozen) {
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    :global(.tabulator .tabulator-frozen.tabulator-frozen-left) {
        border-right: 2px solid #e5e7eb !important;
    }

    :global(.dark .tabulator .tabulator-frozen.tabulator-frozen-left) {
        border-right-color: #374151 !important;
    }

    /* ===== PLACEHOLDER ===== */
    :global(.tabulator .tabulator-placeholder) {
        color: #64748b !important;
        font-size: 16px;
        font-weight: 500;
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 12px;
        margin: 20px;
    }

    :global(.dark .tabulator .tabulator-placeholder) {
        color: #94a3b8 !important;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }

    /* ===== SCROLLBARS ===== */
    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar) {
        width: 10px;
        height: 10px;
    }

    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar-track) {
        background: #f1f5f9;
        border-radius: 10px;
    }

    :global(.dark .tabulator .tabulator-tableholder::-webkit-scrollbar-track) {
        background: #0f172a;
    }

    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar-thumb) {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 10px;
        border: 2px solid #f1f5f9;
    }

    :global(.dark .tabulator .tabulator-tableholder::-webkit-scrollbar-thumb) {
        border-color: #0f172a;
    }

    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar-thumb:hover) {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .grades-table-container {
            padding: 12px;
            border-radius: 12px;
        }

        :global(.tabulator .tabulator-header .tabulator-col) {
            padding: 10px 6px;
        }

        :global(.tabulator .tabulator-cell) {
            padding: 8px 6px;
            font-size: 13px;
        }

        :global(.tabulator .tabulator-cell[tabulator-field="Val"]) {
            font-size: 14px;
        }
    }

    /* ===== GRADE COLOR INDICATORS ===== */
    /* Excellent (≥ 4.5) - Emerald */
    :global(.tabulator .tabulator-cell .grade-excellent) {
        color: #059669 !important;
        font-weight: 700;
    }

    :global(.dark .tabulator .tabulator-cell .grade-excellent) {
        color: #10b981 !important;
    }

    /* Good (≥ 4.0) - Blue */
    :global(.tabulator .tabulator-cell .grade-good) {
        color: #2563eb !important;
        font-weight: 700;
    }

    :global(.dark .tabulator .tabulator-cell .grade-good) {
        color: #3b82f6 !important;
    }

    /* Acceptable (≥ 3.0) - Amber */
    :global(.tabulator .tabulator-cell .grade-acceptable) {
        color: #d97706 !important;
        font-weight: 600;
    }

    :global(.dark .tabulator .tabulator-cell .grade-acceptable) {
        color: #f59e0b !important;
    }

    /* Low (< 3.0) - Rose */
    :global(.tabulator .tabulator-cell .grade-low) {
        color: #dc2626 !important;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(220, 38, 38, 0.1);
    }

    :global(.dark .tabulator .tabulator-cell .grade-low) {
        color: #f87171 !important;
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(248, 250, 252, 0.95);
        backdrop-filter: blur(8px);
        z-index: 1000;
        border-radius: 16px;
    }

    :global(.dark) .loading-overlay {
        background: rgba(15, 23, 42, 0.95);
    }

    .blur-content {
        filter: blur(4px);
        opacity: 0.5;
        pointer-events: none;
    }

    /* Loading Spinner */
    .loading-spinner {
        position: relative;
        width: 80px;
        height: 80px;
        margin-bottom: 24px;
    }

    .spinner-ring {
        position: absolute;
        width: 80px;
        height: 80px;
        border: 4px solid transparent;
        border-top-color: #6366f1;
        border-right-color: #8b5cf6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .loading-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 40px;
        color: #6366f1;
        animation: pulse 1.5s ease-in-out infinite;
    }

    :global(.dark) .loading-icon {
        color: #8b5cf6;
    }

    .loading-text {
        font-size: 18px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 16px;
        font-family: "Inter", sans-serif;
    }

    :global(.dark) .loading-text {
        color: #cbd5e1;
    }

    /* Loading Dots */
    .loading-dots {
        display: flex;
        gap: 8px;
    }

    .loading-dots .dot {
        width: 8px;
        height: 8px;
        background: #6366f1;
        border-radius: 50%;
        animation: bounce 1.4s ease-in-out infinite;
    }

    :global(.dark) .loading-dots .dot {
        background: #8b5cf6;
    }

    .loading-dots .dot:nth-child(1) {
        animation-delay: 0s;
    }

    .loading-dots .dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .loading-dots .dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    /* Animations */
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes pulse {
        0%,
        100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        50% {
            transform: translate(-50%, -50%) scale(1.1);
            opacity: 0.8;
        }
    }

    @keyframes bounce {
        0%,
        80%,
        100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-12px);
        }
    }
</style>
