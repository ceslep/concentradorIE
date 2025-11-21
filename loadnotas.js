var headerMenu = [
    {
        label: "Aspectos",
        action: function (e, column) {
            document.querySelector(".float").classList.add("d-none");
            let modalNAPelement = document.getElementById("modalNAP");
            if (!modalNAP) modalNAP = new bootstrap.Modal(modalNAPelement);
            modalNAPelement.addEventListener("hidden.bs.modal", () => {
                document.querySelector(".float").classList.remove("d-none");
            });
            modalNAPelement.querySelector("input[name='fecha']").value =
                new Date().toDateInputValue();
            colT = column;
            let key = column.getField();
            let idx = key.slice(key.lastIndexOf("N") + 1, key.length);
            console.log(column._column.definition.title);
            let colA = column._column.definition.title.slice(1);
            console.log(colA);
            let aspecto = datosTabla.filter((data) => {
                return data[`aspecto${idx}`] != "" && data[`aspecto${idx}`] != null;
            })[0];
            console.log(aspecto);
            let asp;
            COLUMNA = idx;
            if (aspecto != undefined)
                asp = aspecto[`aspecto${idx}`] != null ? aspecto[`aspecto${idx}`] : "";
            /* let porcentaje = datosTabla.filter(data => {
                       return data[`porcentaje${idx}`] !== "";
                   });*/
            aspx = asp;
            let porcentaje = datosTabla.filter((data) => {
                return data[`porcentaje${idx}`] !== "";
            })[0];
            let porc = "";
            if (porcentaje != undefined)
                porc =
                    porcentaje[`porcentaje${idx}`] != null
                        ? porcentaje[`porcentaje${idx}`]
                        : "";
            consolelog(porc);
            porc = parseInt(porc);
            poraspx = !isNaN(porc) ? porc : "";
            let fechas = datosTabla
                .filter((data) => {
                    return data[`fechaa${idx}`] !== "";
                })
                .map((data) => data[`fechaa${idx}`])[0];
            consolelog(fechas);
            faspx = fechas != null ? fechas : "";

            if (faspx === "0000-00-00" || faspx === "")
                faspx = new Date().toDateInputValue();
            consolelog(faspx);

            modalNAPelement.querySelector("input[name='fecha']").value = faspx;
            modalNAPelement.querySelector("input[name='porcentaje']").value = poraspx;
            modalNAPelement.querySelector("textarea[name='aspecto']").value = aspx;
            modalNAP.show();
        },
    },
    /*{
      label: "",
      action: async function (e, column) {
         
          consolelog(datosTabla);
          colT = column;
          let key = column.getField();
          let idx = key.slice(key.lastIndexOf("N") + 1, key.length);
          consolelog(column._column.definition.title);
          let colA = column._column.definition.title.slice(1);
          let aspecto = datosTabla.filter(data => {
              return data[`aspecto${idx}`] !== "";
          })[0];
          consolelog(aspecto);
          let asp;
          if (aspecto != undefined)
              asp = aspecto[`aspecto${idx}`] != null ? aspecto[`aspecto${colA}`] : "";
          
          aspx = asp;
          let porcentaje = datosTabla.filter(data => {
              return data[`porcentaje${idx}`] !== "";
          })[0];
          let porc = "";
          if (porcentaje != undefined)
              porc = porcentaje[`porcentaje${idx}`] != null ? porcentaje[`porcentaje${idx}`] : "";
          consolelog(porc);
          porc = parseInt(porc);
          poraspx = porc;
          let fechas = datosTabla.filter(data => {
              return data[`fechaa${idx}`] !== "";
          }).map(data => data[`fechaa${idx}`])[0];
          consolelog(fechas);
          faspx = fechas != null ? fechas : "";
  
          if ((faspx === "0000-00-00") || (faspx === ""))
              faspx = (new Date()).toDateInputValue();
          consolelog(faspx);
          let html = `
                  <form id="frmAspectos">
                  <div class="form-group"> 
                      <label for="Fecha">Porcentaje</label>
                      <input type="number" class="form-control pct" name="porcentaje" inputmode="numeric" value='${porc}' onblur=aspectosFrm(this) onkeypress=ky(event) min="1" max="100" oninput=oin(event)>
                  </div>
                      <div class="form-group">
                          <label for="Aspecto">Aspecto</label>
                          <textarea type="text" class="form-control text-primary aspx" name="aspecto"  value='${aspx}' onblur=aspectosFrm(this)>${asp}</textarea>
                      </div>
                      <div class="form-group">
                          <label for="Fecha">Fecha</label>
                          <input type="date" class="form-control faspx" name="fecha"  value='${faspx}' onblur=aspectosFrm(this)>
                      </div>
                    
                  </form>
                  ${spnG2}
              `;
         
          let response = await swal.fire({
              title: column._column.definition.title,
              showCloseButton: true,
              allowOutsideClick: false,
              allowEscapeKey: false,
              html: html,
              confirmButtonText: `Guardar Aspecto`,
              willOpen: () => {
                  let frmAspectos = document.getElementById("frmAspectos");
                  frmAspectos.querySelector("input[name=fecha]").value = faspx;
                  frmAspectos.querySelector("input[name=porcentaje]").value = poraspx;
                  frmAspectos.querySelector("textarea[name=aspecto]").value = aspx;
  
              },
              willClose: () => {
                  let htmlc = Swal.getHtmlContainer();
                  consolelog(htmlc);
                  htmlc.querySelector('.spng2').classList.remove("d-none");
                  Swal.update();
                  consolelog(aspx);
                  consolelog(faspx);
                  isNaN(poraspx) ? poraspx = undefined : poraspx = poraspx;
                  consolelog(poraspx, aspx, faspx);
  
              }
          });
          consolelog(response);
          if (response.isConfirmed) {
              Swal.fire({
                  title: "Aspectos Asignados y porcentajes",
                  toast: true,
  
              });
  
              let key = column.getField();
              let idx = key.slice(key.lastIndexOf("N") + 1, key.length);
              consolelog(idx);
              let datosTablaT = datosTabla.map(dato => {
                  let datoT = new Object();
                  datoT = { ...dato };
                  datoT[`aspecto${idx}`] = aspx;
                  datoT[`porcentaje${idx}`] = poraspx;
                 
                  datoT[`fechaa${idx}`] = faspx;
                
                  consolelog(datoT);
                  return datoT;
              });
              consolelog(datosTablaT);
              datosTabla = [];
              datosTabla = undefined;
              datosTabla = [...datosTablaT];
              table.setData(datosTabla);
  
  
              consolelog(datosTablaT);
           
              poraspx = undefined;
              aspx = undefined;
              faspx = undefined;
              consolelog(datosTabla);
              await calcTotal(datosTabla.length);
              drawAllCells(datosTabla.length);
            
          }
  
      }
  }*/
    /*     {
              label: "Tareas",
              action: function (e, column) {
                  swal.fire("Hola Tarea");
              }
          },
          {
              label: "Exámen",
              action: function (e, column) {
                  swal.fire("Hola Exámen");
              }
          }, */
];

