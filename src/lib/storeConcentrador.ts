import { writable, derived, get } from 'svelte/store';
import type { ConcentradorParsed, ConcentradorAreasParsed, Periodo, AsignaturaOrdenItem, EstudianteRow, Asignatura, AsignaturaNota, PeriodoValoracion, EstudianteRowArea, AreaNota } from './types';
import { defaultConcentradorPayload, fetchConcentrador, fetchConcentradorAreas } from './api';
import type { ConcentradorPayload } from './api';

// Writable Stores
export const parsed = writable<ConcentradorParsed | ConcentradorAreasParsed | null>(null);
export const loading = writable<boolean>(false);
export const error = writable<string | null>(null);
export const payload = writable<ConcentradorPayload>(defaultConcentradorPayload);
export const showPeriodos = writable<boolean>(false);
export const selectedPeriodos = writable<string[]>(['UNO', 'DOS', 'TRES', 'CUATRO', 'DEF']);
export const lastDuration = writable<number>(0);
export const concentradorType = writable<'asignaturas' | 'areas'>('asignaturas'); // Default to asignaturas
export const selectedAsignatura = writable<string | null>(null); // For GradesTableDialog

// Derived Stores
export const currentOrden = derived([parsed, concentradorType], ([$parsed, $concentradorType]) => {
    if (!$parsed) return [];
    if ($concentradorType === 'asignaturas') {
        return ($parsed as ConcentradorParsed).asignaturasOrden?.filter(Boolean) ?? [];
    } else {
        const p = $parsed as ConcentradorAreasParsed;
        return p.areas ? p.areas.map(area => ({ abreviatura: area.abreviatura, docenteId: '' })) : [];
    }
});

// Functions
export function toggleShowPeriodos() {
    showPeriodos.update(v => !v);
}

export async function loadConcentradorData() {
    loading.set(true);
    error.set(null);
    const start = performance.now();
    try {
        const currentPayload = get(payload);
        const currentType = get(concentradorType);

        let data: ConcentradorParsed | ConcentradorAreasParsed;
        if (currentType === 'asignaturas') {
            data = await fetchConcentrador(currentPayload);
        } else {
            // Assuming there's a fetchConcentradorAreas function for areas type
            // For now, using fetchConcentrador for both or need to define fetchConcentradorAreas
            // data = await fetchConcentradorAreas(currentPayload);
            // Placeholder:
            data = await fetchConcentradorAreas(currentPayload);
        }
        
        parsed.set(data);
    } catch (e: any) {
        error.set(e.message);
        console.error('Error loading concentrador data:', e);
    } finally {
        const end = performance.now();
        lastDuration.set(end - start);
        loading.set(false);
    }
}


export function exportCSV() {
  const p = get(parsed);
  const currentType = get(concentradorType);
  if (!p) return;
  const sep = ',';

  let headerColumns: string[];
  let dataRows: string[];

  if (currentType === 'asignaturas') {
    const asign = (p as ConcentradorParsed).asignaturasOrden.filter(Boolean) as AsignaturaOrdenItem[];
    headerColumns = ['Estudiante', ...asign.map(a => `"${a.abreviatura}"`) ];
    dataRows = (p as ConcentradorParsed).estudiantes.map((est: EstudianteRow) => {
      const cols = asign.map((a: AsignaturaOrdenItem) => {
        const asig = est.asignaturas.find((x: AsignaturaNota) => x.asignatura === a.abreviatura);
        const def = asig?.periodos.find((p: PeriodoValoracion) => p.periodo === 'DEF')?.valoracion;
        return def != null ? def.toFixed(2) : '';
      });
      return `"${est.nombres.replace(/"/g,'""')}"${sep}${cols.join(sep)}`;
    });
  } else { // currentType === 'areas'
    const areas = (p as ConcentradorAreasParsed).areasOrden.filter(Boolean) as string[]; // areasOrden is string[] for areas
    headerColumns = ['Estudiante', ...areas.map(a => `"${a}"`) ];
    dataRows = (p as ConcentradorAreasParsed).estudiantes.map((est: EstudianteRowArea) => { // Assuming EstudianteRow also for areas
      const cols = areas.map((a: string) => {
        const area = est.areas?.find((x: AreaNota) => x.area === a); // Assuming EstudianteRow has 'areas' property
        const def = area?.periodos.find((p: PeriodoValoracion) => p.periodo === 'DEF')?.valoracion;
        return def != null ? def.toFixed(2) : '';
      });
      return `"${est.nombres.replace(/"/g,'""')}"${sep}${cols.join(sep)}`;
    });
  }

  const csv = [headerColumns.join(sep), ...dataRows].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'concentrador.csv';
  a.click();
  URL.revokeObjectURL(url);
}

export function exportExcel() {
  const p = get(parsed);
  const currentType = get(concentradorType);
  if (!p) return;
  const sep = '\t'; // Use tab as separator for Excel

  let headerColumns: string[];
  let dataRows: string[];

  if (currentType === 'asignaturas') {
    const asign = (p as ConcentradorParsed).asignaturasOrden.filter(Boolean) as AsignaturaOrdenItem[];
    headerColumns = ['Estudiante', ...asign.map(a => `"${a.abreviatura}"`) ];
    dataRows = (p as ConcentradorParsed).estudiantes.map((est: EstudianteRow) => {
      const cols = asign.map((a: AsignaturaOrdenItem) => {
        const asig = est.asignaturas.find((x: AsignaturaNota) => x.asignatura === a.abreviatura);
        const def = asig?.periodos.find((p: PeriodoValoracion) => p.periodo === 'DEF')?.valoracion;
        return def != null ? def.toFixed(2) : '';
      });
      return `"${est.nombres.replace(/"/g,'""')}"${sep}${cols.join(sep)}`;
    });
  } else { // currentType === 'areas'
    const areas = (p as ConcentradorAreasParsed).areasOrden.filter(Boolean) as string[];
    headerColumns = ['Estudiante', ...areas.map(a => `"${a}"`) ];
    dataRows = (p as ConcentradorAreasParsed).estudiantes.map((est: EstudianteRowArea) => { // Assuming EstudianteRow also for areas
      const cols = areas.map((a: string) => {
        const area = est.areas?.find((x: AreaNota) => x.area === a); // Assuming EstudianteRow has 'areas' property
        const def = area?.periodos.find((p: PeriodoValoracion) => p.periodo === 'DEF')?.valoracion;
        return def != null ? def.toFixed(2) : '';
      });
      return `"${est.nombres.replace(/"/g,'""')}"${sep}${cols.join(sep)}`;
    });
  }

  const csv = [headerColumns.join(sep), ...dataRows].join('\n');
  const blob = new Blob([csv], { type: 'application/vnd.ms-excel;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'concentrador.xls';
  a.click();
  URL.revokeObjectURL(url);
}