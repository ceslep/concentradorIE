<script lang="ts">
    import { onMount } from "svelte";
    import type { GradeData } from "./utils/gradeTableUtils";
    import { theme } from "./themeStore";

    export let data: GradeData[] = [];

    // Statistics state
    let average = 0;
    let passRate = 0;
    let performanceLevels = {
        bajo: 0,
        basico: 0,
        alto: 0,
        superior: 0,
    };
    let totalStudents = 0;
    let activityAverages: number[] = [];
    let topStudents: { name: string; val: string }[] = [];
    let atRiskStudents: { name: string; val: string }[] = [];

    // Gaussian Distribution State
    let standardDeviation = 0;
    let bellCurvePoints: string = "";
    let histogramBins: { x: number; height: number; count: number }[] = [];

    // Reactive calculations
    $: if (data) {
        calculateStatistics();
    }

    function calculateStatistics() {
        if (!data || data.length === 0) {
            resetStats();
            return;
        }

        totalStudents = data.length;
        let sum = 0;
        let passedCount = 0;
        const levels = {
            bajo: 0,
            basico: 0,
            alto: 0,
            superior: 0,
        };

        // Activity sums and counts
        const activitySums = Array(12).fill(0);
        const activityCounts = Array(12).fill(0);

        const grades: number[] = [];

        data.forEach((student) => {
            const val = parseFloat(student.Val);
            if (!isNaN(val)) {
                sum += val;
                grades.push(val);

                if (val >= 3.0) passedCount++;

                if (val < 3.0) levels.bajo++;
                else if (val < 4.0) levels.basico++;
                else if (val < 4.6) levels.alto++;
                else levels.superior++;
            }

            // Calculate activity averages
            for (let i = 1; i <= 12; i++) {
                const grade = parseFloat(student[`N${i}`]);
                if (!isNaN(grade)) {
                    activitySums[i - 1] += grade;
                    activityCounts[i - 1]++;
                }
            }
        });

        average = sum / totalStudents;
        passRate = (passedCount / totalStudents) * 100;
        performanceLevels = levels;

        // Finalize activity averages
        activityAverages = activitySums.map((s, i) =>
            activityCounts[i] > 0 ? s / activityCounts[i] : 0,
        );

        // Top Students
        topStudents = [...data]
            .sort((a, b) => parseFloat(b.Val) - parseFloat(a.Val))
            .slice(0, 5)
            .map((s) => ({ name: s.Nombres, val: s.Val }));

        // At Risk Students
        atRiskStudents = data
            .filter((s) => parseFloat(s.Val) < 3.0)
            .sort((a, b) => parseFloat(a.Val) - parseFloat(b.Val)) // Lowest first
            .map((s) => ({ name: s.Nombres, val: s.Val }));

        // Gaussian Calculations
        calculateGaussianData(grades, average);
    }

    function calculateGaussianData(grades: number[], mean: number) {
        if (grades.length < 2) {
            standardDeviation = 0;
            bellCurvePoints = "";
            histogramBins = [];
            return;
        }

        // 1. Calculate Standard Deviation
        const variance =
            grades.reduce((acc, val) => acc + Math.pow(val - mean, 2), 0) /
            grades.length;
        standardDeviation = Math.sqrt(variance);

        // 2. Generate Histogram Bins (1.0 to 5.0, step 0.5)
        const bins = [];
        const binSize = 0.5;
        const minGrade = 1.0;
        const maxGrade = 5.0;

        for (let i = minGrade; i < maxGrade; i += binSize) {
            const count = grades.filter(
                (g) => g >= i && g < i + binSize,
            ).length;
            // Include 5.0 in the last bin
            if (i + binSize >= maxGrade) {
                const lastCount = grades.filter(
                    (g) => g >= i && g <= maxGrade,
                ).length;
                bins.push({ x: i, count: lastCount });
            } else {
                bins.push({ x: i, count });
            }
        }

        // Normalize histogram height to fit in chart (max height = 100)
        const maxCount = Math.max(...bins.map((b) => b.count), 1); // Avoid div by 0
        histogramBins = bins.map((b) => ({
            x: b.x,
            count: b.count,
            height: (b.count / maxCount) * 80, // Scale to 80% height
        }));

        // 3. Generate Bell Curve Points
        // Normal Distribution Formula: f(x) = (1 / (σ * √2π)) * e^(-0.5 * ((x - μ) / σ)^2)
        // We need to scale this to match our chart coordinates.
        // X axis: 1.0 to 5.0 mapped to 0 to 100% width
        // Y axis: mapped to match histogram scale roughly

        const points = [];
        const step = 0.1;
        const chartWidth = 100; // percent
        const chartHeight = 100; // percent

        // Find peak of normal distribution to scale Y
        const peakY = 1 / (standardDeviation * Math.sqrt(2 * Math.PI));

        for (let x = minGrade; x <= maxGrade; x += step) {
            const y =
                (1 / (standardDeviation * Math.sqrt(2 * Math.PI))) *
                Math.exp(-0.5 * Math.pow((x - mean) / standardDeviation, 2));

            // Map x (1-5) to 0-100
            const xPos = ((x - minGrade) / (maxGrade - minGrade)) * 100;

            // Map y to 0-100 (relative to peak, scaled to 90% height)
            const yPos = 100 - (y / peakY) * 90;

            points.push(`${xPos},${yPos}`);
        }

        bellCurvePoints = points.join(" ");
    }

    function resetStats() {
        average = 0;
        passRate = 0;
        performanceLevels = {
            bajo: 0,
            basico: 0,
            alto: 0,
            superior: 0,
        };
        totalStudents = 0;
        activityAverages = [];
        topStudents = [];
        atRiskStudents = [];
        standardDeviation = 0;
        bellCurvePoints = "";
        histogramBins = [];
    }

    function getBarWidth(count: number): string {
        if (totalStudents === 0) return "0%";
        return `${((count / totalStudents) * 100).toFixed(1)}%`;
    }

    function getActivityBarHeight(avg: number): string {
        return `${(avg / 5) * 100}%`;
    }
