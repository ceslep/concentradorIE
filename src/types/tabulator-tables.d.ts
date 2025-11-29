declare module 'tabulator-tables' {
    export default class Tabulator {
        constructor(element: string | HTMLElement, options?: any);
        destroy(): void;
        getData(): any[];
        getRows(): any[];
        download(type: string, filename: string, options?: any): void;
        setFilter(filters: any[]): void;
        clearFilter(): void;
        clearHeaderFilter(): void;
        hideColumn(field: string): void;
        showColumn(field: string): void;
        on(event: string, callback: Function): void;
        off(event: string, callback: Function): void;
        // Add other methods as needed
    }
}
