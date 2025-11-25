<script lang="ts">
    import { onMount, onDestroy } from "svelte";
    import Chart from "chart.js/auto";
    import type { InfoCantData } from "./types";
    import { theme } from "./themeStore";

    export let data: InfoCantData[] = [];

    let doughnutCanvas: HTMLCanvasElement;
    let barCanvas: HTMLCanvasElement;
    let doughnutChart: Chart | null = null;
    let barChart: Chart | null = null;

    // Colors for charts - Premium Palette
    const colors = [
        "rgba(99, 102, 241, 0.8)", // Indigo
        "rgba(236, 72, 153, 0.8)", // Pink
        "rgba(16, 185, 129, 0.8)", // Emerald
        "rgba(245, 158, 11, 0.8)", // Amber
        "rgba(59, 130, 246, 0.8)", // Blue
        "rgba(139, 92, 246, 0.8)", // Violet
    ];

    const borderColors = [
        "rgba(99, 102, 241, 1)",
        "rgba(236, 72, 153, 1)",
        "rgba(16, 185, 129, 1)",
        "rgba(245, 158, 11, 1)",
        "rgba(59, 130, 246, 1)",
        "rgba(139, 92, 246, 1)",
    ];

    function processData() {
        const sedeTotals: { [key: string]: number } = {};
        const sedeNivelData: { [key: string]: { [key: number]: number } } = {};
        const allNiveles = new Set<number>();

        data.forEach((item) => {
            // Sede Totals
            sedeTotals[item.sede] =
                (sedeTotals[item.sede] || 0) + item.total_estudiantes;

            // Sede-Nivel Data
            if (!sedeNivelData[item.sede]) {
                sedeNivelData[item.sede] = {};
            }
            sedeNivelData[item.sede][item.nivel] =
                (sedeNivelData[item.sede][item.nivel] || 0) +
                item.total_estudiantes;
            allNiveles.add(item.nivel);
        });

        const sortedNiveles = Array.from(allNiveles).sort((a, b) => a - b);
        const sedes = Object.keys(sedeTotals);

        return {
            sedes,
            sedeTotalsValues: sedes.map((s) => sedeTotals[s]),
            sortedNiveles,
            sedeNivelData,
        };
    }

    function initCharts() {
        if (!data || data.length === 0) return;

        const { sedes, sedeTotalsValues, sortedNiveles, sedeNivelData } =
            processData();
        const isDark = $theme === "dark";
        const textColor = isDark ? "#cbd5e1" : "#475569";
        const gridColor = isDark
            ? "rgba(255, 255, 255, 0.1)"
            : "rgba(0, 0, 0, 0.05)";

        // 1. Doughnut Chart: Students per Sede
        if (doughnutCanvas) {
            const doughnutLabelsLine = {
                id: "doughnutLabelsLine",
                afterDatasetsDraw(chart: any) {
                    const {
                        ctx,
                        data: { datasets },
                    } = chart;

                    datasets.forEach((dataset: any, i: number) => {
                        const meta = chart.getDatasetMeta(i);
                        const total = dataset.data.reduce(
                            (acc: number, val: number) => acc + val,
                            0,
                        );

                        meta.data.forEach((element: any, index: number) => {
                            const value = dataset.data[index];
                            const percentage =
                                ((value / total) * 100).toFixed(1) + "%";

                            const { x, y } = element.tooltipPosition();

                            ctx.save();
                            ctx.fillStyle = "#fff";
                            ctx.font = "bold 12px Inter";
                            ctx.textAlign = "center";
                            ctx.textBaseline = "middle";
                            ctx.shadowColor = "rgba(0, 0, 0, 0.5)";
                            ctx.shadowBlur = 4;
                            ctx.fillText(percentage, x, y);
                            ctx.restore();
                        });
                    });
                },
            };

            doughnutChart = new Chart(doughnutCanvas, {
                type: "doughnut",
                data: {
                    labels: sedes,
                    datasets: [
                        {
                            data: sedeTotalsValues,
                            backgroundColor: colors,
                            borderColor: borderColors,
                            borderWidth: 1,
                            hoverOffset: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                color: textColor,
                                font: { family: "Inter" },
                            },
                        },
                        title: {
                            display: true,
                            text: "Distribución por Sede",
                            color: textColor,
                            font: {
                                size: 16,
                                family: "Outfit",
                                weight: "bold",
                            },
                        },
                    },
                },
                plugins: [doughnutLabelsLine],
            });
        }

        // 2. Bar Chart: Students per Nivel by Sede
        if (barCanvas) {
            const datasets = sedes.map((sede, index) => ({
                label: sede,
                data: sortedNiveles.map(
                    (nivel) => sedeNivelData[sede][nivel] || 0,
                ),
                backgroundColor: colors[index % colors.length],
                borderColor: borderColors[index % borderColors.length],
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.7,
            }));

            barChart = new Chart(barCanvas, {
                type: "bar",
                data: {
                    labels: sortedNiveles.map((n) => `Nivel ${n}`),
                    datasets: datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { family: "Inter" },
                            },
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { family: "Inter" },
                            },
                            beginAtZero: true,
                        },
                    },
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                color: textColor,
                                font: { family: "Inter" },
                            },
                        },
                        title: {
                            display: true,
                            text: "Estudiantes por Nivel y Sede",
                            color: textColor,
                            font: {
                                size: 16,
                                family: "Outfit",
                                weight: "bold",
                            },
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                        },
                    },
                },
            });
        }
    }

    onMount(() => {
        initCharts();
    });

    onDestroy(() => {
        if (doughnutChart) doughnutChart.destroy();
        if (barChart) barChart.destroy();
    });

    // Re-render on theme change
    $: if ($theme) {
        if (doughnutChart) doughnutChart.destroy();
        if (barChart) barChart.destroy();
        initCharts();
    }
</script>

<div class="charts-container">
    <div class="chart-card glass-effect">
        <div class="canvas-wrapper">
            <canvas bind:this={doughnutCanvas}></canvas>
        </div>
    </div>

    <div class="chart-card glass-effect">
        <div class="canvas-wrapper">
            <canvas bind:this={barCanvas}></canvas>
        </div>
    </div>
</div>

<style>
    .charts-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1rem;
        width: 100%;
    }

    @media (min-width: 768px) {
        .charts-container {
            grid-template-columns: 1fr 1fr;
        }
        .chart-card {
            grid-column: span 2;
        }
    }

    .chart-card {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 300px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    :global(.dark) .chart-card {
        background: rgba(30, 41, 59, 0.5);
    }

    .chart-card:hover {
        transform: translateY(-2px);
    }

    .canvas-wrapper {
        position: relative;
        width: 100%;
        height: 300px;
    }
</style>
