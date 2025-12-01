const loadNotas = async (docente, nivel, numero, asignatura, asignation = Asignacion, tableNotas = "#tableNotas",
    year = YEAR, layout = "fitDataTable") => {
    document.getElementById("periodoPuestos").value = "";
    if (docente === "") return;
    let per = await getCurrentPeriodo();
    let Elperiodo;
    if (per !== PERIODO && !MAESTRO) {
        PERIODO = per;
        let a = new Object();
        a = { ...docente, nivel, numero, asignatura };
        document.getElementById("periodonotas").value = PERIODO;
        Nasignatura(nivel, numero, asignatura, PERIODO);
    }
    if (tableNotas == "#tableNotasPlanilla") {
        Elperiodo = document.getElementById("periodosNotass").value;

    } else if (tableNotas == "#tableNotas") {
        Elperiodo = document.getElementById("periodonotas").value;
    }
    console.log(PERIODO);
    document.querySelector(".spnasignatura").classList.remove("d-none");
    document.querySelector(".descudo").classList.add("d-none");
    document.querySelector(".float").classList.add("d-none");
    document.getElementById("tableNotas").classList.remove("d-none");
    table = await new Tabulator(tableNotas, {
        height: "1220px",
        layout: layout,
        placeholder: "No hay datos",
        autoColumns: true,
        responsiveLayout: "true",
        ajaxURL: "getNotas.php",
        headerSort: false,
        ajaxResponse: function (url, params, response) {
            //url - the URL of the request
            //params - the parameters passed with the request
            //response - the JSON object returned in the body of the response.
            consolelog(response);
            response = response.sort((a, b) => {
                if (a.Nombres > b.Nombres) return 1;
                if (a.Nombres < b.Nombres) return -1;
                return 0;
            });
            return response; //return the response data to tabulator
        },
        ajaxParams: {
            docente: docente,
            nivel: nivel,
            numero: numero,
            asignatura: asignatura,
            periodo: Elperiodo,
            asignacion: asignation,
            year
        },
        ajaxContentType: "json",
        ajaxConfig: {
            method: "POST",
            headers: {
                "Content-type": "application/json; charset=utf-8", //set specific content type
            },
        },
        rowFormatter: function (row) {
            // consolelog(dataRR);

            row._row.cells[2].element.style.backgroundColor = "gray";
            row._row.cells[2].element.style.border = "1px solid black";
            for (let i = 3; i <= 5; i++) {

                row._row.cells[i].element.style.backgroundColor = "rgb(231,255,231)";
                row._row.cells[i].element.style.border = "1px solid gray";
            }
            for (let i = 6; i <= 8; i++) {

                row._row.cells[i].element.style.backgroundColor = "rgb(255,238,225)";
                row._row.cells[i].element.style.border = "1px solid gray";
            }
            for (let i = 9; i <= 11; i++) {

                row._row.cells[i].element.style.backgroundColor = "rgb(240,245,255)";
                row._row.cells[i].element.style.border = "1px solid gray";
            }
            for (let i = 12; i <= 12; i++) {

                row._row.cells[i].element.style.backgroundColor = "rgb(255,213,214)";
                row._row.cells[i].element.style.border = "1px solid gray";
            }
            for (let i = 13; i <= 13; i++) {

                row._row.cells[i].element.style.backgroundColor = "rgb(255,250,214)";
                row._row.cells[i].element.style.border = "1px solid gray";
            }
            if (dataRR != undefined) {
                // consolelog(dataRR);
                if (parseFloat(dataRR.Val) >= 3) {
                    //row._row.cells[2].element.style = "color:white;text-align:center;background:green;border:1px solid black;width:40px;";
                    row._row.cells[2].element.style.backgroundColor = "green";
                    row._row.cells[2].element.style.color = "white";
                    for (let i = 3; i <= 15; i++) {
                        if (parseFloat(row._row.cells[i].getValue()) < 3)
                            row._row.cells[i].element.style.color = "red";
                    }


                } else {
                    row._row.cells[2].element.style =
                        "color:white;text-align:center;background:red;border:1px solid black;width:40px;";
                    for (let i = 3; i <= 15; i++) {
                        if (parseFloat(row._row.cells[i].getValue()) < 3)
                            row._row.cells[i].element.style.color = "red";
                    }
                }
            }
        },
        renderComplete: async function () {
            try {
                for (let i = 1; i <= 12; i++) {
                    table.hideColumn("aspecto" + i);
                    table.hideColumn("porcentaje" + i);
                    table.hideColumn("fechaa" + i);
                    table.hideColumn("fecha" + i);
                }

                table.hideColumn("estudiante");
            } catch (error) { }
            datosTabla = table.getData();
            datosTablai = [...datosTabla];

            document.querySelector(".spnasignatura").classList.add("d-none");
            consolelog(acceso);
            if (acceso.datos.nombres.includes("COORDI")) {
                document.querySelector(".savingNotas").classList.add("d-none");
                document.querySelector(".float").classList.add("d-none");
            } else {
                document.querySelector(".savingNotas").classList.remove("d-none");
                document.querySelector(".float").classList.remove("d-none");
            }
            drawAllCells(datosTabla.length);
            const rmen = await fetch("getMensaje.php");
            const mensaje = await rmen.json();
            if (mensaje.mensaje !== "") {
                /* Swal.fire({
                    icon: "info",
                    title: mensaje.mensaje,
                }); */
            }
            ik = 0;
            reiniciarInterval();
            startTimer();
            document.querySelector(".infoporcentajes").classList.remove("d-none");
        },
        validationFailed: async function (cell, value, validators) {
            try {
                await Swal.fire({
                    icon: "error",
                    title: `Ha escrito un valor erróneo`,
                    html: ` <h3 class="text-danger">${value}</h3> no está permitido`,
                });
            } catch (error) { }
        },
        autoColumnsDefinitions: function (definitions) {
            definitions.forEach((column, index) => {
                //column.frozen=true;
                column.responsive = 0;
                column.resizable = false;
                if (index === 1) {
                    column.headerFilter = true;
                    column.headerFilterPlaceholder = "Buscar Estudiante...";
                    column.cellClick = async function (e, cell) {
                        Inasistencias(cell.getRow().getData());
                    };
                } else if (index === 2) {
                    column.hozAlign = "center";
                    column.maxWidth = 40;
                    // column.formatter="color";
                } else if (index > 2 && index < 15) {
                    column.cellClick = async function (e, cell) {
                        colT = cell;
                        rr = await cell.getRow();
                        // cc = cell.getPosition().column;
                        row = await rr.getPosition(true);

                        //  consolelog(cell.getValue());
                    };
                    column.cellEdited = async (e) => {
                        try {
                            consolelog(e);

                            colT = e;
                            //dataRR = rr.getData();
                            dataRR = await e.getRow().getData();
                            //row = await rr.getPosition(true);
                            row = await e.getRow().getPosition(true);
                            //   consolelog(dataRR);
                            var Val = 0;
                            var n = 0;
                            let porcentajes = false;
                            var Saber35 = 0;
                            var Hacer35 = 0;
                            var Ser20 = 0;
                            var Autoe5 = 0;
                            var Coev5 = 0;
                            let arraycounts = [];
                            Object.entries(dataRR).forEach((entry) => {
                                const [key, value] = entry;

                                if (key.includes("N") && key != "Nombres") {
                                    if (value != " " && value != null && value != "") {
                                        let idx = key.slice(key.lastIndexOf("N") + 1, key.length);
                                        if (dataRR[`fecha${idx}`] == null)
                                            dataRR[`fecha${idx}`] = new Date().toDateInputValue();
                                        let porcentaje = 1;
                                        if (
                                            dataRR[`porcentaje${idx}`] != null &&
                                            dataRR[`porcentaje${idx}`] != ""
                                        ) {
                                            porcentaje = parseFloat(dataRR[`porcentaje${idx}`]) / 100;
                                            porcentajes = true;
                                        }
                                        Val += parseFloat(value) * porcentaje;
                                        if (porcentaje === 1) n++;
                                        if (idx == 1 || idx == 2 || idx == 3) {
                                            console.log(value);
                                            Saber35 += parseFloat(value);
                                        } else
                                            if (idx == 4 || idx == 5 || idx == 6) {
                                                console.log(value);
                                                Hacer35 += parseFloat(value);
                                            } else if (idx == 7 || idx == 8 || idx == 9) {
                                                console.log(value);
                                                Ser20 += parseFloat(value);
                                            } else if (idx == 10) {
                                                console.log(value);
                                                Autoe5 += parseFloat(value);
                                            } else if (idx == 11) {
                                                console.log(value);
                                                Coev5 += parseFloat(value);
                                            }
                                    }
                                    arraycounts = [...arraycounts, value];
                                }
                            });
                            let contadores = countNonEmptyGroups(arraycounts);
                            console.log({ contadores });
                            console.log(Saber35);
                            console.log(Hacer35);
                            console.log(Ser20);
                            //   consolelog(n);
                            //   consolelog((Val / (n)).toFixed(2));
                            consolelog(n, Val);
                            if (n > 0 && !porcentajes) dataRR.Val = (Val / n).toFixed(2);
                            else if (porcentajes) dataRR.Val = Val.toFixed(2);
                            else dataRR.Val = 0;
                            dataRR.Val = (parseFloat(Saber35 / (contadores[0] != 0 ? contadores[0] : 1)) * 0.35 + parseFloat(Hacer35 / (contadores[1] != 0 ? contadores[1] : 1)) * 0.35 + parseFloat(Ser20 / (contadores[2] != 0 ? contadores[2] : 1)) * 0.20 + parseFloat(Autoe5) * 0.05 + parseFloat(Coev5) * 0.05).toFixed(2);
                            console.log(dataRR.Val);
                            var rowElement = e.getRow().getElement();
                            rowElement.children[2].innerText = dataRR.Val;
                            datosTabla[row] = dataRR;
                            e.getRow().reformat();
                        } catch (error) {
                            consolelog(error);
                        }
                    };
                    column.headerMenu = headerMenu;
                    column.editor = "number";
                    column.editorParams = {
                        verticalNavigation: "table",
                    };
                    column.hozAlign = "right";
                    column.validator = [
                        {
                            type: valid,
                            parameters: {
                                min: 1,
                                max: 5,
                            },
                        },
                    ];
                }
            });

            return definitions;
        },
    });

    // table.modules.localize.lang.ajax.loading="Cargando";
    if (docente == "")
        document.querySelector(".spnasignatura").classList.add("d-none");
    return true;
};