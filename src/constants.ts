// Archivo de constantes para endpoints de la aplicación IE de Occidente
// Agrega aquí otros endpoints relacionados cuando sea necesario.

export const API_BASE_URL = "https://app.iedeoccidente.com/consex2";
export const GET_CONCENTRADOR_ENDPOINT = `${API_BASE_URL}/getConcentrador2.php`;
export const GET_ASIGNACIONES_ENDPOINT = `${API_BASE_URL}/getasignacion.php`;
export const GET_NOTAS_DETALLADO_ENDPOINT = `${API_BASE_URL}/getNotasDetallado.php`;
export const GET_NOTAS_HISTORY_ENDPOINT = `${API_BASE_URL}/getNotasHistory.php`;
export const GET_PERIODOS_ENDPOINT = `${API_BASE_URL}/getPeriodos.php`;
export const GET_YEARS_ENDPOINT = `${API_BASE_URL}/getYearsData.php`;
export const GET_INFOCANT_ENDPOINT = `${API_BASE_URL}/getInfoCant.php`; // New endpoint for InfoCant
export const GET_INASISTENCIAS_DETALLADO_ENDPOINT = `${API_BASE_URL}/inasistenciasDetallado.php`;
export const GET_VALORACIONES_ENDPOINT = `${API_BASE_URL}/getValoraciones.php`;
export const GET_CONCENTRADOR_AREAS_ENDPOINT = `${API_BASE_URL}/getConcentradorAreas.php`;
export const GET_VALORACIONES_AREAS_ENDPOINT = `${API_BASE_URL}/getvala2.php`;
export const GET_NOTAS_DETALLADO_AREAS_ENDPOINT = `${API_BASE_URL}/getNotasDetalladoAreas.php`;
export const GET_ORDERS_ENDPOINT = `${API_BASE_URL}/getOrders.php`;
export const GET_CONSOLIDADO_CONVIVENCIA_ENDPOINT = `${API_BASE_URL}/consolidadoConvivenciaEstudiante.php`;
export const GET_CONVIVENCIA_DETALLADO_ENDPOINT = `${API_BASE_URL}/convivenciaDetallado.php`;
export const GET_NOTAS_ENDPOINT = `${API_BASE_URL}/getNotas2.php`;
export const GET_PERIODOS_NOTAS_ENDPOINT = `${API_BASE_URL}/getPeriodosNotas.php`;
export const LOGIN_ENDPOINT = `${API_BASE_URL}/login.php`;
export const GET_ESTUDIANTE_DETAILS_ENDPOINT = `${API_BASE_URL}/getEstugrupos.php`;
export const GET_GENERAR_MENU_ENDPOINT = `${API_BASE_URL}/generarMenu.php`;
export const GET_INFO_DOCENTES_ENDPOINT = API_BASE_URL + '/getInfoDocentes.php';
export const GET_DOCENTE_INFO_ENDPOINT = API_BASE_URL + '/getInfoDocente.php';
export const GOOGLE_LOGIN_ENDPOINT = `${API_BASE_URL}/google_login.php`;
export const GOOGLE_CLIENT_ID = "460775351784-6b3vev8mdsrcv2l67fu1bc1btb60qgq7.apps.googleusercontent.com";

// Ejemplo de uso (fetch):
// fetch(GET_CONCENTRADOR_ENDPOINT)
//   .then(r => r.json())
//   .then(data => console.log(data));


