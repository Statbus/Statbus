import Chart from "chart.js/auto";
import "chartjs-adapter-date-fns";
const playerCtx = document.getElementById("playerChart");
const adminCtx = document.getElementById("adminChart");

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

const labels = Object.keys(rawData).sort();
const names = Array.from(
  new Set(labels.flatMap((label) => Object.keys(rawData[label])))
);

const players = names.map((name) => ({
  label: `${name.capitalize()} (players)`,
  data: labels.map((label) => rawData[label][name]?.players ?? 0),
  fill: true,
  borderWidth: 2,
  backgroundColor: serverColors[name] + "80",
  borderColor: serverColors[name],
}));

const admins = names.map((name) => ({
  label: `${name.capitalize()} (admins)`,
  data: labels.map((label) => rawData[label][name]?.admins ?? 0),
  fill: true,
  borderWidth: 2,
  backgroundColor: serverColors[name] + "80",
  borderColor: serverColors[name],
}));

const options = {
  type: "radar",
  interaction: {
    intersect: true,
    axis: "r",
    mode: "index",
  },
  data: {
    labels: labels,
  },
  options: {
    scales: {
      r: {
        beginAtZero: true,
        angleLines: { color: "#ccc" },
        grid: { color: "#ddd" },
      },
    },
    plugins: {
      title: {
        display: true,
      },
      legend: {
        position: "top",
      },
    },
  },
};
let playerChart = new structuredClone(options);
playerChart.data.datasets = players;
playerChart.options.plugins.title.text =
  "Average player counts per hour by server";

new Chart(playerCtx, playerChart);

let adminChart = new structuredClone(options);
adminChart.data.datasets = admins;
adminChart.options.plugins.title.text =
  "Average admin counts per hour by server";

new Chart(adminCtx, adminChart);
