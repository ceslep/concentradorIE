<!-- 
GRADESTABLE2.SVELTE

DESCRIPCIÓN:
Componente de tabla de calificaciones de alto rendimiento (nativo). Permite la edición directa de notas con validación en tiempo real, navegación por teclado, deshacer/rehacer y guardado automático.

USO:
<GradesTable2 {docenteId} {periodo} bind:tableData bind:isLoading />

DEPENDENCIAS:
- Store: payload, selectedAsignatura (storeConcentrador.ts).
- Utils: calculateRowVal, gradeFormatter, validateGrade, etc. (gradeTableUtils.ts).
- Componentes: GradeDetailsDialog.svelte.

PROPS/EMIT:
- Prop: `tableNotasId` → string → ID único para el contenedor.
- Prop: `enableAutoSave` → boolean → Habilita el guardado automático de cambios.
- Prop: `onAutoSave` → function → Callback asíncrono para persistir cambios.

RELACIONES:
- Llamado por: GradesTableDialog.svelte.
- Llama a: GradeDetailsDialog.svelte.

NOTAS DE DESARROLLO:
- Implementa una arquitectura robusta de Undo/Redo basada en pilas (`undoStack`, `redoStack`).
- Optimizado para navegación rápida con flechas y validación automática de rango (1.0 - 5.0).

ESTILOS:
- Usa celdas editables nativas (`contenteditable`) con estilos 'low-grade' para notas reprobatorias.
-->

