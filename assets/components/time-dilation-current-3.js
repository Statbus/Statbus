import Chart from "chart.js/auto";
const ctx = document.getElementById("chart");
const labels = Object.keys(data).map((ts) => ts.split(" ")[1]);
const avgValues = Object.values(data).map((d) => Number(d.avg));
const avgFastValues = Object.values(data).map((d) => Number(d.avg_fast));
const avgSlowValues = Object.values(data).map((d) => Number(d.avg_slow));
const currentValues = Object.values(data).map((d) => Number(d.current));

const config = {
  type: "line",
  data: {
    labels: labels,
    datasets: [
      {
        label: "Avg",
        data: avgValues,
        fill: true,
      },
      {
        label: "Avg Fast",
        data: avgFastValues,
        fill: true,
      },
      {
        label: "Avg Slow",
        data: avgSlowValues,
        fill: true,
      },
      {
        label: "Current",
        data: currentValues,
        fill: true,
      },
    ],
  },
  options: {
    scales: {
      y: {
        stacked: true,
      },
    },
  },
};
const chart = new Chart(ctx, config);
