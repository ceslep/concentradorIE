<script lang="ts">
    import { onMount, onDestroy, tick } from "svelte";
    import { writable } from "svelte/store";
    import {
        GET_NOTAS_ENDPOINT,
        GET_PERIODOS_NOTAS_ENDPOINT,
    } from "../../constants";
    import { payload, selectedAsignatura } from "./storeConcentrador";

    import {
        TabulatorFull as Tabulator,
        type ColumnDefinition,
    } from "tabulator-tables";

    // Props
    export let tableNotasId: string = "tableNotas";
    export let initialPeriodo: string | undefined = undefined;
    // layout prop removed as it is now hardcoded
    export let acceso: any = {};
    export let docenteId: string;

    // Reactive derivations
    $: docente = docenteId || $payload.Asignacion;
    $: nivel = $payload.nivel;
    $: numero = $payload.numero;
    $: asignatura = $selectedAsignatura;
    $: asignation = $payload.Asignacion;
    $: year = $payload.year;

    // Internal state
    let tableInstance: any;
    let datosTabla: any[] = [];
    let currentPeriodo: string = "";
    let mounted = false;

    // Reactive trigger
    $: if (mounted && docente && asignatura) {
        loadNotas(`#${tableNotasId}`, docente, asignatura);
    }

    // Modal refs (placeholders – you can bind them if using real modals)
    let modalNAPElement: HTMLElement;
    let modalInasistenciasElement: HTMLElement;
    let modalNAP: any;
    let modalInasistencias: any;

    // Editing state
    let colT: any;
    let rr: any;
    let rowIdx: number;
    let dataRR: any;
    let aspx: string = "";
    let poraspx: number | string = "";
    let faspx: string = "";
    let COLUMNA: string = "";
    let editingColumnIdx: string | null = null;
    let DatosEstux: any;
    let estudianteId: string = "";

    // Helpers
    const toDateInputValue = (date: Date) => {
        const local = new Date(date);
        local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    };

    const valid = function (
        value: any,
        parameters: { min: number; max: number },
    ) {
        const numValue = parseFloat(value);
        return (
            !isNaN(numValue) &&
            numValue >= parameters.min &&
            numValue <= parameters.max
        );
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

    // Nueva función para calcular Val de una fila (extraída de calcTotal)
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

    // Formateador común para notas y Val
    const gradeFormatter = (cell: any) => {
        const val = cell.getValue();
        if (val === " " || val === "" || val === null || val === undefined) {
            return "";
        }
        const num = parseFloat(val);
        return isNaN(num) ? val : num.toFixed(2);
    };

    // Column factory
    function createGradeColumn(title: string, field: string): ColumnDefinition {
        return {
            title: title,
            field: field,
            editor: "number",
            editorParams: { verticalNavigation: "table" },
            hozAlign: "right",
            validator: [{ type: valid, parameters: { min: 1, max: 5 } }],
            headerMenu: headerMenu,
            formatter: gradeFormatter,
            cellEdited: async (e: any) => {
                try {
                    const row = e.getRow();
                    dataRR = row.getData();
                    rowIdx = row.getPosition(true);

                    // Recalcular Val usando la función helper
                    dataRR.Val = calculateRowVal(dataRR);

                    // Actualizar fecha si es necesario (lógica original)
                    const fieldName = e.getField(); // ej: N1
                    const idx = fieldName.replace("N", "");
                    const fechaKey = `fecha${idx}`;
                    if (!dataRR[fechaKey])
                        dataRR[fechaKey] = toDateInputValue(new Date());

                    datosTabla[rowIdx] = dataRR;
                    row.update(dataRR); // Actualizar la fila visualmente
                } catch (error) {
                    // Error in cellEdited
                }
            },
        };
    }

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

    const Nasignatura = (
        nivel: string,
        numero: string,
        asignatura: string,
        periodo: string,
    ) => {
        // Nasignatura called
    };

    const loadNotas = async (
        targetTableId: string,
        currentDocente: string,
        currentAsignatura: string,
    ) => {
        if (!currentDocente) {
            return;
        }

        await tick(); // Wait for DOM update

        const element = document.querySelector(targetTableId);
        if (!element) {
            return;
        }

        const per = await getCurrentPeriodo();
        const Elperiodo = initialPeriodo || per;

        if (per !== currentPeriodo && acceso && !acceso.MAESTRO) {
            currentPeriodo = per;
            Nasignatura(nivel, numero, currentAsignatura, currentPeriodo);
        }

        if (tableInstance) {
            tableInstance.destroy();
        }

        tableInstance = new Tabulator(targetTableId, {
            height: "100%", // Use 100% of container
            renderVertical: "basic", // Disable virtualization to force render
            layout: "fitDataTable", // Revert to fitDataTable to allow horizontal scrolling
            placeholder: "No hay datos",
            responsiveLayout: false, // Disable responsive layout to prevent stacking
            columns: [
                {
                    title: "Notas del Periodo",
                    columns: [
                        {
                            title: "Nombres",
                            field: "Nombres",
                            widthGrow: 2,
                            headerFilter: true,
                            headerFilterPlaceholder: "Buscar Estudiante...",
                            cellClick: (_e, cell) =>
                                Inasistencias(cell.getRow().getData()),
                        },
                        {
                            title: "Val",
                            field: "Val",
                            hozAlign: "center",
                            maxWidth: 50,
                            formatter: gradeFormatter,
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
                },
            ],
            ajaxURL: GET_NOTAS_ENDPOINT,
            headerSort: false,
            ajaxResponse: (_url, _params, response: any[]) => {
                // Calcular Val para cada fila antes de renderizar
                const processed = response.map((item) => {
                    item.Val = calculateRowVal(item);
                    return item;
                });
                return processed.sort((a, b) =>
                    a.Nombres.localeCompare(b.Nombres),
                );
            },
            ajaxParams: {
                docente: currentDocente,
                nivel,
                numero,
                asignatura: currentAsignatura,
                periodo: Elperiodo,
                asignacion: asignation,
                year,
            },
            ajaxContentType: "json",
            ajaxConfig: {
                method: "POST",
                headers: { "Content-type": "application/json; charset=utf-8" },
            },
        });

        tableInstance.on("renderComplete", async () => {
            datosTabla = tableInstance.getData();
        });

        tableInstance.on("validationFailed", async (_cell: any, value: any) => {
            (window as any).Swal?.fire?.({
                icon: "error",
                title: "Valor inválido",
                html: `<h3 class="text-danger">${value}</h3> no está permitido (rango: 1–5)`,
            });
        });
    };

    const headerMenu = [
        {
            label: "Aspectos",
            action: function (_e: Event, column: any) {
                const field = column.getField();
                const idx = field.replace("N", "");
                editingColumnIdx = idx;

                const aspectoData = datosTabla.find((d) => d[`aspecto${idx}`]);
                const porcentajeData = datosTabla.find(
                    (d) => d[`porcentaje${idx}`],
                );
                const fechaData = datosTabla.find((d) => d[`fechaa${idx}`]);

                aspx = aspectoData ? aspectoData[`aspecto${idx}`] : "";
                poraspx =
                    porcentajeData &&
                    !isNaN(Number(porcentajeData[`porcentaje${idx}`]))
                        ? Number(porcentajeData[`porcentaje${idx}`])
                        : "";
                faspx =
                    fechaData?.[`fechaa${idx}`] || toDateInputValue(new Date());

                if (modalNAP?.show) modalNAP.show();
            },
        },
    ];

    const saveAspectos = () => {
        if (!editingColumnIdx) return;
        const idx = editingColumnIdx;
        datosTabla = datosTabla.map((d) => ({
            ...d,
            [`aspecto${idx}`]: aspx,
            [`porcentaje${idx}`]: poraspx,
            [`fechaa${idx}`]: faspx,
        }));
        tableInstance?.setData(datosTabla);
        (window as any).Swal?.fire?.(
            "Éxito",
            "Aspectos actualizados (simulado)",
            "success",
        );
        if (modalNAP?.hide) modalNAP.hide();
    };

    const Inasistencias = (data: any) => {
        DatosEstux = data;
        estudianteId = data?.estudiante || "";
        if (modalInasistencias?.show) modalInasistencias.show();
    };

    const saveInasistencia = async () => {
        if (!DatosEstux) return;
        if (modalInasistencias?.hide) modalInasistencias.hide();
    };

    onMount(async () => {
        mounted = true;
        currentPeriodo = await getCurrentPeriodo();
        // Initialize modals if needed (uncomment if using real Bootstrap)
        // modalNAP = new (window as any).bootstrap.Modal(modalNAPElement);
        // modalInasistencias = new (window as any).bootstrap.Modal(modalInasistenciasElement);
    });

    onDestroy(() => {
        mounted = false;
        if (tableInstance) tableInstance.destroy();
    });
</script>

<div class="grades-table-container">
    <div id={tableNotasId}></div>
</div>

<style>
    .grades-table-container {
        margin: 20px;
        height: 100%; /* Asegurar que ocupe espacio */
        min-height: 400px; /* Altura mínima para ver algo */
    }
    /* Estilos globales para Tabulator si es necesario, o asegurar que el tema se aplique */
    :global(.tabulator) {
        font-size: 14px;
    }
</style>