<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  import { fade } from "svelte/transition";
  import {
    GET_NOTAS_ENDPOINT,
    GET_PERIODOS_NOTAS_ENDPOINT,
  } from "../../../../../constants";
  import { payload, selectedAsignatura } from "../../../../storeConcentrador";
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
  } from "../../../../utils/gradeTableUtils";

  // ========== PROPS ==========
  export let tableNotasId: string = "nativeGradesTable";
  export let docenteId: string;
  export let periodo: string = "";
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
  export let tableData: GradeData[] = [];
  let filteredData: GradeData[] = [];
  export let isLoading = false;
  let searchQuery = "";
  let currentPeriodo = "";

  // Edición
  let editValue = ""; // Solo para validación temporal

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

  /**
   * Carga los datos de la tabla desde la API.
   * Inicializa las filas con IDs únicos y calcula el promedio inicial (Val).
   */
  const loadTableData = async () => {
    if (!docente || !asignatura) return;
    isLoading = true;

    try {
      currentPeriodo = periodo;

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

  /**
   * Gestiona la navegación entre celdas editables usando las flechas del teclado.
   * @param direction - Sentido del movimiento.
   * @param originElement - Elemento desde donde se origina el movimiento.
   */
  const navigateCell = (
    direction: "up" | "down" | "left" | "right",
    originElement?: HTMLElement,
  ) => {
    // Encontrar la celda actualmente enfocada o usar la pasada
    const activeElement =
      originElement || (document.activeElement as HTMLElement);
    if (!activeElement) return;

    const currentCell = activeElement.closest("td.editable-cell");
    if (!currentCell) return;

    const currentRow = currentCell.closest("tr");
    if (!currentRow) return;

    const cells = Array.from(currentRow.querySelectorAll("td.editable-cell"));
    const currentCellIndex = cells.indexOf(currentCell as Element);

    let targetCell: Element | null = null;

    if (direction === "left") {
      // Mover a la celda anterior en la misma fila
      targetCell = cells[currentCellIndex - 1] || null;
    } else if (direction === "right") {
      // Mover a la siguiente celda en la misma fila
      targetCell = cells[currentCellIndex + 1] || null;
    } else if (direction === "up" || direction === "down") {
      // Mover a la fila anterior o siguiente
      const allRows = Array.from(
        currentRow.parentElement?.querySelectorAll("tr") || [],
      );
      const currentRowIndex = allRows.indexOf(currentRow);

      const targetRowIndex =
        direction === "up" ? currentRowIndex - 1 : currentRowIndex + 1;
      const targetRow = allRows[targetRowIndex];

      if (targetRow) {
        const targetRowCells = Array.from(
          targetRow.querySelectorAll("td.editable-cell"),
        );
        targetCell = targetRowCells[currentCellIndex] || null;
      }
    }

    if (targetCell) {
      (targetCell as HTMLElement).focus();
    }
  };

  const updateCellValue = (rowId: string, field: string, newValue: string) => {
    const row = tableData.find((r) => r.id === rowId);
    if (!row) return;

    const oldValue = row[field as keyof GradeData] as string;
    if (oldValue === newValue) return;

    if (field.startsWith("N") && newValue !== "") {
      const idx = extractColumnNumber(field);
      const fechaKey = `fecha${idx}`;
      if (!row[fechaKey as keyof GradeData]) {
        row[fechaKey as keyof GradeData] = toDateInputValue(new Date()) as any;
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

  /**
   * Revierte el último cambio realizado (Undo).
   */
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
      porcentaje: firstRow[`porcentaje${columnNumber}`] || "No disponible",
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

  onMount(() => {
    window.addEventListener("keydown", handleGlobalKeyDown);
    loadTableData();

    let interval: any = null;
    if (enableAutoSave && onAutoSave) {
      interval = setInterval(() => {
        if (hasChanges && !isSaving) saveChanges();
      }, autoSaveInterval);
    }

    return () => {
      window.removeEventListener("keydown", handleGlobalKeyDown);
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
  <div class="toolbar-left">
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
            onclick={() => (searchQuery = "")}
            title="Limpiar búsqueda"
          >
            <span class="material-symbols-rounded">close</span>
          </button>
        {/if}
      </div>
    {/if}
  </div>

  <div class="toolbar-right">
    {#if enableUndoRedo}
      <div class="toolbar-group">
        <button
          class="toolbar-btn"
          onclick={performUndo}
          disabled={!canUndo}
          title="Deshacer (Ctrl+Z)"
        >
          <span class="material-symbols-rounded">undo</span>
        </button>
        <button
          class="toolbar-btn"
          onclick={performRedo}
          disabled={!canRedo}
          title="Rehacer (Ctrl+Y)"
        >
          <span class="material-symbols-rounded">redo</span>
        </button>
      </div>
    {/if}

    {#if enableExport}
      <div class="toolbar-group">
        <button
          class="toolbar-btn"
          onclick={exportToExcel}
          title="Exportar a Excel"
        >
          <span class="material-symbols-rounded">download</span>
          <span class="btn-text">Excel</span>
        </button>
        <button
          class="toolbar-btn"
          onclick={exportToCSV}
          title="Exportar a CSV"
        >
          <span class="material-symbols-rounded">description</span>
          <span class="btn-text">CSV</span>
        </button>
      </div>
    {/if}

    {#if enableAutoSave}
      <div class="toolbar-group">
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
            <button class="save-now-btn" onclick={saveChanges}>Guardar</button>
          {/if}
        </div>
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
        <span class="material-symbols-rounded loading-icon">school</span>
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
            <th class="grade-header">
              <button
                class="grade-header-button"
                onclick={() => handleHeaderClick(`N${i + 1}`)}
                title="Click para ver detalles"
              >
                N{i + 1}
              </button>
            </th>
          {/each}
        </tr>
      </thead>
      <tbody>
        {#each filteredData as row (row.id)}
          <tr>
            <td>{row.Nombres}</td>
            <td class="val-cell {parseFloat(row.Val) < 3.0 ? 'low-grade' : ''}">
              {gradeFormatter(row.Val)}
            </td>
            {#each Array(12) as _, i}
              <td
                class="editable-cell {parseFloat(row[`N${i + 1}`]) < 3.0
                  ? 'low-grade'
                  : ''}"
                contenteditable="true"
                data-row-id={row.id}
                data-field={`N${i + 1}`}
                onfocus={(e) => {
                  const target = e.target as HTMLElement;
                  editValue = target.textContent || "";
                  // Seleccionar todo el texto al enfocar
                  const selection = window.getSelection();
                  const range = document.createRange();
                  range.selectNodeContents(target);
                  selection?.removeAllRanges();
                  selection?.addRange(range);
                }}
                onblur={(e) => {
                  const target = e.target as HTMLElement;
                  const newValue = target.textContent?.trim() || "";
                  const rowId = target.dataset.rowId || "";
                  const field = target.dataset.field || "";

                  if (newValue === "") {
                    updateCellValue(rowId, field, "");
                    target.textContent = gradeFormatter("");
                  } else {
                    const clean = newValue.replace(/[^\d.]/g, "");
                    const num = parseFloat(clean);
                    if (isNaN(num)) {
                      updateCellValue(rowId, field, "");
                      target.textContent = gradeFormatter("");
                    } else {
                      const clamped = Math.max(1, Math.min(5, num));
                      const formattedValue = clamped.toFixed(1);
                      updateCellValue(rowId, field, formattedValue);
                      target.textContent = gradeFormatter(formattedValue);
                    }
                  }
                }}
                onkeydown={(e) => {
                  const target = e.target as HTMLElement;

                  if (e.key === "Enter") {
                    e.preventDefault();
                    if (e.altKey) {
                      navigateCell("left", target);
                    } else if (e.shiftKey) {
                      navigateCell("right", target);
                    } else {
                      navigateCell("down", target);
                    }
                  } else if (e.key === "Escape") {
                    e.preventDefault();
                    // Restaurar el valor original
                    target.textContent = gradeFormatter(row[`N${i + 1}`]);
                    target.blur();
                  } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    navigateCell("up", target);
                  } else if (e.key === "ArrowDown") {
                    e.preventDefault();
                    e.preventDefault();
                    target.blur();
                    setTimeout(() => navigateCell("left"), 10);
                  } else if (
                    e.key === "ArrowRight" &&
                    (e.target as HTMLElement).innerText.length === 0
                  ) {
                    e.preventDefault();
                    target.blur();
                    setTimeout(() => navigateCell("right"), 10);
                  }
                }}
                oninput={(e) => {
                  // Limitar la entrada solo a números y punto decimal
                  const target = e.target as HTMLElement;
                  const text = target.textContent || "";
                  const cleaned = text.replace(/[^\d.]/g, "");
                  if (text !== cleaned) {
                    target.textContent = cleaned;
                    // Mover el cursor al final
                    const selection = window.getSelection();
                    const range = document.createRange();
                    range.selectNodeContents(target);
                    range.collapse(false);
                    selection?.removeAllRanges();
                    selection?.addRange(range);
                  }
                }}
                tabindex="0"
              >
                {gradeFormatter(row[`N${i + 1}`])}
              </td>
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
    gap: 20px;
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

  .toolbar-left {
    flex: 1;
    min-width: 250px;
    display: flex;
    align-items: center;
  }

  .toolbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .toolbar-group {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 10px;
    border: 1px solid rgba(226, 232, 240, 0.5);
  }

  :global(.dark) .toolbar-group {
    background: rgba(30, 41, 59, 0.5);
    border-color: rgba(51, 65, 85, 0.5);
  }

  .search-container {
    position: relative;
    width: 100%;
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
    font-family: "Inter", sans-serif;
    font-size: 14px;
    background: white;
    color: #1e293b;
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
    border-radius: 4px;
    transition: all 0.2s ease;
  }

  .clear-search-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
  }

  :global(.dark) .clear-search-btn:hover {
    background: #334155;
    color: #e2e8f0;
  }

  .toolbar-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    font-family: "Inter", sans-serif;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
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
    color: #a78bfa;
  }

  .btn-text {
    font-size: 13px;
  }

  @media (max-width: 640px) {
    .btn-text {
      display: none;
    }
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
    max-width: 80px;
    overflow: hidden;
  }

  .native-table th:first-child,
  .native-table td:first-child {
    max-width: 250px;
  }

  .native-table th:nth-child(2),
  .native-table td:nth-child(2) {
    max-width: 60px;
    text-align: center;
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

  .native-table .val-cell {
    text-align: center;
  }

  .native-table .editable-cell {
    cursor: text;
    outline: none;
    transition:
      background 0.2s ease,
      box-shadow 0.2s ease;
    padding: 10px 12px !important;
    vertical-align: middle;
    min-width: 60px;
    max-width: 80px;
    text-align: center;
  }

  .editable-cell:hover {
    background: #f1f5f9;
  }

  .editable-cell:focus {
    background: #eff6ff;
    box-shadow: inset 0 0 0 2px #3b82f6;
    outline: none;
  }

  :global(.dark) .editable-cell:hover {
    background: #334155;
  }

  :global(.dark) .editable-cell:focus {
    background: #1e3a5f;
    box-shadow: inset 0 0 0 2px #60a5fa;
  }

  .native-table .grade-header {
    /* Existing styles for th */
    text-align: center;
  }

  .grade-header-button {
    background: none;
    border: none;
    color: inherit;
    font: inherit;
    cursor: pointer;
    padding: 0;
    margin: 0;
    display: block; /* Make the button fill the th */
    width: 100%;
    height: 100%;
    text-align: center; /* Center the text inside the button */
    transition: color 0.2s ease;
  }

  .grade-header-button:hover {
    color: rgba(255, 255, 255, 0.8); /* Example hover color, adjust as needed */
  }

  .grade-header-button:focus {
    outline: 2px solid #eff6ff; /* Example focus style */
    outline-offset: 2px;
    border-radius: 4px;
  }

  .low-grade {
    color: #ef4444 !important;
    font-weight: bold;
  }

  :global(.dark) .native-table .low-grade {
    color: #f87171 !important;
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
      gap: 12px;
    }
    .toolbar-left {
      min-width: 100%;
    }
    .search-container {
      max-width: 100%;
    }
    .toolbar-right {
      justify-content: center;
    }
    .toolbar-group {
      flex: 1;
      justify-content: center;
    }
  }
</style>
