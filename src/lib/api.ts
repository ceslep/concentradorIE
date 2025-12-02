import { GET_CONCENTRADOR_ENDPOINT, GET_ASIGNACIONES_ENDPOINT, GET_NOTAS_DETALLADO_ENDPOINT, GET_PERIODOS_ENDPOINT, GET_YEARS_ENDPOINT, GET_INASISTENCIAS_DETALLADO_ENDPOINT, GET_VALORACIONES_ENDPOINT, GET_CONCENTRADOR_AREAS_ENDPOINT, GET_VALORACIONES_AREAS_ENDPOINT, GET_NOTAS_DETALLADO_AREAS_ENDPOINT, GET_ORDERS_ENDPOINT, GET_CONSOLIDADO_CONVIVENCIA_ENDPOINT, GET_CONVIVENCIA_DETALLADO_ENDPOINT, GET_ESTUDIANTE_DETAILS_ENDPOINT } from '../constants'
import type { ConcentradorParsed, ConcentradorAreasParsed, NotasDetalladoPayload, NotaDetalle, Periodo, Year, InasistenciasDetalladoPayload, Inasistencia, ValoracionPayload, Valoracion, ValoracionAreasResponseItem, AreaOrder, AreaOrderPayload, ConvivenciaRecord, ConsolidadoConvivenciaPayload, ConvivenciaDetallado, AsignaturaOrdenItem, EstudianteRow, Asignatura, Area, EstudianteDetalle } from './types' // Import EstudianteRow, Asignatura, Area

export interface Sede {
  ind: string;
  sede: string;
  grados: { nivel: string; numero: string }[];
}

export interface ConcentradorPayload {
  Asignacion: string
  nivel: string
  numero: string
  periodo: string
  year: string
  activos: boolean
}

// Update ConcentradorResponse to directly be ConcentradorParsed
export type ConcentradorResponse = ConcentradorParsed;

export const defaultConcentradorPayload: ConcentradorPayload = {
  Asignacion: '1',
  nivel: '11',
  numero: '1',
  periodo: 'CUATRO',
  year: new Date().getFullYear().toString(),
  activos: true
}

export async function fetchAsignaciones(): Promise<Sede[]> {
  const res = await fetch(GET_ASIGNACIONES_ENDPOINT)
  if (!res.ok) {
    throw new Error('Error al obtener asignaciones: ' + res.status)
  }
  return res.json()
}

export async function fetchPeriodos(): Promise<Periodo[]> {
  const res = await fetch(GET_PERIODOS_ENDPOINT)
  if (!res.ok) {
    throw new Error('Error al obtener periodos: ' + res.status)
  }
  return res.json()
}

export async function fetchYears(): Promise<Year[]> {
  const res = await fetch(GET_YEARS_ENDPOINT)
  if (!res.ok) {
    throw new Error('Error al obtener años: ' + res.status)
  }
  return res.json()
}

export async function fetchConcentrador(payload: ConcentradorPayload = defaultConcentradorPayload): Promise<ConcentradorParsed> {
  const res = await fetch(GET_CONCENTRADOR_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener concentrador: ' + res.status)
  }
  // Explicitly define an intermediate type for the raw response
  interface RawConcentradorResponse {
    estudiantes: EstudianteRow[];
    asignaturasOrden: string[]; // Expecting string[] from the raw API response
    asignaturas?: Asignatura[];
  }

  const rawData: RawConcentradorResponse = await res.json();

  // Parse asignaturasOrden from "x:y" string array to AsignaturaOrdenItem[]
  const parsedAsignaturasOrden: AsignaturaOrdenItem[] = rawData.asignaturasOrden.map(item => {
    const [abreviatura, docenteId] = item.split(':');
    return { abreviatura, docenteId };
  });

  return {
    ...rawData,
    asignaturasOrden: parsedAsignaturasOrden
  };
}

