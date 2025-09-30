import Chart from "chart.js/auto";
import "chartjs-adapter-date-fns";
const ctx = document.getElementById("serverChart");
const labels = Object.keys(rawData).sort();
console.log(rawData);
// Step 2: Collect all unique IDs
const allIds = [
  ...new Set(labels.flatMap((date) => Object.keys(rawData[date]))),
];

const serverColors = {
  manuel: "#EC8234",
  sybil: "#3449C3",
  terry: "#FC3001",
  "unknown server": "#000",
};

// Step 3: Build datasets for each ID
const datasets = allIds.map((id) => {
  return {
    label: `${id}`,
    data: labels.map((date) => rawData[date][id] ?? 0), // fill missing with 0
    borderWidth: 2,
    backgroundColor: serverColors[id],
    borderColor: serverColors[id],
  };
});
const data = { labels, datasets };
const config = {
  type: "bar",
  data: data,
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: "index", intersect: false },
    plugins: { legend: { position: "top" } },
    scales: {
      x: {
        type: "time", // needs chartjs-adapter-date-fns or moment
        time: { unit: "day", tooltipFormat: "yyyy-MM-dd" },
        stacked: true,
        ticks: {
          major: {
            fontStyle: "bold",
          },
          maxTicksLimit: 15,
        },
      },
      y: {
        beginAtZero: true,
        stacked: true,
        ticks: {
          beginAtZero: true,
          precision: 0,
          maxTicksLimit: 5,
        },
        label: "Connections",
      },
    },
  },
};
const chart = new Chart(ctx, config);