export const asignturastoN=[
  {
    "asignatura": "C. NATURA",
    "nombrec": "CIENCIAS NATURALES",
    "ruta": "/src/assets/icons/output/cienciasNaturales_no_bg.png"
  },
  {
    "asignatura": "C. POLITICAS",
    "nombrec": "CIENCIAS POLÍTICAS Y ECONÓMICAS",
    "ruta": "/src/assets/icons/output/cienciasPoliticas_no_bg.png"
  },
  {
    "asignatura": "CASTELLAN",
    "nombrec": "LENGUA CASTELLANA",
    "ruta": "/src/assets/icons/output/lenguaCastellana_no_bg.png"
  },
  {
    "asignatura": "CIVICA",
    "nombrec": "CÍVICA Y CONSTITUCIÓN",
    "ruta": "/src/assets/icons/output/civicaYconstitucion_no_bg.png"
  },
  {
    "asignatura": "COMP.SOC",
    "nombrec": "COMPORTAMIENTO SOCIAL",
    "ruta": "/src/assets/icons/output/comportamientoSocial_no_bg.png"
  },
  {
    "asignatura": "DANZAS",
    "nombrec": "DANZAS",
    "ruta": "/src/assets/icons/output/danzas_no_bg.png"
  },
  {
    "asignatura": "ED. ARTIST",
    "nombrec": "EDUCACIÓN ARTÍSTICA",
    "ruta": "/src/assets/icons/output/educacionArtistica_no_bg.png"
  },
  {
    "asignatura": "ED.FISICA",
    "nombrec": "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES",
    "ruta": "/src/assets/icons/output/educacionFisica_no_bg.png"
  },
  {
    "asignatura": "EMPREND.",
    "nombrec": "EMPRENDIMIENTO",
    "ruta": "/src/assets/icons/output/emprendimiento_no_bg.png"
  },
  {
    "asignatura": "ETICA",
    "nombrec": "ÉTICA Y VALORES HUMANOS",
    "ruta": "/src/assets/icons/output/etica_no_bg.png"
  },
  {
    "asignatura": "ETICA-PV Y R",
    "nombrec": "ÉTICA, PROYECTO DE VIDA Y RELIGIÓN",
    "ruta": "/src/assets/icons/output/eticayvaloresr_no_bg.png"
  },
  {
    "asignatura": "FILOSOFIA",
    "nombrec": "FILOSOFÍA",
    "ruta": "/src/assets/icons/output/filosofia_no_bg.png"
  },
  {
    "asignatura": "FISICA",
    "nombrec": "FÍSICA",
    "ruta": "/src/assets/icons/output/fisica_no_bg.png"
  },
  {
    "asignatura": "H. INGLES",
    "nombrec": "IDIOMA EXTRANJERO INGLÉS",
    "ruta": "/src/assets/icons/output/ingles_no_bg.png"
  },
  {
    "asignatura": "LECTURA",
    "nombrec": "LECTURA CRÍTICA",
    "ruta": "/src/assets/icons/output/lecturaCritica_no_bg.png"
  },
  {
    "asignatura": "MATEMATI",
    "nombrec": "MATEMÁTICAS",
    "ruta": "/src/assets/icons/output/matematicas_no_bg.png"
  },
  {
    "asignatura": "QUIMICA",
    "nombrec": "QUÍMICA",
    "ruta": "/src/assets/icons/output/quimica_no_bg.png"
  },
  {
    "asignatura": "RELIGION",
    "nombrec": "EDUCACIÓN RELIGIOSA",
    "ruta": "/src/assets/icons/output/religion_no_bg.png"
  },
  {
    "asignatura": "SOCIALES",
    "nombrec": "CIENCIAS SOCIALES e HISTORIA",
    "ruta": "/src/assets/icons/output/sociales_no_bg.png"
  },
  {
    "asignatura": "TALLER AMB.",
    "nombrec": "TALLER AMBIENTAL",
    "ruta": "/src/assets/icons/output/tallerambiental_no_bg.png"
  },
  {
    "asignatura": "TALLER CIUD.",
    "nombrec": "TALLER CIUDADANO",
    "ruta": "/src/assets/icons/output/tallerCiudadano_no_bg.png"
  },
  {
    "asignatura": "TALLER DEPOR",
    "nombrec": "TALLER DEPORTIVO",
    "ruta": "/src/assets/icons/output/tallerDeportivo_no_bg.png"
  },
  {
    "asignatura": "TALLER LIT.",
    "nombrec": "TALLER LITERARIO",
    "ruta": "/src/assets/icons/output/tallerLiterario_no_bg.png"
  },
  {
    "asignatura": "TEATRO",
    "nombrec": "TEATRO",
    "ruta": "/src/assets/icons/output/teatro_no_bg.png"
  },
  {
    "asignatura": "TECNOLOG",
    "nombrec": "TECNOLOGÍA E INFORMÁTICA",
    "ruta": "/src/assets/icons/output/tecnologia_no_bg.png"
  }
]

export const SHOW_COMPONENT_NAMES = false;
