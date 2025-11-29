<script lang="ts">
    import { onMount, onDestroy } from "svelte";
    import { fade } from "svelte/transition";
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
    export let tableNotasId: string = "nativeGradesTable";
    export let docenteId: string;
    export let initialPeriodo: string | undefined = undefined;
    export let enableExport: boolean = true;
    export let enableSearch: boolean = true;
    export let enableAutoSave: boolean = false;
    export let autoSaveInterval: number = 30000;
    export let onAutoSave: ((changes: any[]) => Promise<void>) | null = null;
    export let enableUndoRedo: boolean = true;
    export let maxHistorySize: number = 50;

    // ========== REACTIVE STATE ==========
    $: docente = docenteId || $payload.Asignacion;
    $: asignatura = $selectedAsignatura;
    $: nivel = $payload.nivel;
    $: numero = $payload.numero;
    $: asignacion = $payload.Asignacion;
    $: year = $payload.year;

    // ========== COMPONENT STATE ==========
    let tableData: GradeData[] = [];
    let filteredData: GradeData[] = [];
    let isLoading = false;
    let searchQuery = "";
    let currentPeriodo = "";

    // Edición
    let editingCell: { rowId: string; field: string } | null = null;
    let editValue = "";

    // Cambios
    let pendingChanges: Map<string, any> = new Map();
    let isSaving = false;
    let lastSaveTime: Date | null = null;
    let saveError: string | null = null;

    // Undo/Redo
    let undoStack: HistoryEntry[] = [];
    let redoStack: HistoryEntry[] = [];

    // Diálogo
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

    // ========== COMPUTED ==========
    $: canUndo = undoStack.length > 0 && !isSaving;
    $: canRedo = redoStack.length > 0 && !isSaving;
    $: hasChanges = pendingChanges.size > 0;
    $: saveStatusText = getSaveStatusText();

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

    // ========== HELPER FUNCTIONS ==========

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

    const loadTableData = async () => {
        if (!docente || !asignatura) return;
        isLoading = true;

        try {
            const per = await getCurrentPeriodo();
            currentPeriodo = initialPeriodo || per;

            const res = await fetch(GET_NOTAS_ENDPOINT, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    docente,
                    nivel,
                    numero,
                    asignatura,
                    periodo: currentPeriodo,
                    asignacion,
                    year,
                }),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            let data = await res.json();
            data = data
                .map((item: any, i: number) => ({
                    ...item,
                    id: item.id || `row-${i}`,
                    Val: calculateRowVal(item),
                }))
                .sort((a: any, b: any) => a.Nombres.localeCompare(b.Nombres));

            tableData = data;
            applySearchFilter();
        } catch (error) {
            console.error("Error loading grades:", error);
            saveError = "Error al cargar datos";
        } finally {
            isLoading = false;
        }
    };

    const applySearchFilter = () => {
        if (!searchQuery.trim()) {
            filteredData = tableData;
        } else {
            const q = searchQuery.toLowerCase();
            filteredData = tableData.filter((row) =>
                row.Nombres.toLowerCase().includes(q),
            );
        }
    };

    const startEdit = (rowId: string, field: string, value: any) => {
        editingCell = { rowId, field };
        editValue = value ?? "";
    };

    const saveEdit = () => {
        if (!editingCell) return;
        const { rowId, field } = editingCell;
        let newValue = editValue.trim();

        if (newValue === "") {
            updateCellValue(rowId, field, "");
        } else {
            const clean = newValue.replace(/[^\d.]/g, "");
            const num = parseFloat(clean);
            if (isNaN(num)) {
                updateCellValue(rowId, field, "");
            } else {
                const clamped = Math.max(1, Math.min(5, num));
                updateCellValue(rowId, field, clamped.toFixed(1));
            }
        }
        editingCell = null;
    };

    const cancelEdit = () => {
        editingCell = null;
    };

    const updateCellValue = (
        rowId: string,
        field: string,
        newValue: string,
    ) => {
        const row = tableData.find((r) => r.id === rowId);
        if (!row) return;

        const oldValue = row[field as keyof GradeData] as string;
        if (oldValue === newValue) return;

        if (field.startsWith("N") && newValue !== "") {
            const idx = extractColumnNumber(field);
            const fechaKey = `fecha${idx}`;
            if (!row[fechaKey as keyof GradeData]) {
                row[fechaKey as keyof GradeData] = toDateInputValue(
                    new Date(),
                ) as any;
            }
        }

        (row[field as keyof GradeData] as any) = newValue;
        row.Val = calculateRowVal(row);

        const key = `${rowId}-${field}`;
        pendingChanges.set(key, { rowId, field, newValue });
        if (enableUndoRedo) {
            undoStack.push({
                rowId,
                field,
                oldValue,
                newValue,
                timestamp: Date.now(),
            });
            if (undoStack.length > maxHistorySize) undoStack.shift();
            redoStack = [];
        }

        applySearchFilter();
        if (enableAutoSave) debouncedSave();
    };

    const performUndo = () => {
        if (!canUndo) return;
        const entry = undoStack.pop();
        if (!entry) return;
        redoStack.push(entry);

        const row = tableData.find((r) => r.id === entry.rowId);
        if (row) {
            (row[entry.field as keyof GradeData] as any) = entry.oldValue;
            row.Val = calculateRowVal(row);
            const key = `${entry.rowId}-${entry.field}`;
            pendingChanges.delete(key);
            applySearchFilter();
        }
    };

    const performRedo = () => {
        if (!canRedo) return;
        const entry = redoStack.pop();
        if (!entry) return;
        undoStack.push(entry);

        const row = tableData.find((r) => r.id === entry.rowId);
        if (row) {
            (row[entry.field as keyof GradeData] as any) = entry.newValue;
            row.Val = calculateRowVal(row);
            const key = `${entry.rowId}-${entry.field}`;
            pendingChanges.set(key, {
                rowId: entry.rowId,
                field: entry.field,
                newValue: entry.newValue,
            });
            applySearchFilter();
        }
    };

    const saveChanges = async () => {
        if (!hasChanges || isSaving || !onAutoSave) return;
        isSaving = true;
        saveError = null;
        try {
            const changes = Array.from(pendingChanges.values());
            await onAutoSave(changes);
            pendingChanges.clear();
            lastSaveTime = new Date();
        } catch (error: any) {
            saveError = error.message || "Error al guardar";
        } finally {
            isSaving = false;
        }
    };

    const debouncedSave = debounce(saveChanges, 2000);

    const exportToCSV = () => {
        if (tableData.length === 0) return;
        const headers = [
            "Nombres",
            "Val",
            ...Array.from({ length: 12 }, (_, i) => `N${i + 1}`),
        ];
        const rows = tableData.map((row) =>
            headers
                .map(
                    (h) =>
                        `"${String(row[h as keyof GradeData] ?? "").replace(/"/g, '""')}"`,
                )
                .join(","),
        );
        const csv = [headers.join(","), ...rows].join("\n");
        downloadFile(
            `Notas_${asignatura}_${currentPeriodo}_${year}.csv`,
            csv,
            "text/csv",
        );
    };

    const exportToExcel = () => {
        exportToCSV(); // Fallback a CSV
    };

    const downloadFile = (
        filename: string,
        content: string,
        contentType: string,
    ) => {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    const handleHeaderClick = (columnName: string) => {
        if (!columnName.startsWith("N") || columnName === "Nombres") return;
        if (tableData.length === 0) return;

        const firstRow = tableData[0];
        const columnNumber = extractColumnNumber(columnName);
        dialogData = {
            columnName,
            aspecto: firstRow[`aspecto${columnNumber}`] || "No disponible",
            porcentaje:
                firstRow[`porcentaje${columnNumber}`] || "No disponible",
            fechaa: firstRow[`fechaa${columnNumber}`] || "No disponible",
            fecha: firstRow[`fecha${columnNumber}`] || "No disponible",
        };
        showDialog = true;
    };

    const handleGlobalKeyDown = (e: KeyboardEvent) => {
        if (e.ctrlKey && e.key === "z" && !e.shiftKey) {
            e.preventDefault();
            performUndo();
        }
        if (
            (e.ctrlKey && e.shiftKey && e.key === "z") ||
            (e.ctrlKey && e.key === "y")
        ) {
            e.preventDefault();
            performRedo();
        }
        if (e.ctrlKey && e.key === "s") {
            e.preventDefault();
            saveChanges();
        }
        if (e.ctrlKey && e.key === "f") {
            e.preventDefault();
            document.getElementById(`${tableNotasId}-search`)?.focus();
        }
    };

    const handleTableClick = (e: MouseEvent) => {
        if (editingCell) {
            const target = e.target as HTMLElement;
            if (
                !target.closest(".editable-cell") &&
                !target.closest(".edit-input")
            ) {
                saveEdit();
            }
        }
    };

    onMount(() => {
        window.addEventListener("keydown", handleGlobalKeyDown);
        document.addEventListener("click", handleTableClick);
        loadTableData();

        let interval: any = null;
        if (enableAutoSave && onAutoSave) {
            interval = setInterval(() => {
                if (hasChanges && !isSaving) saveChanges();
            }, autoSaveInterval);
        }

        return () => {
            window.removeEventListener("keydown", handleGlobalKeyDown);
            document.removeEventListener("click", handleTableClick);
            if (interval) clearInterval(interval);
        };
    });

    onDestroy(() => {
        // Cleanup
    });

    $: if (docente && asignatura) {
        loadTableData();
    }

    $: applySearchFilter();
</script>

<!-- ========== TOOLBAR ========== -->
<div class="grades-toolbar">
    {#if enableSearch}
        <div class="search-container">
            <span class="material-symbols-rounded search-icon">search</span>
            <input
                type="text"
                id="{tableNotasId}-search"
                bind:value={searchQuery}
                placeholder="Buscar estudiante..."
                class="search-input"
            />
            {#if searchQuery}
                <button
                    class="clear-search-btn"
                    on:click={() => (searchQuery = "")}
                >
                    <span class="material-symbols-rounded">close</span>
                </button>
            {/if}
        </div>
    {/if}

    <div class="toolbar-actions">
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
                    <button class="save-now-btn" on:click={saveChanges}
                        >Guardar ahora</button
                    >
                {/if}
            </div>
        {/if}
    </div>
</div>

<!-- ========== TABLE ========== -->
<div class="native-table-container" id={tableNotasId}>
    {#if isLoading}
        <div class="loading-overlay" transition:fade>
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <span class="material-symbols-rounded loading-icon">school</span
                >
            </div>
            <p class="loading-text">Cargando calificaciones...</p>
        </div>
    {/if}

    <div class="table-wrapper">
        <table class="native-table">
            <thead>
                <tr>
                    <th style="width: 250px;">Nombres</th>
                    <th style="width: 60px;">Val</th>
                    {#each Array(12) as _, i}
                        <th
                            class="grade-header"
                            on:click={() => handleHeaderClick(`N${i + 1}`)}
                            title="Click para ver detalles"
                        >
                            N{i + 1}
                        </th>
                    {/each}
                </tr>
            </thead>
            <tbody>
                {#each filteredData as row (row.id)}
                    <tr>
                        <td>{row.Nombres}</td>
                        <td
                            class="val-cell {parseFloat(row.Val) < 3.0
                                ? 'low-grade'
                                : ''}"
                        >
                            {gradeFormatter(row.Val)}
                        </td>
                        {#each Array(12) as _, i}
                            {#if editingCell && editingCell.rowId === row.id && editingCell.field === `N${i + 1}`}
                                <td class="editable-cell">
                                    <input
                                        type="text"
                                        class="edit-input"
                                        bind:value={editValue}
                                        on:focus={(e) => {
                                            if (e.target)
                                                (
                                                    e.target as HTMLInputElement
                                                ).select();
                                        }}
                                        on:keydown={(e) => {
                                            if (e.key === "Enter") saveEdit();
                                            if (e.key === "Escape")
                                                cancelEdit();
                                        }}
                                        on:blur={saveEdit}
                                    />
                                </td>
                            {:else}
                                <td
                                    class="editable-cell {parseFloat(
                                        row[`N${i + 1}`],
                                    ) < 3.0
                                        ? 'low-grade'
                                        : ''}"
                                    on:dblclick={() =>
                                        startEdit(
                                            row.id,
                                            `N${i + 1}`,
                                            row[`N${i + 1}`],
                                        )}
                                    on:keydown={(e) => {
                                        if (e.key === "Enter") {
                                            e.preventDefault();
                                            startEdit(
                                                row.id,
                                                `N${i + 1}`,
                                                row[`N${i + 1}`],
                                            );
                                        }
                                    }}
                                    tabindex="0"
                                >
                                    {gradeFormatter(row[`N${i + 1}`])}
                                </td>
                            {/if}
                        {/each}
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>
</div>

<!-- ========== DIALOG ========== -->
<GradeDetailsDialog
    show={showDialog}
    columnName={dialogData.columnName}
    aspecto={dialogData.aspecto}
    porcentaje={dialogData.porcentaje}
    fechaa={dialogData.fechaa}
    fecha={dialogData.fecha}
    onClose={() => (showDialog = false)}
/>

<!-- ========== STYLES ========== -->
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

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
    }

    .search-input {
        width: 100%;
        padding: 10px 40px 10px 44px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-family: "Inter", sans-serif;
        background: white;
        color: #1e293b;
    }

    :global(.dark) .search-input {
        background: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
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
        font-family: "Inter", sans-serif;
        cursor: pointer;
    }

    :global(.dark) .toolbar-btn {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
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
        color: #64748b;
    }

    :global(.dark) .save-status {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
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

    .save-now-btn {
        padding: 4px 10px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: none;
        border-radius: 6px;
        color: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .spinning {
        animation: spin 1s linear infinite;
    }

    .native-table-container {
        position: relative;
        height: 100%;
        min-height: 500px;
        padding: 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 0 0 16px 16px;
    }

    :global(.dark) .native-table-container {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    .table-wrapper {
        max-height: 600px;
        overflow: auto;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .native-table {
        width: 100%;
        border-collapse: collapse;
        font-family: "Inter", sans-serif;
        font-size: 14px;
    }

    .native-table th,
    .native-table td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    :global(.dark) .native-table th,
    :global(.dark) .native-table td {
        border-bottom-color: #334155;
        color: #e2e8f0;
    }

    .native-table th {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-weight: 600;
        position: sticky;
        top: 0;
    }

    .val-cell,
    .editable-cell {
        text-align: right;
    }

    .editable-cell {
        cursor: pointer;
        outline: none;
        transition: background 0.2s;
    }

    .editable-cell:hover {
        background: #f1f5f9;
    }

    :global(.dark) .editable-cell:hover {
        background: #334155;
    }

    .edit-input {
        width: 100%;
        padding: 4px 8px;
        border: 2px solid #3b82f6;
        border-radius: 4px;
        text-align: right;
        font-family: "Inter", sans-serif;
        font-size: 14px;
        outline: none;
        background: white;
        color: #1e293b;
    }

    :global(.dark) .edit-input {
        background: #1e293b;
        color: #e2e8f0;
        border-color: #60a5fa;
    }

    .grade-header {
        cursor: pointer;
        text-align: center;
    }

    .grade-header:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .low-grade {
        color: #ef4444;
        font-weight: bold;
    }

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
        background: rgba(255, 255, 255, 0.9);
        z-index: 10;
        border-radius: 16px;
    }

    :global(.dark) .loading-overlay {
        background: rgba(0, 0, 0, 0.8);
    }

    .spinner-ring {
        width: 40px;
        height: 40px;
        border: 3px solid transparent;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 16px;
    }

    .loading-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 24px;
        color: white;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {
        .native-table-container {
            padding: 10px;
        }
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
        .btn-text {
            display: none;
        }
    }
</style>
