<script lang="ts">
    import { onMount, onDestroy, tick } from "svelte";
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

    // Props
    export let tableNotasId: string = "gradesTable2";
    export let docenteId: string;
    export let initialPeriodo: string | undefined = undefined;
    export let acceso: any = {};

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

    // Trigger load when dependencies change
    $: if (mounted && docente && asignatura) {
        console.log("GradesTable2: Triggering load", { docente, asignatura });
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

        return isNaN(num) ? val : num.toFixed(2);
    };

    const valid = (value: any, parameters: { min: number; max: number }) => {
        const numValue = parseFloat(value);
        return (
            !isNaN(numValue) &&
            numValue >= parameters.min &&
            numValue <= parameters.max
        );
    };

    // --- Column Definitions ---

    function createGradeColumn(title: string, field: string): ColumnDefinition {
        return {
            title: title,
            field: field,
            editor: "number",
            editorParams: {
                verticalNavigation: "table",
                min: 1,
                max: 5,
                step: 0.1,
            },
            hozAlign: "right",
            validator: [{ type: valid, parameters: { min: 1, max: 5 } }],
            formatter: gradeFormatter,
            cellEdited: (e: any) => {
                const row = e.getRow();
                const data = row.getData();

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
            console.error("Error fetching periodo:", error);
            return "DefaultPeriod";
        }
    };

    const loadTable = async () => {
        if (!docente) return;

        await tick();
        const element = document.getElementById(tableNotasId);
        if (!element) {
            console.error(`GradesTable2: Element #${tableNotasId} not found`);
            return;
        }

        const per = await getCurrentPeriodo();
        const Elperiodo = initialPeriodo || per;

        if (tableInstance) tableInstance.destroy();

        console.log("GradesTable2: Initializing Tabulator...", {
            docente,
            asignatura,
            Elperiodo,
        });

        tableInstance = new Tabulator(element, {
            height: "100%",
            layout: "fitDataTable", // Allow horizontal scroll
            renderVertical: "basic", // Disable virtualization for modals
            placeholder: "No hay datos",
            reactiveData: true, // Enable reactive data
            ajaxContentType: "json", // Force JSON payload
            responsiveLayout: false, // Disable responsive layout
            columnDefaults: {
                headerSort: false, // Disable header sorting for all columns
                headerHozAlign: "center", // Center header names
            },
            columns: [
                {
                    title: "Nombres",
                    field: "Nombres",
                    width: 300,
                    frozen: true, // Freeze name column
                    headerFilter: "input",
                    headerFilterPlaceholder: "Buscar...",
                },
                {
                    title: "Val",
                    field: "Val",
                    hozAlign: "center",
                    width: 60,
                    formatter: gradeFormatter,
                    frozen: true, // Freeze Val column
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
                console.log("GradesTable2: Data received", response?.length);
                // Pre-calculate Val
                return response
                    .map((item) => {
                        item.Val = calculateRowVal(item);
                        return item;
                    })
                    .sort((a, b) => a.Nombres.localeCompare(b.Nombres));
            },
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

<div class="grades-table-wrapper">
    <div id={tableNotasId}></div>
</div>

<style>
    .grades-table-wrapper {
        height: 100%;
        min-height: 500px;
        width: 100%;
        overflow: hidden; /* Let Tabulator handle scroll */
    }
    :global(.tabulator) {
        font-size: 14px;
    }
    :global(.tabulator-row .tabulator-cell) {
        padding: 4px;
    }
</style>