</script>

<div class="stats-container">
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue-100 text-blue-600">
                <span class="material-symbols-rounded">group</span>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Estudiantes</span>
                <span class="stat-value">{totalStudents}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green-100 text-green-600">
                <span class="material-symbols-rounded">functions</span>
            </div>
            <div class="stat-info">
                <span class="stat-label">Promedio General</span>
                <span class="stat-value">{average.toFixed(2)}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple-100 text-purple-600">
                <span class="material-symbols-rounded">check_circle</span>
            </div>
            <div class="stat-info">
                <span class="stat-label">Aprobación</span>
                <span class="stat-value">{passRate.toFixed(1)}%</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange-100 text-orange-600">
                <span class="material-symbols-rounded">query_stats</span>
            </div>
            <div class="stat-info">
                <span class="stat-label">Desviación Estándar</span>
                <span class="stat-value">{standardDeviation.toFixed(2)}</span>
            </div>
        </div>
    </div>

    <!-- Gaussian Distribution Chart -->
    <div class="distribution-section mb-6">
        <h3 class="section-title">
            Análisis de Distribución (Campana de Gauss)
        </h3>
        <div
            class="relative h-64 w-full bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
        >
            <!-- Y Axis Grid -->
            <div
                class="absolute inset-0 p-4 flex flex-col justify-between text-xs text-gray-400 pointer-events-none"
            >
                <div
                    class="border-b border-gray-200 dark:border-gray-700 w-full h-0"
                ></div>
                <div
                    class="border-b border-gray-200 dark:border-gray-700 w-full h-0"
                ></div>
                <div
                    class="border-b border-gray-200 dark:border-gray-700 w-full h-0"
                ></div>
                <div
                    class="border-b border-gray-200 dark:border-gray-700 w-full h-0"
                ></div>
                <div
                    class="border-b border-gray-200 dark:border-gray-700 w-full h-0"
                ></div>
            </div>

            <!-- Chart Area -->
            <svg
                class="w-full h-full overflow-visible"
                preserveAspectRatio="none"
                viewBox="0 0 100 100"
            >
                <!-- Histogram Bars -->
                {#each histogramBins as bin, i}
                    <rect
                        x={((bin.x - 1) / 4) * 100}
                        y={100 - bin.height}
                        width={12.5}
                        height={bin.height}
                        fill="rgba(99, 102, 241, 0.2)"
                        stroke="rgba(99, 102, 241, 0.5)"
                        stroke-width="0.5"
                        class="transition-all duration-500 hover:fill-indigo-300"
                    >
                        <title
                            >{bin.x.toFixed(1)} - {(bin.x + 0.5).toFixed(1)}: {bin.count}
                            estudiantes</title
                        >
                    </rect>
                    <!-- Count Label -->
                    {#if bin.count > 0}
                        <text
                            x={((bin.x - 1) / 4) * 100 + 6.25}
                            y={100 - bin.height - 2}
                            text-anchor="middle"
                            font-size="3"
                            fill="#6b7280">{bin.count}</text
                        >
                    {/if}
                {/each}

                <!-- Bell Curve -->
                {#if bellCurvePoints}
                    <polyline
                        points={bellCurvePoints}
                        fill="none"
                        stroke="#ef4444"
                        stroke-width="1"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="drop-shadow-md"
                    />
                    <!-- Mean Line -->
                    <line
                        x1={((average - 1) / 4) * 100}
                        y1="0"
                        x2={((average - 1) / 4) * 100}
                        y2="100"
                        stroke="#10b981"
                        stroke-width="0.5"
                        stroke-dasharray="2,2"
                    />
                {/if}
            </svg>

            <!-- X Axis Labels -->
            <div
                class="absolute bottom-0 left-0 right-0 flex justify-between px-4 text-xs text-gray-500 transform translate-y-4"
            >
                <span>1.0</span>
                <span>2.0</span>
                <span>3.0</span>
                <span>4.0</span>
                <span>5.0</span>
            </div>

            <!-- Legend -->
            <div
                class="absolute top-2 right-2 flex gap-3 text-xs bg-white/80 dark:bg-gray-800/80 p-2 rounded shadow-sm backdrop-blur-sm"
            >
                <div class="flex items-center gap-1">
                    <div
                        class="w-3 h-3 bg-indigo-200 border border-indigo-400"
                    ></div>
                    <span>Estudiantes</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-1 bg-red-500"></div>
                    <span>Curva Normal</span>
                </div>
                <div class="flex items-center gap-1">
                    <div
                        class="w-3 h-3 border-l border-green-500 border-dashed"
                    ></div>
                    <span>Promedio</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Performance Distribution -->
        <div class="distribution-section">
            <h3 class="section-title">Distribución de Desempeño</h3>
            <div class="distribution-chart">
                <!-- Bajo -->
                <div class="chart-row">
                    <div class="row-label">
                        <span class="label-text">Bajo</span>
                        <span class="label-range">(1.0 - 2.9)</span>
                    </div>
                    <div class="bar-container">
                        <div
                            class="bar bg-red-500"
                            style="width: {getBarWidth(performanceLevels.bajo)}"
                        ></div>
                    </div>
                    <div class="row-value">
                        {performanceLevels.bajo}
                        <span class="percentage"
                            >({getBarWidth(performanceLevels.bajo)})</span
                        >
                    </div>
                </div>

                <!-- Básico -->
                <div class="chart-row">
                    <div class="row-label">
                        <span class="label-text">Básico</span>
                        <span class="label-range">(3.0 - 3.9)</span>
                    </div>
                    <div class="bar-container">
                        <div
                            class="bar bg-yellow-500"
                            style="width: {getBarWidth(
                                performanceLevels.basico,
                            )}"
                        ></div>
                    </div>
                    <div class="row-value">
                        {performanceLevels.basico}
                        <span class="percentage"
                            >({getBarWidth(performanceLevels.basico)})</span
                        >
                    </div>
                </div>

                <!-- Alto -->
                <div class="chart-row">
                    <div class="row-label">
                        <span class="label-text">Alto</span>
                        <span class="label-range">(4.0 - 4.5)</span>
                    </div>
                    <div class="bar-container">
                        <div
                            class="bar bg-blue-500"
                            style="width: {getBarWidth(performanceLevels.alto)}"
                        ></div>
                    </div>
                    <div class="row-value">
                        {performanceLevels.alto}
                        <span class="percentage"
                            >({getBarWidth(performanceLevels.alto)})</span
                        >
                    </div>
                </div>

                <!-- Superior -->
                <div class="chart-row">
                    <div class="row-label">
                        <span class="label-text">Superior</span>
                        <span class="label-range">(4.6 - 5.0)</span>
                    </div>
                    <div class="bar-container">
                        <div
                            class="bar bg-green-500"
                            style="width: {getBarWidth(
                                performanceLevels.superior,
                            )}"
                        ></div>
                    </div>
                    <div class="row-value">
                        {performanceLevels.superior}
                        <span class="percentage"
                            >({getBarWidth(performanceLevels.superior)})</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Averages -->
        <div class="distribution-section">
            <h3 class="section-title">Promedio por Actividad (N1-N12)</h3>
            <div class="h-64 flex items-end justify-between gap-2 pt-4">
                {#each activityAverages as avg, i}
                    <div
                        class="flex flex-col items-center flex-1 h-full justify-end group"
                    >
                        <div
                            class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            {avg.toFixed(1)}
                        </div>
                        <div
                            class="w-full bg-indigo-500 rounded-t-md transition-all duration-500 relative group-hover:bg-indigo-600"
                            style="height: {getActivityBarHeight(avg)}"
                        ></div>
                        <div class="text-xs text-gray-500 mt-2 font-medium">
                            N{i + 1}
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Students -->
        <div class="distribution-section">
            <h3 class="section-title flex items-center gap-2">
                <span class="material-symbols-rounded text-yellow-500"
                    >emoji_events</span
                >
                Estudiantes Destacados
            </h3>
            <div class="overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead
                        class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-2">Estudiante</th>
                            <th class="px-4 py-2 text-right">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each topStudents as student}
                            <tr
                                class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
                            >
                                <td
                                    class="px-4 py-2 font-medium text-gray-900 dark:text-white truncate max-w-[200px]"
                                    title={student.name}
                                >
                                    {student.name}
                                </td>
                                <td
                                    class="px-4 py-2 text-right font-bold text-green-600"
                                >
                                    {student.val}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Students at Risk -->
        <div class="distribution-section">
            <h3 class="section-title flex items-center gap-2">
                <span class="material-symbols-rounded text-red-500"
                    >warning</span
                >
                Estudiantes en Riesgo
            </h3>
            {#if atRiskStudents.length === 0}
                <div
                    class="flex flex-col items-center justify-center h-40 text-gray-500"
                >
                    <span
                        class="material-symbols-rounded text-4xl mb-2 text-green-500"
                        >thumb_up</span
                    >
                    <p>¡No hay estudiantes en riesgo!</p>
                </div>
            {:else}
                <div class="overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                        >
                            <tr>
                                <th class="px-4 py-2">Estudiante</th>
                                <th class="px-4 py-2 text-right">Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each atRiskStudents as student}
                                <tr
                                    class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td
                                        class="px-4 py-2 font-medium text-gray-900 dark:text-white truncate max-w-[200px]"
                                        title={student.name}
                                    >
                                        {student.name}
                                    </td>
                                    <td
                                        class="px-4 py-2 text-right font-bold text-red-600"
                                    >
                                        {student.val}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            {/if}
        </div>
    </div>