export async function fetchConcentradorAreas(payload: ConcentradorPayload = defaultConcentradorPayload): Promise<ConcentradorAreasParsed> {
  // Obtener estructura base (estudiantes y áreas)
  const resBase = await fetch(GET_CONCENTRADOR_AREAS_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!resBase.ok) {
    throw new Error('Error al obtener concentrador por áreas: ' + resBase.status)
  }
  const baseData = await resBase.json()
  
  // Obtener valoraciones
  const resValoraciones = await fetch(GET_VALORACIONES_AREAS_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!resValoraciones.ok) {
    throw new Error('Error al obtener valoraciones por áreas: ' + resValoraciones.status)
  }
  const valoracionesData: ValoracionAreasResponseItem[] = await resValoraciones.json()
  
  // Crear areasOrden a partir de baseData.areas
  const areasOrden = baseData.areas ? baseData.areas.map((a: Area) => a.abreviatura) : [];
  
  // Combinar datos: agregar valoraciones a cada estudiante
  const estudiantesConValoraciones = baseData.estudiantes.map((est: any) => {
    // Filtrar valoraciones de este estudiante
    const valoracionesEstudiante = valoracionesData.filter(v => v.estudiante === est.estudiante);
    
    // Agrupar por área (v.asignat es la abreviatura del área)
    const areasMap = new Map<string, number>();
    
    valoracionesEstudiante.forEach(v => {
      // v.asignat contiene la ABREVIATURA del área (CPOL, CNAT, etc.)
      // v.valoracion es la valoración DEFINITIVA
      areasMap.set(v.asignat, v.valoracion);
    });
    
    // Convertir a estructura AreaNota[]
    const areas = Array.from(areasMap.entries()).map(([areaAbrev, valoracionDef]) => {
      // Solo crear el período DEF con la valoración
      const periodos = [{
        periodo: 'DEF',
        valoracion: valoracionDef
      }];
      
      return {
        area: areaAbrev,
        periodos,
        estudianteId: est.estudiante.toString()
      };
    });
    
    return {
      id: est.estudiante.toString(),
      nombres: est.nombres,
      areas
    };
  });
  
  const result: ConcentradorAreasParsed = {
    estudiantes: estudiantesConValoraciones,
    areasOrden,
    areas: baseData.areas
  };
  
  return result;
}

export async function fetchNotasDetallado(payload: NotasDetalladoPayload): Promise<NotaDetalle[]> {
  const res = await fetch(GET_NOTAS_DETALLADO_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener notas detalladas: ' + res.status)
  }
  return res.json()
}

export async function fetchNotasDetalladoAreas(payload: NotasDetalladoPayload): Promise<NotaDetalle[]> {
  const res = await fetch(GET_NOTAS_DETALLADO_AREAS_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener notas detalladas por área: ' + res.status)
  }
  return res.json()
}

export async function fetchInasistenciasDetallado(payload: InasistenciasDetalladoPayload): Promise<Inasistencia[]> {
  const res = await fetch(GET_INASISTENCIAS_DETALLADO_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener inasistencias detalladas: ' + res.status)
  }
  return res.json()
}

export async function fetchValoracionesAreasData(payload: ConcentradorPayload = defaultConcentradorPayload): Promise<ValoracionAreasResponseItem[]> {
  const res = await fetch(GET_VALORACIONES_AREAS_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener valoraciones por áreas: ' + res.status)
  }
  return res.json()
}

export async function fetchAreaOrder(payload: AreaOrderPayload): Promise<string[]> {
  const res = await fetch(GET_ORDERS_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener el orden de las áreas: ' + res.status)
  }
  return res.json()
}

export async function fetchValoraciones(payload: ValoracionPayload): Promise<Valoracion> {
  const res = await fetch(GET_VALORACIONES_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener valoraciones: ' + res.status)
  }
  return res.json()
}

export async function fetchConsolidadoConvivencia(payload: ConsolidadoConvivenciaPayload): Promise<ConvivenciaRecord[]> {
  const res = await fetch(GET_CONSOLIDADO_CONVIVENCIA_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener el consolidado de convivencia: ' + res.status)
  }
  return res.json()
}

export async function fetchConvivenciaDetallado(payload: { ind: string, year: string }): Promise<ConvivenciaDetallado> {
  const res = await fetch(GET_CONVIVENCIA_DETALLADO_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  if (!res.ok) {
    throw new Error('Error al obtener el detalle de convivencia: ' + res.status)
  }
  const data = await res.json();
  if (data && data.length > 0) {
    return data[0]; // The API returns an array, but we expect a single object
  } else {
    throw new Error('No se encontraron detalles de convivencia.');
  }
}

export async function fetchStudentDetails(estudianteId: string, year: string): Promise<EstudianteDetalle> {
  const res = await fetch(GET_ESTUDIANTE_DETAILS_ENDPOINT, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ estudianteId, year }) // Added year to the body
  });
  if (!res.ok) {
    throw new Error('Error al obtener detalles del estudiante: ' + res.status);
  }
  const data = await res.json();
  if (data.error) {
    throw new Error(data.error);
  }
  // If the API unexpectedly returns an array with a single element, extract it
  if (Array.isArray(data) && data.length > 0) {
    return data[0];
  }
  return data;
}