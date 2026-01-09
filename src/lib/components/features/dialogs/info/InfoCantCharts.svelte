<script lang="ts">
    import { onMount, onDestroy } from "svelte";
    import Chart from "chart.js/auto";
    import type { InfoCantData } from "../../../../types";
    import { theme } from "../../../../themeStore";

    export let data: InfoCantData[] = [];

    let doughnutCanvas: HTMLCanvasElement;
    let barCanvas: HTMLCanvasElement;
    let doughnutChart: Chart | null = null;
    let barChart: Chart | null = null;

    // Colors for charts - Refined Premium Palette
    const colors = [
        "rgba(99, 102, 241, 0.85)", // Indigo - deeper
        "rgba(236, 72, 153, 0.85)", // Pink - vibrant
        "rgba(16, 185, 129, 0.85)", // Emerald - rich
        "rgba(245, 158, 11, 0.85)", // Amber - warm
        "rgba(59, 130, 246, 0.85)", // Blue - crisp
        "rgba(139, 92, 246, 0.85)", // Violet - deep
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
        const textColor = isDark ? "#e2e8f0" : "#475569";
        const titleColor = isDark ? "#f1f5f9" : "#1e293b";
        const gridColor = isDark
            ? "rgba(255, 255, 255, 0.08)"
            : "rgba(0, 0, 0, 0.06)";

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
                            ctx.fillStyle = "#ffffff";
                            ctx.font = "600 13px Inter";
                            ctx.textAlign = "center";
                            ctx.textBaseline = "middle";
                            ctx.shadowColor = "rgba(0, 0, 0, 0.3)";
                            ctx.shadowBlur = 3;
                            ctx.shadowOffsetY = 1;
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
                            borderWidth: 2,
                            hoverOffset: 8,
                            hoverBorderWidth: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10,
                            left: 10,
                            right: 10,
                        },
                    },
                    plugins: {
                        legend: {
                            position:
                                window.innerWidth < 640 ? "bottom" : "right",
                            labels: {
                                color: textColor,
                                font: {
                                    family: "Inter",
                                    size: 13,
                                    weight: 500,
                                },
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: "circle",
                                boxWidth: 8,
                                boxHeight: 8,
                            },
                        },
                        title: {
                            display: true,
                            text: "Distribución por Sede",
                            color: titleColor,
                            font: {
                                size: 18,
                                family: "Outfit",
                                weight: "bold",
                            },
                            padding: {
                                top: 10,
                                bottom: 20,
                            },
                        },
                        tooltip: {
                            backgroundColor: isDark
                                ? "rgba(30, 41, 59, 0.95)"
                                : "rgba(255, 255, 255, 0.95)",
                            titleColor: isDark ? "#f1f5f9" : "#1e293b",
                            bodyColor: isDark ? "#e2e8f0" : "#475569",
                            borderColor: isDark
                                ? "rgba(71, 85, 105, 0.3)"
                                : "rgba(203, 213, 225, 0.5)",
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            titleFont: {
                                family: "Inter",
                                size: 13,
                                weight: 600,
                            },
                            bodyFont: {
                                family: "Inter",
                                size: 12,
                                weight: 500,
                            },
                            cornerRadius: 8,
                            displayColors: true,
                        },
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 800,
                        easing: "easeInOutQuart",
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
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.75,
                categoryPercentage: 0.85,
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
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 5,
                            left: 5,
                            right: 5,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: "Inter",
                                    size: 12,
                                    weight: 500,
                                },
                                padding: 8,
                            },
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawTicks: false,
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    family: "Inter",
                                    size: 12,
                                    weight: 500,
                                },
                                padding: 10,
                            },
                            beginAtZero: true,
                        },
                    },
                    plugins: {
                        legend: {
                            position: "top",
                            align: "start",
                            labels: {
                                color: textColor,
                                font: {
                                    family: "Inter",
                                    size: 13,
                                    weight: 500,
                                },
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: "rectRounded",
                                boxWidth: 10,
                                boxHeight: 10,
                            },
                        },
                        title: {
                            display: true,
                            text: "Estudiantes por Nivel y Sede",
                            color: titleColor,
                            font: {
                                size: 18,
                                family: "Outfit",
                                weight: "bold",
                            },
                            padding: {
                                top: 10,
                                bottom: 20,
                            },
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                            backgroundColor: isDark
                                ? "rgba(30, 41, 59, 0.95)"
                                : "rgba(255, 255, 255, 0.95)",
                            titleColor: isDark ? "#f1f5f9" : "#1e293b",
                            bodyColor: isDark ? "#e2e8f0" : "#475569",
                            borderColor: isDark
                                ? "rgba(71, 85, 105, 0.3)"
                                : "rgba(203, 213, 225, 0.5)",
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            titleFont: {
                                family: "Inter",
                                size: 13,
                                weight: 600,
                            },
                            bodyFont: {
                                family: "Inter",
                                size: 12,
                                weight: 500,
                            },
                            cornerRadius: 8,
                            displayColors: true,
                        },
                    },
                    interaction: {
                        mode: "index",
                        intersect: false,
                    },
                    animation: {
                        duration: 800,
                        easing: "easeInOutQuart",
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
        gap: 2rem;
        padding: 1.5rem;
        width: 100%;
    }

    @media (min-width: 640px) {
        .charts-container {
            padding: 2rem;
        }
    }

    @media (min-width: 1024px) {
        .charts-container {
            grid-template-columns: repeat(2, 1fr);
            gap: 2.5rem;
        }
    }

    .chart-card {
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.7),
            rgba(255, 255, 255, 0.5)
        );
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 2rem;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 320px;
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.08),
            0 2px 8px rgba(0, 0, 0, 0.04);
        backdrop-filter: blur(12px) saturate(120%);
        -webkit-backdrop-filter: blur(12px) saturate(120%);
        transition:
            transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
            box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .chart-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.8),
            transparent
        );
        opacity: 0.6;
    }

    :global(.dark) .chart-card {
        background: linear-gradient(
            135deg,
            rgba(30, 41, 59, 0.7),
            rgba(30, 41, 59, 0.5)
        );
        border: 1px solid rgba(71, 85, 105, 0.3);
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.4),
            0 2px 8px rgba(0, 0, 0, 0.2);
    }

    :global(.dark) .chart-card::before {
        background: linear-gradient(
            90deg,
            transparent,
            rgba(148, 163, 184, 0.2),
            transparent
        );
    }

    .chart-card:hover {
        transform: translateY(-4px);
        box-shadow:
            0 12px 48px rgba(0, 0, 0, 0.12),
            0 4px 12px rgba(0, 0, 0, 0.06);
    }

    :global(.dark) .chart-card:hover {
        box-shadow:
            0 12px 48px rgba(0, 0, 0, 0.6),
            0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .canvas-wrapper {
        position: relative;
        width: 100%;
        height: 320px;
    }

    @media (max-width: 639px) {
        .chart-card {
            padding: 1.5rem;
            min-height: 280px;
        }

        .canvas-wrapper {
            height: 280px;
        }
    }

    @media (min-width: 1024px) {
        .canvas-wrapper {
            height: 360px;
        }

        .chart-card {
            min-height: 360px;
        }
    }
</style>