const drawCells = (row, dataRRT) => {
    if (dataRRT != undefined) {
        consolelog(dataRRT);
        if (parseFloat(dataRRT.Val) >= 3) {
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
};

const drawAllCells = (cant) => {
    for (let i = 0; i < cant; i++) {
        if (parseFloat(datosTabla[i].Val) < 3 && datosTabla[i].Val != null) {
            table.rowManager.rows[i].cells[2].element.style.backgroundColor = "red";
            table.rowManager.rows[i].cells[2].element.style.color = "white";
        } else if (datosTabla[i].Val != null) {
            table.rowManager.rows[i].cells[2].element.style.backgroundColor = "green";
            table.rowManager.rows[i].cells[2].element.style.color = "white";
        }
        Object.keys(datosTabla[i]).forEach((key) => {
            if (key.substring(0, 1) === "N" && key != "Nombres") {
                if (parseFloat(datosTabla[i][key]) < 3) {
                    table.rowManager.rows[i].cells[
                        parseInt(key.substring(1)) + 2
                    ].element.style.color = "red";
                }
            }
        });
        /*for (let j = 3; j <= 15; j++) {
                
                if (parseFloat(datos) < 3)
                    table.rowManager.rows[i].cells[j].element.style.color = "red";
            }*/
    }
};


const drawAllCellsT = (cant) => {
    console.log(table)
    console.log(table.columnManager.columnsByField)
    table.columnManager.columnsByField.Nombres.element.style.width = "270px";
    for (let i = 0; i < table.rowManager.rows.length; i++) {
        table.rowManager.rows[i].cells[1].element.style.width = "270px";
        const { value } = table.rowManager.rows[i].cells[2];
        console.log(value)
        if (value && value < 3) {
            table.rowManager.rows[i].cells[2].element.style.backgroundColor = "red";
            table.rowManager.rows[i].cells[2].element.style.color = "white";
        } else if (value) {
            table.rowManager.rows[i].cells[2].element.style.backgroundColor = "green";
            table.rowManager.rows[i].cells[2].element.style.color = "white";
        }
        console.log(table.rowManager.rows[i].cells)
        for (let j = 3; j < 63; j++) {
            const { value } = table.rowManager.rows[i].cells[j];
            if (value & !isNaN(value) & value < 3) {
                table.rowManager.rows[i].cells[j].element.style.backgroundColor = "red";
                table.rowManager.rows[i].cells[j].element.style.color = "white";
            }
            if (j >= 15) {
                table.rowManager.rows[i].cells[j].element.style.display = "none";
                table.columnManager.columnsByIndex[j].element.style.display = "none";
            }

        }
    }
};



function countNonEmptyGroups(array) {
    // Crear los 5 grupos con .slice
    const groups = [
        array.slice(0, 3),  // índices 0..2
        array.slice(3, 6),  // índices 3..5
        array.slice(6, 9),  // índices 6..8
        array.slice(9, 10), // índice 9
        array.slice(10, 11), // índice 10
    ];

    // Definimos una función para saber si un valor está vacío/espacios
    const isEmpty = (val) => !val || String(val).trim() === "";

    // Contamos cuántos “no vacíos” hay en cada subarreglo
    return groups.map(
        (group) => group.filter((val) => !isEmpty(val)).length
    );
}

const calcTotal = async (cant) => {
    console.log(cant)
    for (let i = 0; i < cant; i++) {
        try {
            dataRR = await table.rowManager.rows[i].getData();

            var Val = 0;
            var n = 0;
            let porcentajes = false;
            var Saber35 = 0;
            var Hacer35 = 0;
            var Ser20 = 0;
            var Autoe5 = 0;
            var Coev5 = 0;
            var p1;
            var p2;
            var p3;
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
            console.log({ arraycounts });
            let contadores = countNonEmptyGroups(arraycounts)
            //   consolelog(n);
            //   consolelog((Val / (n)).toFixed(2));
            //   consolelog(n, Val);
            if (n > 0 && !porcentajes) dataRR.Val = (Val / n).toFixed(2);
            else if (porcentajes) dataRR.Val = Val.toFixed(2);
            else dataRR.Val = 0;
            dataRR.Val = (parseFloat(Saber35 / (contadores[0] != 0 ? contadores[0] : 1)) * 0.35 + parseFloat(Hacer35 / (contadores[1] != 0 ? contadores[1] : 1)) * 0.35 + parseFloat(Ser20 / (contadores[2] != 0 ? contadores[2] : 1)) * 0.20 + parseFloat(Autoe5) * 0.05 + parseFloat(Coev5) * 0.05).toFixed(2);
            console.log(dataRR.Val);
            var rowElement = table.rowManager.rows[i].getElement();
            rowElement.children[2].innerText = dataRR.Val;
            datosTabla[i] = dataRR;
        } catch (error) {
            consolelog(error);
        }
    }
};



document.querySelector(".ctnts").addEventListener("click", async (e) => {
    e.preventDefault(); if (!_svl_animate) return;
    await calcTotal(datosTabla.length);
});

/*
document.querySelector(".inastabs").addEventListener("click", async (e) => {

    
    let tab = e.target.closest('a');
    consolelog(tab);
    document.querySelector(".inastabs").querySelectorAll('a').forEach(a => {
        a.classList.remove("active");
    });
    tab.classList.add("active");

});*/

document.querySelector(".telacudx").addEventListener("click", async (e) => {
    let estudiante = document.getElementById("estudiantei").value;
    let response = await fetch("getDataAcudiente.php", {
        method: "POST",
        body: JSON.stringify({
            estudiante,
        }),
        headers: {
            "Content-Type": "application/json",
        },
    });
    let dataAcudiente = await response.json();

    let html = `<p>Acudiente</p>${dataAcudiente[0].acudiente} 
              <p>Teléfono 1</p>
              <a class="ui-btn" href='tel:+57${dataAcudiente[0].telefono1}'>${dataAcudiente[0].telefono1}</a></p>	
              <p>Teléfono 2</p>
              <a class="ui-btn" href='tel:+57${dataAcudiente[0].telefono2}'>${dataAcudiente[0].telefono2}</a></p>		
    `;
    swal.fire({
        title: "<strong>Contacto</strong>",
        html: html,
    });
});



let DatosEstux;
const Inasistencias = async (data) => {
    document.querySelector(".float").classList.add("d-none");
    if (!modalInasistencias) {
        modalInasistencias = new bootstrap.Modal(
            document.getElementById("modalInasistencias")
        );
    }
    consolelog(data);
    DatosEstux = data;
    let { estudiante, Nombres } = data;
    document.querySelector("#fechainasistencia").value =
        new Date().toDateInputValue();
    document.querySelector(".nestudiantei").innerText = `${Nombres}`;
    document.getElementById("estudiantei").value = estudiante;
    document.getElementById("periodoi").value =
        document.getElementById("periodonotas").value;
    document.getElementById("docentei").value = elDocente;
    document.getElementById("materiai").value = Asignatura;
    document.getElementById("niveli").value = Nivel;
    document.getElementById("numeroi").value = Numero;
    document.getElementById("asignacioni").value = Asignacion;
    if (!firstTabEl) {
        var firstTabEl = document.querySelector(".inastabs li:first-child a");
        var firstTab = new bootstrap.Tab(firstTabEl);
    }
    firstTab.show();
    modalInasistencias.show();

    document
        .getElementById("modalInasistencias")
        .addEventListener("hidden.bs.modal", function (event) {
            document.querySelector(".float").classList.remove("d-none");
        });
};

const getCurrentPeriodo = async () => {
    let response = await fetch("getPeriodosNotas.php");
    let periodos = await response.json();
    let periodoActual = periodos
        .filter((periodo) => periodo.selected === "selected")
        .map((periodo) => periodo.nombre)[0];
    return periodoActual;
};

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
