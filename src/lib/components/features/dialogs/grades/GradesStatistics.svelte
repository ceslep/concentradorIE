<script lang="ts">
  import { onMount } from "svelte";
  import type { GradeData } from "../../../../utils/gradeTableUtils";
  import { theme } from "../../../../themeStore";

  export let data: GradeData[] = [];
  export let loading: boolean = false;

  // Statistics state
  let average = 0;
  let passRate = 0;
  let failRate = 0;
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
    failRate = 100 - passRate;
    performanceLevels = levels;

    // Finalize activity averages
    activityAverages = activitySums.map((s, i) =>
      activityCounts[i] > 0 ? s / activityCounts[i] : 0
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
      const count = grades.filter((g) => g >= i && g < i + binSize).length;
      // Include 5.0 in the last bin
      if (i + binSize >= maxGrade) {
        const lastCount = grades.filter((g) => g >= i && g <= maxGrade).length;
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
    failRate = 0;
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
  {#if loading}
    <!-- Skeleton Loading -->
    <div class="summary-grid animate-pulse">
      {#each Array(5) as _}
        <div
          class="stat-card h-24 bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700"
        >
          <div
            class="stat-icon bg-gray-200 dark:bg-gray-700 w-12 h-12 rounded-xl"
          ></div>
          <div class="stat-info flex-1 gap-2">
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
          </div>
        </div>
      {/each}
    </div>

    <div class="distribution-section mb-6 animate-pulse">
      <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-64 mb-4"></div>
      <div
        class="h-64 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
      ></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 animate-pulse">
      <div class="distribution-section h-64">
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-48 mb-4"></div>
        <div class="space-y-4">
          {#each Array(4) as _}
            <div class="flex items-center gap-4">
              <div class="w-24 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
              <div
                class="flex-1 h-6 bg-gray-200 dark:bg-gray-700 rounded-xl"
              ></div>
              <div class="w-16 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
          {/each}
        </div>
      </div>
      <div class="distribution-section h-64">
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-48 mb-4"></div>
        <div class="flex items-end gap-2 h-48">
          {#each Array(12) as _}
            <div
              class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-t-md"
              style="height: {Math.random() * 80 + 20}%"
            ></div>
          {/each}
        </div>
      </div>
    </div>
  {:else}
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
        <div class="stat-icon bg-orange-100 text-orange-600">
          <span class="material-symbols-rounded">query_stats</span>
        </div>
        <div class="stat-info">
          <span class="stat-label">Desviación Estándar</span>
          <span class="stat-value">{standardDeviation.toFixed(2)}</span>
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
        <div class="stat-icon bg-red-100 text-red-600">
          <span class="material-symbols-rounded">cancel</span>
        </div>
        <div class="stat-info">
          <span class="stat-label">Reprobación</span>
          <span class="stat-value">{failRate.toFixed(1)}%</span>
        </div>
      </div>
    </div>

    <!-- Gaussian Distribution Chart -->
    <div class="distribution-section mb-6">
      <h3 class="section-title">Análisis de Distribución (Campana de Gauss)</h3>
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
            <div class="w-3 h-3 bg-indigo-200 border border-indigo-400"></div>
            <span>Estudiantes</span>
          </div>
          <div class="flex items-center gap-1">
            <div class="w-3 h-1 bg-red-500"></div>
            <span>Curva Normal</span>
          </div>
          <div class="flex items-center gap-1">
            <div class="w-3 h-3 border-l border-green-500 border-dashed"></div>
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
                style="width: {getBarWidth(performanceLevels.basico)}"
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
                style="width: {getBarWidth(performanceLevels.superior)}"
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
        <h3 class="section-title">Promedio por Actividad</h3>
        <div class="h-64 flex items-end justify-between gap-2 px-2">
          {#each activityAverages as avg, i}
            <div
              class="flex flex-col items-center flex-1 h-full justify-end group"
            >
              <div
                class="relative w-full flex items-end justify-center h-[85%]"
              >
                <div
                  class="w-full bg-indigo-500/80 rounded-t-md transition-all duration-300 group-hover:bg-indigo-600 relative overflow-hidden"
                  style="height: {getActivityBarHeight(avg)}"
                >
                  <!-- Tooltip -->
                  <div
                    class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10"
                  >
                    {avg.toFixed(1)}
                  </div>
                </div>
              </div>
              <span class="text-xs text-gray-500 mt-2 font-medium"
                >N{i + 1}</span
              >
            </div>
          {/each}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Students -->
      <div class="distribution-section">
        <h3 class="section-title text-green-600">Mejores Estudiantes</h3>
        <div class="space-y-3">
          {#each topStudents as student, i}
            <div
              class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800/30"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-6 h-6 rounded-full bg-green-200 text-green-700 flex items-center justify-center text-xs font-bold"
                >
                  {i + 1}
                </div>
                <span class="font-medium text-gray-700 dark:text-gray-300"
                  >{student.name}</span
                >
              </div>
              <span class="font-bold text-green-600">{student.val}</span>
            </div>
          {/each}
        </div>
      </div>

      <!-- At Risk Students -->
      <div class="distribution-section">
        <h3 class="section-title text-red-600">Estudiantes en Riesgo</h3>
        <div class="space-y-3">
          {#each atRiskStudents as student}
            <div
              class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800/30"
            >
              <span class="font-medium text-gray-700 dark:text-gray-300"
                >{student.name}</span
              >
              <span class="font-bold text-red-600">{student.val}</span>
            </div>
          {/each}
          {#if atRiskStudents.length === 0}
            <div class="text-center py-8 text-gray-500 italic">
              No hay estudiantes con promedio bajo 3.0
            </div>
          {/if}
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  .stats-container {
    padding: 1rem;
    background-color: #f8fafc;
    min-height: 100%;
  }

  :global(.dark) .stats-container {
    background-color: #111827;
  }

  .summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s;
  }

  :global(.dark) .stat-card {
    background: #1f2937;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  }

  .stat-card:hover {
    transform: translateY(-2px);
  }

  .stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .stat-info {
    display: flex;
    flex-direction: column;
  }

  .stat-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
  }

  :global(.dark) .stat-label {
    color: #9ca3af;
  }

  .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
  }

  :global(.dark) .stat-value {
    color: #f3f4f6;
  }

  .distribution-section {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  :global(.dark) .distribution-section {
    background: #1f2937;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  }

  .section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 1.5rem;
  }

  :global(.dark) .section-title {
    color: #e2e8f0;
  }

  .distribution-chart {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .chart-row {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .row-label {
    width: 100px;
    display: flex;
    flex-direction: column;
  }

  .label-text {
    font-weight: 500;
    color: #475569;
  }

  :global(.dark) .label-text {
    color: #cbd5e1;
  }

  .label-range {
    font-size: 0.75rem;
    color: #94a3b8;
  }

  .bar-container {
    flex: 1;
    height: 0.75rem;
    background: #f1f5f9;
    border-radius: 9999px;
    overflow: hidden;
  }

  :global(.dark) .bar-container {
    background: #374151;
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