</div>

<style>
    .stats-container {
        padding: 20px;
        font-family: "Inter", sans-serif;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #e2e8f0;
    }

    :global(.dark) .stat-card {
        background: #1e293b;
        border-color: #334155;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    :global(.dark) .stat-label {
        color: #94a3b8;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    :global(.dark) .stat-value {
        color: #f8fafc;
    }

    .distribution-section {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    :global(.dark) .distribution-section {
        background: #1e293b;
        border-color: #334155;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
    }

    :global(.dark) .section-title {
        color: #f8fafc;
    }

    .distribution-chart {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .chart-row {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .row-label {
        width: 120px;
        display: flex;
        flex-direction: column;
    }

    .label-text {
        font-weight: 600;
        color: #334155;
        font-size: 14px;
    }

    :global(.dark) .label-text {
        color: #cbd5e1;
    }

    .label-range {
        font-size: 12px;
        color: #64748b;
    }

    .bar-container {
        flex: 1;
        height: 24px;
        background: #f1f5f9;
        border-radius: 12px;
        overflow: hidden;
    }

    :global(.dark) .bar-container {
        background: #334155;
    }

    .bar {
        height: 100%;
        border-radius: 12px;
        transition: width 0.5s ease-out;
    }

    .row-value {
        width: 80px;
        text-align: right;
        font-weight: 600;
        color: #334155;
        font-size: 14px;
    }

    :global(.dark) .row-value {
        color: #cbd5e1;
    }

    .percentage {
        display: block;
        font-size: 11px;
        color: #64748b;
        font-weight: 400;
    }
</style>
