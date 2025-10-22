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
  tension: 0.2,
  backgroundColor: serverColors[name] + "80",
  borderColor: serverColors[name],
}));

const admins = names.map((name) => ({
  label: `${name.capitalize()} (players)`,
  data: labels.map((label) => rawData[label][name]?.admins ?? 0),
  fill: true,
  borderWidth: 2,
  tension: 0.2,
  backgroundColor: serverColors[name] + "80",
  borderColor: serverColors[name],
}));

new Chart(playerCtx, {
  type: "radar",
  data: {
    labels: labels,
    datasets: players,
  },
  options: {
    responsive: true,
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
        text: "Average player counts per hour by server",
      },
      legend: {
        position: "top",
      },
    },
  },
});

new Chart(adminCtx, {
  type: "radar",
  data: {
    labels: labels,
    datasets: admins,
  },
  options: {
    responsive: true,
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
        text: "Average admin counts per hour by server",
      },
      legend: {
        position: "top",
      },
    },
  },
});
