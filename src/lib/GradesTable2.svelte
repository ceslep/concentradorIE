<script lang="ts">
    /**
     * GradesTable2 - Enhanced Grades Table Component
     *
     * A highly optimized, feature-rich grades table using Tabulator 6.3
     *
     * Features:
     * - Virtual scrolling for performance
     * - Inline editing with validation
     * - Export to Excel/CSV
     * - Column filters and global search
     * - Auto-save functionality (optional)
     * - Undo/Redo support
     * - Responsive design with dark mode
     * - Keyboard shortcuts
     *
     * @component
     */
    import { onMount, onDestroy, tick } from "svelte";
    import { fade, scale } from "svelte/transition";
    import {
        TabulatorFull as Tabulator,
        type ColumnDefinition,
        type CellComponent,
        type RowComponent,
    } from "tabulator-tables";
    import "tabulator-tables/dist/css/tabulator.min.css";
    import {
        GET_NOTAS_ENDPOINT,
        GET_PERIODOS_NOTAS_ENDPOINT,
    } from "../../constants";
    import { payload, selectedAsignatura } from "./storeConcentrador";
    import GradeDetailsDialog from "./GradeDetailsDialog.svelte";
    import {
        calculateRowVal,
        gradeFormatter,
        validateGrade,
        toDateInputValue,
        debounce,
        extractColumnNumber,
        type GradeData,
        type HistoryEntry,
    } from "./utils/gradeTableUtils";

    // ========== PROPS ==========

    /** Unique ID for the table element */
    export let tableNotasId: string = "gradesTable2";

    /** Teacher/Docente ID */
    export let docenteId: string;

    /** Initial period to load */
    export let initialPeriodo: string | undefined = undefined;

    /** Enable export functionality */
    export let enableExport: boolean = true;

    /** Enable column filters */
    export let enableFilters: boolean = true;

    /** Enable global search */
    export let enableSearch: boolean = true;

    /** Enable auto-save (requires onAutoSave callback) */
    export let enableAutoSave: boolean = false;

    /** Auto-save interval in milliseconds */
    export let autoSaveInterval: number = 30000;

    /** Callback for auto-save functionality */
    export let onAutoSave: ((changes: any[]) => Promise<void>) | null = null;

    /** Enable undo/redo functionality */
    export let enableUndoRedo: boolean = true;

    /** Maximum undo history size */
    export let maxHistorySize: number = 50;

    // ========== REACTIVE STATE ==========

    $: docente = docenteId || $payload.Asignacion;
    $: asignatura = $selectedAsignatura;
    $: nivel = $payload.nivel;
    $: numero = $payload.numero;
    $: asignacion = $payload.Asignacion;
    $: year = $payload.year;

    // ========== COMPONENT STATE ==========

    let tableInstance: any;
    let mounted = false;
    let currentPeriodo: string = "";
    let isLoading = false;
    let searchQuery = "";

    // Changes tracking
    let pendingChanges: Map<string, any> = new Map();
    let isSaving = false;
    let lastSaveTime: Date | null = null;
    let saveError: string | null = null;

    // Undo/Redo state
    let undoStack: HistoryEntry[] = [];
    let redoStack: HistoryEntry[] = [];

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

    // ========== COMPUTED VALUES ==========

    $: canUndo = undoStack.length > 0 && !isSaving;
    $: canRedo = redoStack.length > 0 && !isSaving;
    $: hasChanges = pendingChanges.size > 0;
    $: saveStatusText = getSaveStatusText();

    // ========== HELPER FUNCTIONS ==========

    function getSaveStatusText(): string {
        if (isSaving) return "Guardando...";
        if (saveError) return `Error: ${saveError}`;
        if (hasChanges) return `${pendingChanges.size} cambios pendientes`;
        if (lastSaveTime) {
            const elapsed = Date.now() - lastSaveTime.getTime();
            if (elapsed < 60000) return "Guardado hace un momento";
            return `Guardado hace ${Math.floor(elapsed / 60000)} min`;
        }
        return "Sin cambios";
    }

    /**
     * Fetches the current active period from the backend
     */
    const getCurrentPeriodo = async (): Promise<string> => {
        try {
            const res = await fetch(GET_PERIODOS_NOTAS_ENDPOINT);
            const periodos = await res.json();
            return (
                periodos.find((p: any) => p.selected === "selected")?.nombre ||
                "DefaultPeriod"
            );
        } catch (error) {
            console.error("Error fetching periods:", error);
            return "DefaultPeriod";
        }
    };

    /**
     * Handles header click to show grade details dialog
     */
    const handleHeaderClick = (columnName: string) => {
        // Only handle N1-N12 columns
        if (!columnName.startsWith("N") || columnName === "Nombres") return;

        const columnNumber = extractColumnNumber(columnName);

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

    /**
     * Adds an action to the undo stack
     */
    const pushToUndoStack = (entry: HistoryEntry) => {
        if (!enableUndoRedo) return;

        undoStack.push(entry);
        if (undoStack.length > maxHistorySize) {
            undoStack.shift(); // Remove oldest entry
        }
        redoStack = []; // Clear redo stack on new action
    };

    /**
     * Tracks a cell change for auto-save and undo
     */
    const trackChange = (
        rowId: string,
        field: string,
        oldValue: any,
        newValue: any,
    ) => {
        const key = `${rowId}-${field}`;
        pendingChanges.set(key, { rowId, field, newValue });

        if (enableUndoRedo) {
            pushToUndoStack({
                rowId,
                field,
                oldValue,
                newValue,
                timestamp: Date.now(),
            });
        }
    };

    /**
     * Performs undo action
     */
    const performUndo = () => {
        if (!canUndo || !tableInstance) return;

        const entry = undoStack.pop();
        if (!entry) return;

        redoStack.push(entry);

        // Find and update the row
        const rows = tableInstance.getRows();
        const row = rows.find((r: any) => r.getData().id === entry.rowId);

        if (row) {
            const data = row.getData();
            data[entry.field] = entry.oldValue;

            // Recalculate Val
            if (entry.field.startsWith("N")) {
                data.Val = calculateRowVal(data);
            }

            row.update(data);

            // Remove from pending changes
            const key = `${entry.rowId}-${entry.field}`;
            pendingChanges.delete(key);
        }
    };

    /**
     * Performs redo action
     */
    const performRedo = () => {
        if (!canRedo || !tableInstance) return;

        const entry = redoStack.pop();
        if (!entry) return;

        undoStack.push(entry);

        // Find and update the row
        const rows = tableInstance.getRows();
        const row = rows.find((r: any) => r.getData().id === entry.rowId);

        if (row) {
            const data = row.getData();
            data[entry.field] = entry.newValue;

            // Recalculate Val
            if (entry.field.startsWith("N")) {
                data.Val = calculateRowVal(data);
            }

            row.update(data);

            // Add back to pending changes
            const key = `${entry.rowId}-${entry.field}`;
            pendingChanges.set(key, {
                rowId: entry.rowId,
                field: entry.field,
                newValue: entry.newValue,
            });
        }
    };

    /**
     * Saves pending changes (auto-save or manual)
     */
    const saveChanges = async () => {
        if (!hasChanges || isSaving || !onAutoSave) return;

        isSaving = true;
        saveError = null;

        try {
            const changes = Array.from(pendingChanges.values());
            await onAutoSave(changes);

            pendingChanges.clear();
            lastSaveTime = new Date();
            pendingChanges = new Map(); // Trigger reactivity
        } catch (error: any) {
            saveError = error.message || "Error al guardar";
            console.error("Save error:", error);
        } finally {
            isSaving = false;
        }
    };

    // Debounced save for auto-save
    const debouncedSave = debounce(saveChanges, 2000);

    /**
     * Exports table data to Excel format
     */
    const exportToExcel = () => {
        if (!tableInstance) return;

        const filename = `Notas_${asignatura}_${currentPeriodo}_${year}.xlsx`;
        tableInstance.download("xlsx", filename, {
            sheetName: "Calificaciones",
        });
    };

    /**
     * Exports table data to CSV format
     */
    const exportToCSV = () => {
        if (!tableInstance) return;

        const filename = `Notas_${asignatura}_${currentPeriodo}_${year}.csv`;
        tableInstance.download("csv", filename);
    };

    /**
     * Handles global search across all columns
     */
    const handleSearch = () => {
        if (!tableInstance) return;

        if (searchQuery.trim() === "") {
            tableInstance.clearFilter();
        } else {
            tableInstance.setFilter([
                { field: "Nombres", type: "like", value: searchQuery },
            ]);
        }
    };

    /**
     * Clears all filters and search
     */
    const clearFilters = () => {
        searchQuery = "";
        if (tableInstance) {
            tableInstance.clearFilter();
            tableInstance.clearHeaderFilter();
        }
    };

    /**
     * Creates a grade column definition
     */
    function createGradeColumn(title: string, field: string): ColumnDefinition {
        return {
            title: title,
            field: field,
            editor: "input",
            editorParams: {
                selectContents: true,
            },
            hozAlign: "right",
            validator: [
                { type: validateGrade, parameters: { min: 1, max: 5 } },
            ],
            formatter: gradeFormatter,
            headerFilter: enableFilters ? "input" : undefined,
            headerFilterPlaceholder: "Filtrar...",
            headerClick: (e: any, column: any) => {
                handleHeaderClick(column.getField());
            },
            headerTooltip: (column: any) => {
                const field = column.getField();
                return `Click para ver detalles de ${field}`;
            },
            cellEdited: (cell: CellComponent) => {
                const row = cell.getRow();
                const data = row.getData();
                const oldValue = data[field];

                // Obtener el valor actual de la celda (ya procesado por el editor)
                let cellValue = cell.getValue();

                // Limpiar y validar el input
                if (
                    cellValue !== null &&
                    cellValue !== undefined &&
                    cellValue !== ""
                ) {
                    // Eliminar caracteres no numéricos excepto el punto
                    cellValue = String(cellValue).replace(/[^\d.]/g, "");

                    const numValue = parseFloat(cellValue);

                    if (!isNaN(numValue)) {
                        // Asegurar que esté entre 1 y 5
                        const clampedValue = Math.max(1, Math.min(5, numValue));
                        // Formatear a 1 decimal
                        const formattedValue = clampedValue.toFixed(1);

                        // ✅ USAR cell.setValue() PARA ACTUALIZAR LA CELDA
                        cell.setValue(formattedValue);

                        // Actualizar la fecha si es necesario
                        const idx = extractColumnNumber(field);
                        const fechaKey = `fecha${idx}`;
                        if (!data[fechaKey])
                            data[fechaKey] = toDateInputValue(new Date());

                        // Recalcular Val
                        data.Val = calculateRowVal(data as GradeData);

                        // Trackear el cambio
                        const rowId = data.id || data.Nombres;
                        trackChange(rowId, field, oldValue, formattedValue);

                        // Resaltar la celda modificada
                        cell.getElement().classList.add("cell-modified");
                        cell.getElement().offsetHeight; // Force reflow to restart animation if needed

                        // ✅ ACTUALIZAR LA FILA COMPLETA PARA QUE TABULATOR REFLEJE LOS CAMBIOS
                        row.update(data);

                        // Trigger auto-save if enabled
                        if (enableAutoSave) {
                            debouncedSave();
                        }
                    } else {
                        // Si no es un número válido, establecer vacío
                        cell.setValue("");
                        data[field] = "";
                        data.Val = calculateRowVal(data as GradeData);
                        row.update(data);
                    }
                } else {
                    // Si el valor es vacío o nulo
                    cell.setValue("");
                    data[field] = "";
                    data.Val = calculateRowVal(data as GradeData);
                    row.update(data);
                }
            },
        };
    }

    /**
     * Loads and initializes the Tabulator table
     */
    const loadTable = async () => {
        if (!docente) return;

        isLoading = true;

        await tick();
        const element = document.getElementById(tableNotasId);
        if (!element) {
            isLoading = false;
            return;
        }

        const per = await getCurrentPeriodo();
        const Elperiodo = initialPeriodo || per;
        currentPeriodo = Elperiodo;

        if (tableInstance) tableInstance.destroy();

        const isMobile = window.innerWidth < 768;

        tableInstance = new Tabulator(element, {
            height: "100%",
            layout: "fitDataTable",
            renderVertical: "virtual",
            placeholder: "No hay datos disponibles",
            reactiveData: true,
            ajaxContentType: "json",
            responsiveLayout: false,
            editTriggerEvent: "click",
            keybindings: {
                navUp: "up",
                navDown: "down",
                navLeft: "left",
                navRight: "right",
            },
            columnDefaults: {
                headerSort: false,
                headerHozAlign: "center",
            },
            columns: [
                {
                    title: "Nombres",
                    field: "Nombres",
                    width: isMobile ? 200 : 300,
                    frozen: !isMobile,
                    headerFilter: enableFilters ? "input" : undefined,
                    headerFilterPlaceholder: "Buscar...",
                },
                {
                    title: "Val",
                    field: "Val",
                    hozAlign: "center",
                    width: 60,
                    formatter: gradeFormatter,
                    frozen: !isMobile,
                    tooltip: "Valoración final calculada",
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
                asignacion,
                year,
            },
            ajaxConfig: {
                method: "POST",
                headers: { "Content-type": "application/json; charset=utf-8" },
            },
            ajaxResponse: (_url, _params, response: any[]) => {
                // Pre-calculate Val
                const processedData = response
                    .map((item, index) => {
                        item.id = item.id || `row-${index}`;
                        item.Val = calculateRowVal(item);
                        return item;
                    })
                    .sort((a, b) => a.Nombres.localeCompare(b.Nombres));

                return processedData;
            },
            ajaxRequestFunc: async (url: string, config: any, params: any) => {
                try {
                    const response = await fetch(url, {
                        method: config.method,
                        headers: config.headers,
                        body: JSON.stringify(params),
                    });

                    if (!response.ok) {
                        throw new Error(
                            `HTTP error! status: ${response.status}`,
                        );
                    }

                    const data = await response.json();

                    // Hide loading after data is received
                    setTimeout(() => {
                        isLoading = false;
                    }, 200);

                    return data;
                } catch (error) {
                    console.error("Error loading grades:", error);
                    isLoading = false;
                    saveError = "Error al cargar datos";
                    throw error;
                }
            },
        });
    };

    // ========== KEYBOARD SHORTCUTS ==========

    const handleKeyboardShortcuts = (e: KeyboardEvent) => {
        // Ctrl+Z: Undo
        if (e.ctrlKey && e.key === "z" && !e.shiftKey) {
            e.preventDefault();
            performUndo();
        }

        // Ctrl+Shift+Z or Ctrl+Y: Redo
        if (
            (e.ctrlKey && e.shiftKey && e.key === "z") ||
            (e.ctrlKey && e.key === "y")
        ) {
            e.preventDefault();
            performRedo();
        }

        // Ctrl+S: Save
        if (e.ctrlKey && e.key === "s") {
            e.preventDefault();
            saveChanges();
        }

        // Ctrl+F: Focus search
        if (e.ctrlKey && e.key === "f") {
            e.preventDefault();
            document.getElementById(`${tableNotasId}-search`)?.focus();
        }
    };

    // ========== LIFECYCLE ==========

    onMount(() => {
        // Initialize periodo asynchronously
        getCurrentPeriodo().then((periodo) => {
            currentPeriodo = periodo;
        });

        mounted = true;

        // Add keyboard shortcuts
        window.addEventListener("keydown", handleKeyboardShortcuts);

        // Auto-save interval
        let interval: ReturnType<typeof setInterval> | null = null;
        if (enableAutoSave && onAutoSave) {
            interval = setInterval(() => {
                if (hasChanges && !isSaving) {
                    saveChanges();
                }
            }, autoSaveInterval);
        }

        return () => {
            if (interval) clearInterval(interval);
        };
    });

    onDestroy(() => {
        mounted = false;
        if (tableInstance) tableInstance.destroy();
        window.removeEventListener("keydown", handleKeyboardShortcuts);
    });

    // Trigger load when dependencies change
    $: if (mounted && docente && asignatura) {
        loadTable();
    }
</script>

<!-- ========== TOOLBAR ========== -->
<div class="grades-toolbar">
    <!-- Search -->
    {#if enableSearch}
        <div class="search-container">
            <span class="material-symbols-rounded search-icon">search</span>
            <input
                type="text"
                id="{tableNotasId}-search"
                bind:value={searchQuery}
                on:input={handleSearch}
                placeholder="Buscar estudiante..."
                class="search-input"
            />
            {#if searchQuery}
                <button
                    class="clear-search-btn"
                    on:click={clearFilters}
                    title="Limpiar búsqueda"
                >
                    <span class="material-symbols-rounded">close</span>
                </button>
            {/if}
        </div>
    {/if}

    <div class="toolbar-actions">
        <!-- Undo/Redo -->
        {#if enableUndoRedo}
            <button
                class="toolbar-btn"
                on:click={performUndo}
                disabled={!canUndo}
                title="Deshacer (Ctrl+Z)"
            >
                <span class="material-symbols-rounded">undo</span>
            </button>
            <button
                class="toolbar-btn"
                on:click={performRedo}
                disabled={!canRedo}
                title="Rehacer (Ctrl+Y)"
            >
                <span class="material-symbols-rounded">redo</span>
            </button>
            <div class="toolbar-separator"></div>
        {/if}

        <!-- Export -->
        {#if enableExport}
            <button
                class="toolbar-btn"
                on:click={exportToExcel}
                title="Exportar a Excel"
            >
                <span class="material-symbols-rounded">download</span>
                <span class="btn-text">Excel</span>
            </button>
            <button
                class="toolbar-btn"
                on:click={exportToCSV}
                title="Exportar a CSV"
            >
                <span class="material-symbols-rounded">description</span>
                <span class="btn-text">CSV</span>
            </button>
            <div class="toolbar-separator"></div>
        {/if}

        <!-- Save Status -->
        {#if enableAutoSave}
            <div
                class="save-status"
                class:has-changes={hasChanges}
                class:saving={isSaving}
                class:error={saveError}
            >
                {#if isSaving}
                    <span class="material-symbols-rounded spinning">sync</span>
                {:else if saveError}
                    <span class="material-symbols-rounded">error</span>
                {:else if hasChanges}
                    <span class="material-symbols-rounded">edit</span>
                {:else}
                    <span class="material-symbols-rounded">check_circle</span>
                {/if}
                <span class="save-status-text">{saveStatusText}</span>

                {#if hasChanges && !isSaving}
                    <button class="save-now-btn" on:click={saveChanges}>
                        Guardar ahora
                    </button>
                {/if}
            </div>
        {/if}
    </div>
</div>

<!-- ========== TABLE CONTAINER ========== -->
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

<!-- ========== GRADE DETAILS DIALOG ========== -->
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

    /* ===== TOOLBAR ===== */
    .grades-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px 12px 0 0;
        border-bottom: 2px solid #e2e8f0;
        flex-wrap: wrap;
    }

    :global(.dark) .grades-toolbar {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-bottom-color: #334155;
    }

    .search-container {
        position: relative;
        flex: 1;
        min-width: 250px;
        max-width: 400px;
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 20px;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 10px 40px 10px 44px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        font-family: "Inter", sans-serif;
        color: #1e293b;
        background: white;
        transition: all 0.2s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    :global(.dark) .search-input {
        background: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }

    :global(.dark) .search-input:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .clear-search-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .clear-search-btn:hover {
        background: #f1f5f9;
        color: #ef4444;
    }

    :global(.dark) .clear-search-btn:hover {
        background: #334155;
    }

    .toolbar-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .toolbar-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        color: #475569;
        font-size: 14px;
        font-weight: 500;
        font-family: "Inter", sans-serif;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .toolbar-btn:hover:not(:disabled) {
        background: #f8fafc;
        border-color: #6366f1;
        color: #6366f1;
        transform: translateY(-1px);
    }

    .toolbar-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    :global(.dark) .toolbar-btn {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    :global(.dark) .toolbar-btn:hover:not(:disabled) {
        background: #334155;
        border-color: #8b5cf6;
        color: #8b5cf6;
    }

    .toolbar-separator {
        width: 1px;
        height: 24px;
        background: #e2e8f0;
    }

    :global(.dark) .toolbar-separator {
        background: #334155;
    }

    .save-status {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
    }

    .save-status.has-changes {
        border-color: #f59e0b;
        color: #d97706;
    }

    .save-status.saving {
        border-color: #3b82f6;
        color: #2563eb;
    }

    .save-status.error {
        border-color: #ef4444;
        color: #dc2626;
    }

    :global(.dark) .save-status {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }

    .save-now-btn {
        padding: 4px 10px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: none;
        border-radius: 6px;
        color: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .save-now-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
    }

    .spinning {
        animation: spin 1s linear infinite;
    }

    /* ===== CONTAINER ===== */
    .grades-table-container {
        position: relative;
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
        border-radius: 0 0 16px 16px;
        padding: 20px;
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    :global(.dark) .grades-table-container {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    /* ===== TABULATOR STYLES ===== */
    :global(.tabulator) {
        font-family: "Inter", sans-serif !important;
        font-size: 14px;
        border: none !important;
        background: transparent !important;
        border-radius: 12px;
        overflow: hidden;
    }

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

    :global(.tabulator .tabulator-header .tabulator-col[tabulator-field^="N"]) {
        cursor: pointer;
    }

    :global(
            .tabulator
                .tabulator-header
                .tabulator-col[tabulator-field^="N"]:hover
        ) {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    /* Header Filter */
    :global(.tabulator .tabulator-header-filter input) {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 12px;
        color: #1e293b !important;
    }

    :global(.tabulator .tabulator-header-filter input:focus) {
        outline: none;
        border-color: #fbbf24 !important;
        box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
    }

    /* Table Body */
    :global(.tabulator .tabulator-tableholder) {
        background: white;
    }

    :global(.dark .tabulator .tabulator-tableholder) {
        background: #1e293b;
    }

    /* Rows */
    :global(.tabulator .tabulator-row) {
        background: white !important;
        border-bottom: 1px solid #e2e8f0 !important;
        transition: all 0.15s ease;
    }

    :global(.dark .tabulator .tabulator-row) {
        background: #1e293b !important;
        border-bottom-color: #334155 !important;
    }

    :global(.tabulator .tabulator-row:hover) {
        background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%) !important;
    }

    :global(.dark .tabulator .tabulator-row:hover) {
        background: linear-gradient(90deg, #334155 0%, #475569 100%) !important;
    }

    /* Cells */
    :global(.tabulator .tabulator-cell) {
        border-right: 1px solid #e2e8f0 !important;
        padding: 10px 12px;
        color: #1e293b;
        font-weight: 500;
    }

    :global(.dark .tabulator .tabulator-cell) {
        border-right-color: #334155 !important;
        color: #e2e8f0;
    }

    /* Modified cell indicator */
    :global(.tabulator .tabulator-cell.cell-modified) {
        background: #fef3c7 !important;
        border-left: 3px solid #f59e0b !important;
    }

    :global(.dark .tabulator .tabulator-cell.cell-modified) {
        background: #422006 !important;
        border-left-color: #f59e0b !important;
    }

    /* Editing Cell */
    :global(.tabulator .tabulator-cell.tabulator-editing) {
        background: #eff6ff !important;
        border: 2px solid #3b82f6 !important;
        border-radius: 6px;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
        text-align: right;
        width: 100%;
    }

    :global(.dark .tabulator .tabulator-cell.tabulator-editing input) {
        color: #f1f5f9;
    }

    /* Hide spin buttons for number inputs */
    :global(.tabulator .tabulator-cell input::-webkit-inner-spin-button),
    :global(.tabulator .tabulator-cell input::-webkit-outer-spin-button) {
        -webkit-appearance: none;
        margin: 0;
    }

    :global(.tabulator .tabulator-cell input[type="number"]) {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    :global(.tabulator .tabulator-cell input[type="text"]) {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    /* Frozen columns */
    :global(.tabulator .tabulator-frozen) {
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    /* Scrollbar */
    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar) {
        width: 10px;
        height: 10px;
    }

    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar-track) {
        background: #f1f5f9;
        border-radius: 10px;
    }

    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar-thumb) {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 10px;
        border: 2px solid #f1f5f9;
    }

    :global(.tabulator .tabulator-tableholder::-webkit-scrollbar-thumb:hover) {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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

    .loading-text {
        font-size: 18px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 16px;
    }

    :global(.dark) .loading-text {
        color: #cbd5e1;
    }

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

    .loading-dots .dot:nth-child(1) {
        animation-delay: 0s;
    }

    .loading-dots .dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .loading-dots .dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    /* ===== ANIMATIONS ===== */
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .grades-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-container {
            max-width: 100%;
        }

        .toolbar-actions {
            justify-content: center;
        }

        .save-status {
            flex-direction: column;
            text-align: center;
        }

        .btn-text {
            display: none;
        }
    }
</style>
