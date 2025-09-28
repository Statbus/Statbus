import Chart from "chart.js/auto";

function formatData(response, label) {
  let newFormat = {
    datasets: [
      {
        label: label,
        data: [],
        backgroundColor: [],
      },
    ],
    labels: [],
  };
  response.forEach((item) => {
    newFormat.datasets[0].data.push(item.minutes);
    newFormat.labels.push(item.job);
    newFormat.datasets[0].backgroundColor.push(item.background);
  });
  return newFormat;
}
const ctx = document.getElementById("jobs");
const config = {
  type: "bar",
  data: [],
  options: {
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  },
};
const chart = new Chart(ctx, config);
async function renderChart(url, chart, label) {
  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP Error ${response.status}`);
    }
    const data = await response.json();
    chart.data = formatData(data, "Minutes");
  } catch (error) {
    console.log("Error:", error);
  }
  chart.update();
}
let url = `${window.location}/playtime?all=true`.replace("/jobs", "");
renderChart(url, chart, "Minutes");
