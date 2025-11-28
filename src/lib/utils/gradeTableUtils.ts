/**
 * Grade Table Utilities
 * 
 * Utility functions for grade calculations, formatting, and validation
 * used in the GradesTable2 component with Tabulator.
 */

import type { CellComponent } from "tabulator-tables";

/**
 * Grade data structure for a student row
 */
export interface GradeData {
    Nombres: string;
    Val: string;
    N1?: string | null;
    N2?: string | null;
    N3?: string | null;
    N4?: string | null;
    N5?: string | null;
    N6?: string | null;
    N7?: string | null;
    N8?: string | null;
    N9?: string | null;
    N10?: string | null;
    N11?: string | null;
    N12?: string | null;
    aspecto1?: string | null;
    aspecto2?: string | null;
    aspecto3?: string | null;
    aspecto4?: string | null;
    aspecto5?: string | null;
    aspecto6?: string | null;
    aspecto7?: string | null;
    aspecto8?: string | null;
    aspecto9?: string | null;
    aspecto10?: string | null;
    aspecto11?: string | null;
    aspecto12?: string | null;
    porcentaje1?: string | null;
    porcentaje2?: string | null;
    porcentaje3?: string | null;
    porcentaje4?: string | null;
    porcentaje5?: string | null;
    porcentaje6?: string | null;
    porcentaje7?: string | null;
    porcentaje8?: string | null;
    porcentaje9?: string | null;
    porcentaje10?: string | null;
    porcentaje11?: string | null;
    porcentaje12?: string | null;
    fecha1?: string | null;
    fecha2?: string | null;
    fecha3?: string | null;
    fecha4?: string | null;
    fecha5?: string | null;
    fecha6?: string | null;
    fecha7?: string | null;
    fecha8?: string | null;
    fecha9?: string | null;
    fecha10?: string | null;
    fecha11?: string | null;
    fecha12?: string | null;
    fechaa1?: string | null;
    fechaa2?: string | null;
    fechaa3?: string | null;
    fechaa4?: string | null;
    fechaa5?: string | null;
    fechaa6?: string | null;
    fechaa7?: string | null;
    fechaa8?: string | null;
    fechaa9?: string | null;
    fechaa10?: string | null;
    fechaa11?: string | null;
    fechaa12?: string | null;
    [key: string]: any;
}

/**
 * Validation parameters for grade values
 */
export interface ValidationParams {
    min: number;
    max: number;
}

/**
 * History entry for undo/redo functionality
 */
export interface HistoryEntry {
    rowId: string;
    field: string;
    oldValue: any;
    newValue: any;
    timestamp: number;
}

/**
 * Converts a Date object to YYYY-MM-DD format for date inputs
 * 
 * @param date - Date object to convert
 * @returns Date string in YYYY-MM-DD format
 */
export const toDateInputValue = (date: Date): string => {
    const local = new Date(date);
    local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return local.toJSON().slice(0, 10);
};

/**
 * Counts non-empty values in predefined grade groups
 * Groups: N1-N3 (Saber), N4-N6 (Hacer), N7-N9 (Ser), N10 (Autoe), N11 (Coev)
 * 
 * @param array - Array of grade values
 * @returns Array of counts for each group
 */
export const countNonEmptyGroups = (array: any[]): number[] => {
    const groups = [
        array.slice(0, 3),   // N1-N3 (Saber 35%)
        array.slice(3, 6),   // N4-N6 (Hacer 35%)
        array.slice(6, 9),   // N7-N9 (Ser 20%)
        array.slice(9, 10),  // N10 (Autoevaluación 5%)
        array.slice(10, 11), // N11 (Coevaluación 5%)
    ];

    const isEmpty = (val: any) => !val || String(val).trim() === "";

    return groups.map(
        (group) => group.filter((val) => !isEmpty(val)).length,
    );
};

/**
 * Calculates the final grade (Val) for a student based on weighted averages
 * 
 * Weights:
 * - Saber (N1-N3): 35%
 * - Hacer (N4-N6): 35%
 * - Ser (N7-N9): 20%
 * - Autoevaluación (N10): 5%
 * - Coevaluación (N11): 5%
 * 
 * @param data - Student grade data
 * @returns Calculated final grade as string with 2 decimals
 */
export const calculateRowVal = (data: GradeData): string => {
    const noteFields = [
        "N1", "N2", "N3", "N4", "N5", "N6",
        "N7", "N8", "N9", "N10", "N11",
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

/**
 * Formats a grade value for display in the table
 * Applies color coding: red for grades < 3.0
 * 
 * @param cell - Tabulator cell component
 * @returns Formatted grade string or empty string
 */
export const gradeFormatter = (cell: CellComponent): string => {
    const val = cell.getValue();
    const element = cell.getElement();

    if (val === " " || val === "" || val === null || val === undefined) {
        element.style.color = ""; // Reset color
        return "";
    }

    const num = parseFloat(val);

    // Paint red if less than 3.0
    if (!isNaN(num) && num < 3.0) {
        element.style.color = "#ef4444"; // red-500
        element.style.fontWeight = "bold";
    } else {
        element.style.color = ""; // Reset color
        element.style.fontWeight = "";
    }

    return isNaN(num) ? val : num.toFixed(1);
};

/**
 * Validates a grade value against min/max constraints
 * Allows empty values (space, empty string, null, undefined)
 * 
 * @param value - Value to validate
 * @param parameters - Validation parameters (min, max)
 * @returns true if valid, false otherwise
 */
export const validateGrade = (
    value: any,
    parameters: ValidationParams
): boolean => {
    // Allow empty values
    if (
        value === "" ||
        value === " " ||
        value === null ||
        value === undefined
    ) {
        return true;
    }

    // Convert to number and validate range
    const numValue = parseFloat(value);

    if (isNaN(numValue)) {
        return false;
    }

    return numValue >= parameters.min && numValue <= parameters.max;
};

/**
 * Debounces a function call
 * 
 * @param func - Function to debounce
 * @param wait - Wait time in milliseconds
 * @returns Debounced function
 */
export const debounce = <T extends (...args: any[]) => any>(
    func: T,
    wait: number
): ((...args: Parameters<T>) => void) => {
    let timeout: ReturnType<typeof setTimeout> | null = null;

    return (...args: Parameters<T>) => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};

/**
 * Formats a number to a specific decimal precision
 * 
 * @param value - Value to format
 * @param decimals - Number of decimal places
 * @returns Formatted string
 */
export const formatNumber = (value: any, decimals: number = 1): string => {
    const num = parseFloat(value);
    if (isNaN(num)) return "";
    return num.toFixed(decimals);
};

/**
 * Checks if a value is empty (null, undefined, empty string, or whitespace)
 * 
 * @param value - Value to check
 * @returns true if empty, false otherwise
 */
export const isEmpty = (value: any): boolean => {
    return value === null ||
        value === undefined ||
        value === "" ||
        (typeof value === "string" && value.trim() === "");
};

/**
 * Gets the grade category based on the numeric value
 * 
 * @param grade - Numeric grade value
 * @returns Category string: "excellent", "good", "acceptable", or "low"
 */
export const getGradeCategory = (grade: number): string => {
    if (grade >= 4.5) return "excellent";
    if (grade >= 4.0) return "good";
    if (grade >= 3.0) return "acceptable";
    return "low";
};

/**
 * Extracts the column number from a field name (e.g., "N1" -> "1")
 * 
 * @param fieldName - Field name (e.g., "N1", "aspecto1")
 * @returns Column number as string
 */
export const extractColumnNumber = (fieldName: string): string => {
    return fieldName.replace(/[^\d]/g, "");
};
