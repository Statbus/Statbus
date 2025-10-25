import Chart from "chart.js/auto";
import "chartjs-adapter-date-fns";
import trendlinePlugin from "chartjs-plugin-trendline";
Chart.register(trendlinePlugin);

// === Setup ===
const ctx = document.getElementById("yearlyChart");
const toggleMetricBtn = document.getElementById("toggleMetric"); // players/admins
const toggleGranularityBtn = document.getElementById("toggleGranularity"); // daily/monthly

const labels = Object.keys(rawData).sort();
const allIds = [
  ...new Set(labels.flatMap((date) => Object.keys(rawData[date]))),
];

const serverColors = {
  manuel: "#EC8234",
  sybil: "#3449C3",
  terry: "#FC3001",
  "unknown server": "#000",
};

Object.defineProperty(String.prototype, "capitalize", {
  value: function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
  },
  enumerable: false,
});

const hiddenServers = {};
let currentMetric = "players";
let useMonthly = false;

// === Precompute monthly averages ===
function computeMonthlyAverages(data) {
  const monthly = {};
  for (const dateStr of Object.keys(data)) {
    const month = dateStr.slice(0, 7); // "YYYY-MM"
    monthly[month] ??= {};
    for (const server of Object.keys(data[dateStr])) {
      monthly[month][server] ??= { players: [], admins: [] };
      monthly[month][server].players.push(data[dateStr][server].players || 0);
      monthly[month][server].admins.push(data[dateStr][server].admins || 0);
    }
  }
  const averaged = {};
  for (const month of Object.keys(monthly)) {
    averaged[month] = {};
    for (const server of Object.keys(monthly[month])) {
      averaged[month][server] = {
        players:
          monthly[month][server].players.reduce((a, b) => a + b, 0) /
          monthly[month][server].players.length,
        admins:
          monthly[month][server].admins.reduce((a, b) => a + b, 0) /
          monthly[month][server].admins.length,
      };
    }
  }
  return averaged;
}

const monthlyData = computeMonthlyAverages(rawData);

// === Dataset generator ===
function makeDatasets(metric) {
  const dataSource = useMonthly ? monthlyData : rawData;
  const dates = Object.keys(dataSource).sort();

  return allIds.map((id) => ({
    label: id.capitalize(),
    data: dates.map((date) => ({
      x: date,
      y: dataSource[date][id]?.[metric] ?? 0,
    })),
    borderColor: serverColors[id],
    backgroundColor: serverColors[id] + "60",
    borderWidth: 2,
    fill: false,
    pointRadius: 0,
    tension: 0.3,
    trendlineLinear: {
      style: serverColors[id] + "90",
      lineStyle: "dotted",
      width: 1,
    },
    hidden: hiddenServers[id] ?? false,
  }));
}

// === Chart config ===
const baseOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: "nearest", axis: "x", intersect: false },
  plugins: {
    legend: false,
    tooltip: { mode: "index", intersect: false },
    title: { display: true, text: "Average Daily Players per Server" },
  },
  scales: {
    x: {
      type: "time",
      time: { unit: "month", tooltipFormat: "yyyy-MM-dd" },
      ticks: { maxTicksLimit: 12 },
    },
    y: {
      beginAtZero: true,
      ticks: { precision: 0, maxTicksLimit: 6 },
      title: { display: true, text: "Players" },
      max: 80,
    },
  },
};

// === Create chart ===
const chart = new Chart(ctx, {
  type: "line",
  data: { labels, datasets: makeDatasets(currentMetric) },
  options: baseOptions,
});

generateSharedLegend(chart);

// === Chart updater ===
function updateChart() {
  chart.data.datasets = makeDatasets(currentMetric);
  chart.options.plugins.title.text = `Average ${
    useMonthly ? "Monthly" : "Daily"
  } ${currentMetric.capitalize()} per Server`;
  chart.options.scales.x.time.unit = useMonthly ? "month" : "day";
  chart.options.scales.y.title.text = currentMetric.capitalize();
  chart.update();
  generateSharedLegend(chart);
}

// === Metric toggle ===
toggleMetricBtn.addEventListener("click", () => {
  currentMetric = currentMetric === "players" ? "admins" : "players";
  toggleMetricBtn.textContent =
    currentMetric === "players" ? "Show Admins" : "Show Players";
  updateChart();
});

// === Granularity toggle ===
toggleGranularityBtn.addEventListener("click", () => {
  useMonthly = !useMonthly;
  toggleGranularityBtn.textContent = useMonthly ? "Show Daily" : "Show Monthly";
  updateChart();
});

// === Shared legend ===
function generateSharedLegend(chart) {
  const container = document.getElementById("legend");
  container.innerHTML = chart.data.datasets
    .filter((ds) => !ds.label.includes("Trend")) // skip trendline labels
    .map(
      (ds) => `
      <button data-label="${ds.label}" class="btn"
        style="border-color: ${ds.borderColor}; color: ${
        ds.borderColor
      }; opacity: ${ds.hidden ? "0.4" : "1"};">
        ${ds.label}
      </button>`
    )
    .join("");

  container.querySelectorAll("button[data-label]").forEach((el) => {
    el.addEventListener("click", () => {
      const label = el.getAttribute("data-label");
      chart.data.datasets.forEach((ds) => {
        if (ds.label === label) ds.hidden = !ds.hidden;
      });
      hiddenServers[label.toLowerCase()] = !hiddenServers[label.toLowerCase()];
      el.style.opacity = el.style.opacity === "0.4" ? "1" : "0.4";
      chart.update();
    });
  });
}
