<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import { writable } from 'svelte/store';
    import { GET_NOTAS_ENDPOINT, GET_PERIODOS_NOTAS_ENDPOINT } from '../../constants';
    import { payload, selectedAsignatura } from './storeConcentrador';

    import { TabulatorFull as Tabulator, type ColumnDefinition } from 'tabulator-tables';
    // import { Modal } from 'bootstrap';
    // import Swal from 'sweetalert2';

    // Placeholder for global variables used in loadnotas.js
    // These should ideally be passed as props or managed by Svelte stores
    export let tableNotasId: string = 'tableNotas';
    export let initialPeriodo: string | undefined = undefined;
    export let layout: "fitDataTable" | "fitData" | "fitColumns" | "fitDataFill" | "fitDataStretch" = 'fitDataTable'; // Reinstated layout prop
    export let acceso: any = {}; // Reinstated acceso prop
    export let docenteId: string; // New prop

    $: docente = docenteId || $payload.Asignacion; // Use docenteId if provided, otherwise fallback to payload.Asignacion
    $: nivel = $payload.nivel;
    $: numero = $payload.numero;
    $: asignatura = $selectedAsignatura;
    $: asignation = $payload.Asignacion;
    $: year = $payload.year;

    // Reactive statement to load notes when docente or asignatura changes
    $: if (docente && asignatura) {
        consolelog('Reactive loadNotas triggered by docente or asignatura change:', { docente, asignatura });
        loadNotas(`#${tableNotasId}`, docente, asignatura);
    }

    let tableInstance: any;
    let datosTabla: any[] = [];
    let datosTablai: any[] = []; // Used in original for some reason, keeping for now
    let currentPeriodo: string = '';

    // Modals
    let modalNAPElement: HTMLElement;
    let modalInasistenciasElement: HTMLElement;
    let modalNAP: any; // Bootstrap Modal instance
    let modalInasistencias: any; // Bootstrap Modal instance

    // Variables for headerMenu and cell editing
    let colT: any;
    let rr: any; // row
    let rowIdx: number; // Renamed from 'row' to avoid conflict
    let dataRR: any;
    let aspx: string = '';
    let poraspx: number | string = '';
    let faspx: string = '';
    let COLUMNA: string = ''; // For the header menu to identify the column
    let editingColumnIdx: string | null = null; // To store the index of the column being edited
    let DatosEstux: any; // For Inasistencias modal
    let estudianteId: string = ''; // Reactive variable for binding estudiante ID

    // Helper to format date to YYYY-MM-DD
    const toDateInputValue = (date: Date) => {
        const local = new Date(date);
        local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    };

    // Helper for original console.log replacement
    const consolelog = console.log;

    // Helper for validation (assuming 'valid' function from original)
    // This needs to be properly defined based on the actual 'valid' function's logic.
    // For now, a placeholder that checks min/max
    const valid = function (value: any, parameters: { min: number; max: number }) {
        const numValue = parseFloat(value);
        return !isNaN(numValue) && numValue >= parameters.min && numValue <= parameters.max;
    };

    // Helper function to create grade columns (N1-N12)
    function createGradeColumn(title: string, field: string): ColumnDefinition {
        return {
            title: title,
            field: field,
            editor: "number",
            editorParams: { verticalNavigation: 'table' },
            hozAlign: "right",
            validator: [{ type: valid, parameters: { min: 1, max: 5 } }],
            headerMenu: headerMenu,
            cellEdited: async (e: any) => {
                try {
                    colT = e;
                    dataRR = await e.getRow().getData();
                    rowIdx = await e.getRow().getPosition(true);

                    let Val = 0;
                    let n = 0;
                    let porcentajes = false;
                    let Saber35 = 0;
                    let Hacer35 = 0;
                    let Ser20 = 0;
                    let Autoe5 = 0;
                    let Coev5 = 0;
                    let arraycounts: any[] = [];

                    Object.entries(dataRR).forEach(([key, value]) => {
                        if (key.includes('N') && key !== 'Nombres') {
                            if (value !== ' ' && value !== null && value !== '') {
                                let idx = key.slice(key.lastIndexOf('N') + 1, key.length);
                                if (dataRR[`fecha${idx}`] == null) {
                                    dataRR[`fecha${idx}`] = toDateInputValue(new Date());
                                }
                                let porcentaje = 1;
                                if (dataRR[`porcentaje${idx}`] != null && dataRR[`porcentaje${idx}`] !== '') {
                                    porcentaje = parseFloat(dataRR[`porcentaje${idx}`]) / 100;
                                    porcentajes = true;
                                }
                                Val += parseFloat(value as string) * porcentaje;
                                if (porcentaje === 1) n++;

                                const numVal = parseFloat(value as string);
                                if (!isNaN(numVal)) {
                                    if (idx === '1' || idx === '2' || idx === '3') {
                                        Saber35 += numVal;
                                    } else if (idx === '4' || idx === '5' || idx === '6') {
                                        Hacer35 += numVal;
                                    } else if (idx === '7' || idx === '8' || idx === '9') {
                                        Ser20 += numVal;
                                    } else if (idx === '10') {
                                        Autoe5 += numVal;
                                    } else if (idx === '11') {
                                        Coev5 += numVal;
                                    }
                                }
                            }
                            arraycounts = [...arraycounts, value];
                        }
                    });

                    let contadores = countNonEmptyGroups(arraycounts);

                    const saberAvg = contadores[0] !== 0 ? Saber35 / contadores[0] : 0;
                    const hacerAvg = contadores[1] !== 0 ? Hacer35 / contadores[1] : 0;
                    const serAvg = contadores[2] !== 0 ? Ser20 / contadores[2] : 0;
                    const autoeVal = Autoe5;
                    const coevVal = Coev5;

                    dataRR.Val = (
                        saberAvg * 0.35 +
                        hacerAvg * 0.35 +
                        serAvg * 0.20 +
                        autoeVal * 0.05 +
                        coevVal * 0.05
                    ).toFixed(2);
                    
                    const rowElement = e.getRow().getElement();
                    if (rowElement && rowElement.children[2]) {
                        rowElement.children[2].innerText = dataRR.Val;
                    }
                    datosTabla[rowIdx] = dataRR;
                    e.getRow().reformat();
                } catch (error) {
                    consolelog('Error in cellEdited:', error);
                }
            }
        };
    }


    const countNonEmptyGroups = (array: any[]) => {
        const groups = [
            array.slice(0, 3),
            array.slice(3, 6),
            array.slice(6, 9),
            array.slice(9, 10),
            array.slice(10, 11),
        ];

        const isEmpty = (val: any) => !val || String(val).trim() === '';

        return groups.map((group) => group.filter((val) => !isEmpty(val)).length);
    };

    const calcTotal = async (cant: number) => {
        consolelog('calcTotal called for', cant, 'rows');
        for (let i = 0; i < cant; i++) {
            try {
                // Ensure tableInstance and its rows are available
                if (!tableInstance || !tableInstance.rowManager || !tableInstance.rowManager.rows[i]) {
                    consolelog('Table instance or row not ready for index', i);
                    continue;
                }

                const rowData = await tableInstance.rowManager.rows[i].getData();

                let Val = 0;
                let n = 0;
                let porcentajes = false;
                let Saber35 = 0;
                let Hacer35 = 0;
                let Ser20 = 0;
                let Autoe5 = 0;
                let Coev5 = 0;
                let arraycounts: any[] = [];

                Object.entries(rowData).forEach(([key, value]) => {
                    if (key.includes('N') && key !== 'Nombres') {
                        if (value !== ' ' && value !== null && value !== '') {
                            let idx = key.slice(key.lastIndexOf('N') + 1, key.length);
                            if (rowData[`fecha${idx}`] == null) {
                                rowData[`fecha${idx}`] = toDateInputValue(new Date());
                            }
                            let porcentaje = 1;
                            if (rowData[`porcentaje${idx}`] != null && rowData[`porcentaje${idx}`] !== '') {
                                porcentaje = parseFloat(rowData[`porcentaje${idx}`]) / 100;
                                porcentajes = true;
                            }
                            Val += parseFloat(value as string) * porcentaje;
                            if (porcentaje === 1) n++;

                            const numVal = parseFloat(value as string);
                            if (!isNaN(numVal)) {
                                if (idx === '1' || idx === '2' || idx === '3') {
                                    Saber35 += numVal;
                                } else if (idx === '4' || idx === '5' || idx === '6') {
                                    Hacer35 += numVal;
                                } else if (idx === '7' || idx === '8' || idx === '9') {
                                    Ser20 += numVal;
                                } else if (idx === '10') {
                                    Autoe5 += numVal;
                                } else if (idx === '11') {
                                    Coev5 += numVal;
                                }
                            }
                        }
                        arraycounts = [...arraycounts, value];
                    }
                });

                let contadores = countNonEmptyGroups(arraycounts);

                let finalVal = 0;
                if (n > 0 && !porcentajes) {
                    finalVal = Val / n;
                } else if (porcentajes) {
                    finalVal = Val;
                }

                // Weighted calculation
                const saberAvg = contadores[0] !== 0 ? Saber35 / contadores[0] : 0;
                const hacerAvg = contadores[1] !== 0 ? Hacer35 / contadores[1] : 0;
                const serAvg = contadores[2] !== 0 ? Ser20 / contadores[2] : 0;

                // Autoe5 and Coev5 are single values, no average needed for them directly from groups
                const autoeVal = Autoe5;
                const coevVal = Coev5;

                finalVal = (
                    saberAvg * 0.35 +
                    hacerAvg * 0.35 +
                    serAvg * 0.20 +
                    autoeVal * 0.05 +
                    coevVal * 0.05
                );
                
                rowData.Val = finalVal.toFixed(2);

                // Update the visible cell and the internal datosTabla
                const rowElement = tableInstance.rowManager.rows[i].getElement();
                if (rowElement && rowElement.children[2]) {
                    rowElement.children[2].innerText = rowData.Val;
                }
                datosTabla[i] = rowData;
                tableInstance.updateRow(tableInstance.rowManager.rows[i], rowData); // Update Tabulator's internal data
            } catch (error) {
                consolelog('Error in calcTotal for row', i, error);
            }
        }
    };


    const getCurrentPeriodo = async () => {
        try {
            const response = await fetch(GET_PERIODOS_NOTAS_ENDPOINT);
            const periodos = await response.json();
            const periodoActual = periodos.filter((periodo: any) => periodo.selected === 'selected').map((periodo: any) => periodo.nombre)[0];
            return periodoActual;
        } catch (error) {
            consolelog('Error fetching current period:', error);
            return 'DefaultPeriod'; // Fallback
        }
    };

    const Nasignatura = async (nivel: string, numero: string, asignatura: string, periodo: string) => {
        // This function's original purpose was to update some global state based on assignment
        // and re-render something. For now, it will just log or handle state updates.
        consolelog(`Nasignatura called with: ${nivel}, ${numero}, ${asignatura}, ${periodo}`);
        // This function's actual logic from outside loadnotas.js is missing,
        // so we'll just acknowledge its call.
    };

    const loadNotas = async (targetTableId: string, currentDocente: string, currentAsignatura: string) => {
        consolelog('loadNotas called with:', { targetTableId, currentDocente, currentAsignatura, initialPeriodo });
        const targetElement = document.querySelector(targetTableId);
        consolelog('Target element existence:', targetElement ? 'Found' : 'Not Found', targetElement);

        if (currentDocente === '') {
            consolelog('docente is empty, returning early from loadNotas');
            return;
        }

        let per = await getCurrentPeriodo();
        let Elperiodo = initialPeriodo || per; // Use prop if provided, else fetch current
        consolelog('Elperiodo:', Elperiodo);

        if (per !== currentPeriodo && acceso && !acceso.MAESTRO) { // Assuming acceso.MAESTRO exists
            currentPeriodo = per;
            // let a = new Object(); a = { ...docente, nivel, numero, asignatura }; // Original had a bug, `docente` is a string
            // Corrected:
            Nasignatura(nivel, numero, currentAsignatura, currentPeriodo);
        }

        // UI feedback
        // document.querySelector('.spnasignatura')?.classList.remove('d-none');
        // document.querySelector('.descudo')?.classList.add('d-none');
        // document.querySelector('.float')?.classList.add('d-none');
        // document.getElementById(tableNotasId)?.classList.remove('d-none');

        // Tabulator initialization
        if (tableInstance) {
            tableInstance.destroy(); // Destroy previous instance if it exists
        }

        tableInstance = new Tabulator(targetTableId, {
            height: '1220px',
            layout: layout,
            placeholder: 'No hay datos',
            responsiveLayout: true,
            columns: [
                {
                    title: "Nombres, Val, N1..N12", // Main header
                    columns: [
                        { title: "Nombres", field: "Nombres", widthGrow: 2, headerFilter: true, headerFilterPlaceholder: 'Buscar Estudiante...', cellClick: function (e: Event, cell: any) { Inasistencias(cell.getRow().getData()); } },
                        { title: "Val", field: "Val", hozAlign: "center", maxWidth: 40, resizable: false },
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
                    ]
                }
            ],
            ajaxURL: GET_NOTAS_ENDPOINT,
            headerSort: false,
            ajaxResponse: function (url: string, params: any, response: any) {
                response = response.sort((a: any, b: any) => {
                    if (a.Nombres > b.Nombres) return 1;
                    if (a.Nombres < b.Nombres) return -1;
                    return 0;
                });
                return response;
            },
            ajaxParams: {
                docente: currentDocente,
                nivel,
                numero,
                asignatura: currentAsignatura,
                periodo: Elperiodo,
                asignacion: asignation,
                year
            },
            ajaxContentType: 'json',
            ajaxConfig: {
                method: 'POST',
                headers: {
                    'Content-type': 'application/json; charset=utf-8',
                },
            },
            rowFormatter: function (row: any) {
                const element = row.getElement();
                if (!element) return;

                // Apply background colors to groups of columns
                for (let i = 3; i <= 5; i++) { // Saber
                    if (element.children[i]) {
                        element.children[i].style.backgroundColor = 'rgb(231,255,231)';
                        element.children[i].style.border = '1px solid gray';
                    }
                }
                for (let i = 6; i <= 8; i++) { // Hacer
                    if (element.children[i]) {
                        element.children[i].style.backgroundColor = 'rgb(255,238,225)';
                        element.children[i].style.border = '1px solid gray';
                    }
                }
                for (let i = 9; i <= 11; i++) { // Ser
                    if (element.children[i]) {
                        element.children[i].style.backgroundColor = 'rgb(240,245,255)';
                        element.children[i].style.border = '1px solid gray';
                    }
                }
                if (element.children[12]) { // Autoevaluación
                    element.children[12].style.backgroundColor = 'rgb(255,213,214)';
                    element.children[12].style.border = '1px solid gray';
                }
                if (element.children[13]) { // Coevaluación
                    element.children[13].style.backgroundColor = 'rgb(255,250,214)';
                    element.children[13].style.border = '1px solid gray';
                }

                const dataRR = row.getData();
                const valCellElement = element.children[2]; // 'Val' column

                if (valCellElement) {
                    valCellElement.style.backgroundColor = 'gray';
                    valCellElement.style.border = '1px solid black';

                    if (dataRR && dataRR.Val !== undefined && dataRR.Val !== null) {
                        const val = parseFloat(dataRR.Val);
                        if (val >= 3) {
                            valCellElement.style.backgroundColor = 'green';
                            valCellElement.style.color = 'white';
                        } else {
                            valCellElement.style.backgroundColor = 'red';
                            valCellElement.style.color = 'white';
                        }
                    }
                }

                // Apply red color to individual grades if less than 3
                for (let i = 3; i <= 15; i++) {
                    const cell = row.getCells()[i];
                    if (cell) {
                        const cellValue = parseFloat(cell.getValue());
                        if (!isNaN(cellValue) && cellValue < 3) {
                            cell.getElement().style.color = 'red';
                        } else {
                             cell.getElement().style.color = ''; // Reset color if not red
                        }
                    }
                }
            },
        });

        tableInstance.on('renderComplete', async function () {
            // Column hiding logic removed as columns are now explicitly defined
            // and only the requested columns are included.

            datosTabla = tableInstance.getData();
            datosTablai = [...datosTabla];

            // UI feedback
            // document.querySelector('.spnasignatura')?.classList.add('d-none');
            if (acceso.datos && acceso.datos.nombres && acceso.datos.nombres.includes('COORDI')) {
                // document.querySelector('.savingNotas')?.classList.add('d-none');
                // document.querySelector('.float')?.classList.add('d-none');
            } else {
                // document.querySelector('.savingNotas')?.classList.remove('d-none');
                // document.querySelector('.float')?.classList.remove('d-none');
            }
            await calcTotal(datosTabla.length); // Ensure calcTotal is awaited
            // This function should be replaced by rowFormatter logic or specific cell formatters.
            // For now, removing direct call to avoid duplicate or conflicting DOM ops.

           /*  const rmen = await fetch('getMensaje.php');
            const mensaje = await rmen.json();
            if (mensaje.mensaje !== '') {
                (window as any).Swal.fire({
                    icon: 'info',
                    title: mensaje.mensaje,
                });
            } */
            // ik = 0; // Global variable
            // reiniciarInterval(); // Function not defined
            // startTimer(); // Function not defined
            // document.querySelector('.infoporcentajes')?.classList.remove('d-none');
        });

        tableInstance.on('validationFailed', async function (cell: any, value: any, validators: any) {
            try {
                (window as any).Swal.fire({
                    icon: 'error',
                    title: `Ha escrito un valor erróneo`,
                    html: ` <h3 class="text-danger">${value}</h3> no está permitido`,
                });
            } catch (error) {
                consolelog('Error in validationFailed:', error);
            }
        });
        // if (docente == '') document.querySelector('.spnasignatura')?.classList.add('d-none');
        return true;
    };

    const saveAspectos = () => {
        if (!editingColumnIdx) return;

        const idx = editingColumnIdx; // Use the stored index

        const updatedDatosTabla = datosTabla.map((dato) => {
            return {
                ...dato,
                [`aspecto${idx}`]: aspx,
                [`porcentaje${idx}`]: poraspx,
                [`fechaa${idx}`]: faspx,
            };
        });

        datosTabla = updatedDatosTabla; // Update Svelte's reactive state
        tableInstance.setData(datosTabla); // Update Tabulator's data
        // In a real application, you would send this data to a backend endpoint:
        // try {
        //     const response = await fetch('saveAspectos.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.stringify({
        //             aspecto: aspx,
        //             porcentaje: poraspx,
        //             fecha: faspx,
        //             columnIdx: idx,
        //             // other relevant data from datosTabla if saving per-student aspects
        //         }),
        //     });
        //     const result = await response.json();
        //     if (result.success) {
        //         (window as any).Swal.fire('Éxito', 'Aspectos guardados correctamente', 'success');
        //     } else {
        //         (window as any).Swal.fire('Error', 'No se pudieron guardar los aspectos', 'error');
        //     }
        // } catch (error) {
        //     consolelog('Error saving aspectos:', error);
        //     (window as any).Swal.fire('Error', 'Ocurrió un error al guardar los aspectos', 'error');
        // }
        (window as any).Swal.fire('Éxito', 'Aspectos guardados correctamente (simulado)', 'success');
        modalNAP.hide(); // Close the modal
    };


    // Header Menu logic (adapted from original)
    const headerMenu = [
        {
            label: 'Aspectos',
            action: function (e: Event, column: any) {
                // document.querySelector('.float')?.classList.add('d-none');
                if (!modalNAP) {
                 //   modalNAP = new (window as any).bootstrap.Modal(modalNAPElement);
                }
             //   modalNAPElement.addEventListener('hidden.bs.modal', () => {
                    // document.querySelector('.float')?.classList.remove('d-none');
               // });

                // Get column data
                colT = column;
                let key = column.getField();
                let idx = key.slice(key.lastIndexOf('N') + 1, key.length);
                editingColumnIdx = idx; // Store the index of the column being edited

                // Filter datosTabla to get relevant aspect, percentage, and date
                // Original logic was `filter` then `[0]`, which would only get the aspect from the first row that has it.
                // For header-level aspects, it might be better to find a common aspect or allow setting one for all.
                // Assuming for now that the aspect/percentage/date is global for the column.
                let aspectoData = datosTabla.find((data) => data[`aspecto${idx}`] != '' && data[`aspecto${idx}`] != null);
                let porcentajeData = datosTabla.find((data) => data[`porcentaje${idx}`] !== '');
                let fechasData = datosTabla.find((data) => data[`fechaa${idx}`] !== '');

                aspx = aspectoData ? (aspectoData[`aspecto${idx}`] || '') : '';

                let porcVal = porcentajeData ? (porcentajeData[`porcentaje${idx}`] || '') : '';
                poraspx = !isNaN(parseInt(porcVal)) ? parseInt(porcVal) : '';

                let fechasVal = fechasData ? (fechasData[`fechaa${idx}`] || '') : '';
                faspx = fechasVal || toDateInputValue(new Date());

                // Update modal inputs - Svelte's bind:value will handle this automatically now.

                modalNAP.show();
            },
        },
    ];

    const saveInasistencia = async () => {
        if (!DatosEstux) return;

        const dataToSave = {
            estudiante: DatosEstux.estudiante,
            nombreEstudiante: DatosEstux.Nombres,
            periodo: currentPeriodo,
            docente: docente,
            asignatura: asignatura,
            nivel: nivel,
            numero: numero,
            asignacion: asignation,
            fechaInasistencia: faspx, // Using faspx for the absence date, assuming the modal's date input also binds to this.
            // Additional fields from the inasistencia modal (e.g., justification, type) would go here
        };

        consolelog('Saving Inasistencia:', dataToSave);
        // In a real application, you would send this data to a backend endpoint:
        try {
            const response = await fetch('saveInasistencias.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataToSave),
            });
            const result = await response.json();
            if (result.success) {
                (window as any).Swal.fire('Éxito', 'Inasistencia guardada correctamente', 'success');
                modalInasistencias.hide();
            } else {
                (window as any).Swal.fire('Error', 'No se pudo guardar la inasistencia', 'error');
            }
        } catch (error) {
            consolelog('Error saving inasistencia:', error);
            (window as any).Swal.fire('Error', 'Ocurrió un error al guardar la inasistencia', 'error');
        }
        modalInasistencias.hide();
    };

    const Inasistencias = async (data: any) => {
        // document.querySelector('.float')?.classList.add('d-none');
     /*    if (!modalInasistencias) {
            modalInasistencias = new (window as any).bootstrap.Modal(modalInasistenciasElement);
        } */
        consolelog('Inasistencias data:', data);
        DatosEstux = data;
        estudianteId = DatosEstux?.estudiante || ''; // Update the reactive variable
        let { estudiante, Nombres } = data;

        // Update modal content
        const fechaInasistenciaInput = document.querySelector('#fechainasistencia') as HTMLInputElement;
        const nestudianteSpan = document.querySelector('.nestudiantei') as HTMLSpanElement;
        const estudianteInput = document.getElementById('estudiantei') as HTMLInputElement;
        const periodoInput = document.getElementById('periodoi') as HTMLInputElement;
        const docenteInput = document.getElementById('docentei') as HTMLInputElement;
        const materiaInput = document.getElementById('materiai') as HTMLInputElement;
        const nivelInput = document.getElementById('niveli') as HTMLInputElement;
        const numeroInput = document.getElementById('numeroi') as HTMLInputElement;
        const asignacionInput = document.getElementById('asignacioni') as HTMLInputElement;

        if (fechaInasistenciaInput) fechaInasistenciaInput.value = toDateInputValue(new Date());
        if (nestudianteSpan) nestudianteSpan.innerText = Nombres;
        if (estudianteInput) estudianteInput.value = estudiante;
        if (periodoInput) periodoInput.value = (document.getElementById('periodonotas') as HTMLInputElement)?.value || currentPeriodo;
        if (docenteInput) docenteInput.value = docente ?? ''; // Assuming `docente` prop is `elDocente`
        if (materiaInput) materiaInput.value = asignatura ?? ''; // Assuming `asignatura` prop is `Asignatura`
        if (nivelInput) nivelInput.value = nivel; // Assuming `nivel` prop is `Nivel`
        if (numeroInput) numeroInput.value = numero; // Assuming `numeroi` prop is `Numero`
        if (asignacionInput) asignacionInput.value = asignation; // Assuming `asignacioni` prop is `Asignacion`

        // var firstTabEl = document.querySelector(".inastabs li:first-child a"); // Original global
        // var firstTab = new (window as any).bootstrap.Tab(firstTabEl); // Original global
        // firstTab.show();

        modalInasistencias.show();

       // modalInasistenciasElement.addEventListener('hidden.bs.modal', function (event: Event) {
            // document.querySelector('.float')?.classList.remove('d-none');
    //    });
    };

    onMount(async () => {
        // Initialize Bootstrap Modals
     //   modalNAP = new (window as any).bootstrap.Modal(modalNAPElement);
     //   modalInasistencias = new (window as any).bootstrap.Modal(modalInasistenciasElement);

        currentPeriodo = await getCurrentPeriodo(); // Set initial currentPeriodo
    });

    onDestroy(() => {
        if (tableInstance) {
            tableInstance.destroy();
        }
        // Remove event listeners for modals if any were added outside Svelte's lifecycle
       
    });
</script>

<div class="grades-table-container">
    <div id={tableNotasId}></div>

    <!-- Modal for Aspectos (modalNAP) -->
   
</div>

<style>
    /* Add any specific styles for the component here */
    .grades-table-container {
        margin: 20px;
    }
</style>